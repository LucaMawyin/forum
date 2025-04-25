<?php
include_once "includes/utils.php";

class Comment {
  private $conn;
  private $table_name = "comments";
  
  public $comment_id;
  public $post_id;
  public $user_id;
  public $content;
  public $created_at;
  public $updated_at;
  public $is_deleted;
  
  public function __construct($db) {
    $this->conn = $db;
  }
  
  public function create_comment($user_id, $post_id, $content) {
    if (empty($user_id) || empty($post_id) || empty($content)) {
      return return_response(false, "All fields are required");
    }
    
    // Check if post is closed
    $query = "SELECT is_closed FROM posts WHERE post_id = :post_id AND is_deleted = FALSE";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $post_id);
    $stmt->execute();
    $post = $stmt->fetch();
    
    if (!$post) {
      return return_response(false, "Post not found");
    }
    
    if ($post['is_closed']) {
      return return_response(false, "This post is closed. New comments are not allowed.");
    }
    
    $query = "INSERT INTO " . $this->table_name . "
              (user_id, post_id, content)
              VALUES (:user_id, :post_id, :content)";
    
    $stmt = $this->conn->prepare($query);
    $content = clean_input($content);
    
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":post_id", $post_id);
    $stmt->bindParam(":content", $content);
    
    if ($stmt->execute()) {
      return array(
        "success" => true,
        "comment_id" => $this->conn->lastInsertId(),
        "message" => "Comment added successfully"
      );
    }
    
    return return_response(false, "Unable to add comment");
  }
  
  public function get_comments_by_post($post_id) {
    $query = "SELECT c.*, u.username
              FROM " . $this->table_name . " c
              JOIN users u ON c.user_id = u.user_id
              WHERE c.post_id = :post_id AND c.is_deleted = FALSE
              ORDER BY c.created_at ASC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $post_id);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
  
  public function delete_comment($comment_id, $user_id) {
    $query = "SELECT c.*, p.user_id as post_author_id
              FROM " . $this->table_name . " c
              JOIN posts p ON c.post_id = p.post_id
              WHERE c.comment_id = :comment_id AND c.is_deleted = FALSE";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":comment_id", $comment_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
      return return_response(false, "Comment not found");
    }
    
    $comment = $stmt->fetch();
    
    // Check if user is authorized to delete
    if ($comment['user_id'] != $user_id) {
      // Check if user is a site-wide admin or moderator
      $query = "SELECT role FROM users WHERE user_id = :user_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':user_id', $user_id);
      $stmt->execute();
      $site_role = $stmt->fetch();
      
      // If not site-wide admin/mod, check if user is post author
      if (!$site_role || ($site_role['role'] != 'moderator' && $site_role['role'] != 'admin')) {
        if ($comment['post_author_id'] != $user_id) {
          return return_response(false, "You don't have permission to delete this comment");
        }
      }
    }
    
    $query = "UPDATE " . $this->table_name . "
              SET is_deleted = TRUE
              WHERE comment_id = :comment_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":comment_id", $comment_id);
    
    if ($stmt->execute()) {
      return return_response(true, "Comment deleted successfully");
    }
    
    return return_response(false, "Unable to delete comment");
  }
  
  public function get_comment_by_id($comment_id) {
    $query = "SELECT c.*, u.username
              FROM " . $this->table_name . " c
              JOIN users u ON c.user_id = u.user_id
              WHERE c.comment_id = :comment_id AND c.is_deleted = FALSE";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":comment_id", $comment_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
      return $stmt->fetch();
    }
    
    return null;
  }
  
  public function get_user_comments($user_id, $limit = 10) {
    $query = "SELECT c.*, p.title as post_title, p.post_id
              FROM " . $this->table_name . " c
              JOIN posts p ON c.post_id = p.post_id
              WHERE c.user_id = :user_id AND c.is_deleted = FALSE
              ORDER BY c.created_at DESC
              LIMIT :limit";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }
}
?>