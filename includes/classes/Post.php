<?php
include_once "includes/utils.php";

class Post {
  private $conn;
  private $table_name = "posts";

  public $post_id;
  public $user_id;
  public $course_id;
  public $title;
  public $content;
  public $created_at;
  public $updated_at;
  public $is_pinned;
  public $is_closed;
  public $is_deleted;
  public $view_count;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function create_post($user_id, $course_id, $title, $content) {
    if (empty($user_id) || empty($course_id) || empty($title) || empty($content)) {
      return return_response(false, "All fields are required");
    }

    $query = "INSERT INTO " . $this->table_name . "
              (user_id, course_id, title, content)
              VALUES (:user_id, :course_id, :title, :content)";
    
    $stmt = $this->conn->prepare($query);
    $title = clean_input($title);
    $content = clean_input($content);

    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":content", $content);

    if ($stmt->execute()) {
      return array(
        "success" => true,
        "post_id" => $this->conn->lastInsertId(),
        "message" => "Post created successfuly"
      );
    }

    return return_response(false, "Unable to create post");
  }

  public function get_post_by_id($post_id, $increment_view = true) {
    $query = "SELECT p.*,
                u.username,
                c.course_code,
                c.course_name,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) as comment_count
              FROM " . $this->table_name . " p
              JOIN users u ON p.user_id = u.user_id
              JOIN courses c ON p.course_id = c.course_id
              WHERE p.post_id = :post_id AND p.is_deleted = FALSE";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $post_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) return null;

    $post = $stmt->fetch();
    if ($increment_view) $this->increment_view_count($post_id);

    return $post;
  }

  public function update_post($post_id, $title, $content, $user_id) {
    $post = $this->get_post_by_id($post_id, false);

    if (!$post) return return_response(false, "Post not found");

    if ($post['user_id'] != $user_id) {
      $query = "SELECT role from user_courses WHERE user_id = :user_id AND course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(":user_id", $user_id);
      $stmt->bindParam(":course_id", $post['course_id']);
      $stmt->execute();

      $user_role = $stmt->fetch();

      if (!$user_role || ($user_role['role'] != 'moderator' && $user_role != 'admin')) {
        return return_response(false, "You don't have permission to edit this post");
      }
    }

    $query = "UPDATE " . $this->table_name . "
              SET title = :title,
                  content = :content,
                  updated_at = CURRENT_TIMESTAMP
              WHERE post_id = :post_id";

    $stmt = $this->conn->prepare($query);
    $title = clean_input($title);
    $content = clean_input($content);

    $stmt->bindParam(":post_id", $post_id);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":content", $content);

    if ($stmt->execute()) {
      return return_response(true, "Post updated successfuly");
    }

    return return_response(false, "Unable to update post");
  }

  public function delete_post($post_id, $user_id) {
    $post = $this->get_post_by_id($post_id, false);

    if (!$post) return return_response(false, "Post not found");

    if ($post['user_id'] != $user_id) {
      $query = "SELECT role FROM user_courses WHERE user_id = :user_id AND course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':user_id', $user_id);
      $stmt->bindParam(':course_id', $post['course_id']);
      $stmt->execute();
      
      $user_role = $stmt->fetch();
      
      if (!$user_role || ($user_role['role'] != 'moderator' && $user_role['role'] != 'admin')) {
          return return_response(false, "You don't have permission to delete this post");
      }
    }

    $query = "UPDATE " . $this->table_name . "
              SET is_deleted = TRUE
              WHERE post_id = :post_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $post_id);

    if ($stmt->execute()) {
      return return_response(false, "Post deleted successfuly");
    }

    return return_response(false, "Unable to delete post");
  }

  public function toggle_pin($post_id, $user_id) {
    $post = $this->get_post_by_id($post_id, false);

    if (!$post) return return_response(false, "Post not found");

    $query = "SELECT role FROM user_courses WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $post['course_id']);
    $stmt->execute();
    
    $user_role = $stmt->fetch();
    
    if (!$user_role || ($user_role['role'] != 'moderator' && $user_role['role'] != 'admin')) {
        return return_response(false, "You don't have permission to pin posts");
    }

    $new_status = $post['is_pinned'] ? 0 : 1;
    $action = $new_status ? "pinned" : "unpinned";

    $query = "UPDATE " . $this->table_name . "
              SET is_pinned = :new_status
              WHERE post_id = :post_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":new_status", $new_status);
    $stmt->bindParam(":post_id", $post_id);

    if ($stmt->execute()) {
      return return_response(true, "Post $action successfuly");
    }

    return return_response(false, "Unable to update post status");
  }

  public function toggle_close($post_id, $user_id) {
    $post = $this->get_post_by_id($post_id, false);

    if (!$post) return return_response(false, "Post not found");

    $query = "SELECT role FROM user_courses WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':course_id', $post['course_id']);
    $stmt->execute();
    
    $user_role = $stmt->fetch();
    
    if (!$user_role || ($user_role['role'] != 'moderator' && $user_role['role'] != 'admin')) {
        return return_response(false, "You don't have permission to close this post");
    }

    $new_status = $post['is_closed'] ? 0 : 1;
    $action = $new_status ? "closed" : "reopened";

    $query = "UPDATE " . $this->table_name . "
              SET is_closed = :new_status
              WHERE post_id = :post_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":new_status", $new_status);
    $stmt->bindParam(":post_id", $post_id);

    if ($stmt->execute()) {
      return return_response(true, "Post $action successfuly");
    }

    return return_response(false, "Unable to update post status");
  }

  private function increment_view_count($post_id) {
    $query = "UPDATE " . $this->table_name . "
              SET view_count = view_count + 1
              WHERE post_id = :post_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":post_id", $post_id);
    $stmt->execute();
  }

  public function get_posts_by_course($course_id, $sort_by = 'recent', $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;

    $query = "SELECT p.*,
                u.username,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) as comment_count
              FROM " . $this->table_name . " p
              JOIN users u ON p.user_id = u.user_id
              WHERE p.course_id = :course_id AND p.is_deleted = FALSE";

    switch ($sort_by) {
      case 'popular':
        $query .= " ORDER BY p.view_count DESC";
        break;
      case 'unanswered':
        $query .= " AND (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) = 0
                  ORDER BY p.created_at DESC";
        break;
      default:
        $query .= " ORDER BY p.is_pinned DESC, p.created_at DESC";
        break;
    }

    $query .= " LIMIT :limit OFFSET :offset";
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll();

    $query = "SELECT COUNT(*) as total FROM " . $this->table_name . "
              WHERE course_id = :course_id AND is_deleted = FALSE";

    if ($sort_by == 'unanswered') {
      $query .= " AND (SELECT COUNT(*) FROM comments WHERE is_deleted = FALSE) = 0";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->execute();
    $total_posts = $stmt->fetch()['total'];

    return array(
      "posts" => $posts,
      "total" => $total_posts,
      "page" => $page,
      "limit" => $limit,
      "total_pages" => ceil($total_posts / $limit)
    );
  }

  public function get_recent_posts($user_id = null, $limit = 10, $sort_by = 'recent') {
    $query = "SELECT p.*,
                u.username,
                c.course_code,
                c.course_name,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) as comment_count
              FROM " . $this->table_name . " p
              JOIN users u ON p.user_id = u.user_id
              JOIN courses c ON p.course_id = c.course_id
              WHERE p.is_deleted = FALSE";
    
    if ($user_id) {
      $query .= " AND p.course_id IN (
                    SELECT course_id FROM user_courses WHERE user_id = :user_id
                  )";
    }

    if ($sort_by == 'unanswered') {
      $query .= " AND (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) = 0";
    }

    switch ($sort_by) {
      case 'popular':
        $query .= " ORDER BY p.view_count DESC";
        break;
      case 'unanswered':
        $query .= " ORDER BY p.created_at DESC";
        break;
      default:
        $query .= " ORDER BY p.is_pinned DESC, p.created_at DESC";
        break;
    }
    
    $query .= " LIMIT :limit";
    
    $stmt = $this->conn->prepare($query);

    if ($user_id) $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(); 
  }

  public function search_posts($search_term, $user_id = null) {
    $query = "SELECT p.*, 
                  u.username, 
                  c.course_code, 
                  c.course_name,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) as comment_count
              FROM " . $this->table_name . " p
              JOIN users u ON p.user_id = u.user_id
              JOIN courses c ON p.course_id = c.course_id
              WHERE p.is_deleted = FALSE
              AND (p.title LIKE :search OR p.content LIKE :search OR c.course_code LIKE :search)";
    
    if ($user_id) {
      $query .= " AND p.course_id IN (
                    SELECT course_id FROM user_courses WHERE user_id = :user_id
                )";
    }
    
    $query .= " ORDER BY p.created_at DESC LIMIT 50";
    
    $stmt = $this->conn->prepare($query);
    
    $search_param = "%{$search_term}%";
    $stmt->bindParam(':search', $search_param);
    
    if ($user_id) $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    return $stmt->fetchAll();
  }

  public function get_posts_by_user($user_id, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;
    
    $query = "SELECT p.*, 
                  c.course_code, 
                  c.course_name,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id AND is_deleted = FALSE) as comment_count
              FROM " . $this->table_name . " p
              JOIN courses c ON p.course_id = c.course_id
              WHERE p.user_id = :user_id AND p.is_deleted = FALSE
              ORDER BY p.created_at DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $this->conn->prepare($query);
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $posts = $stmt->fetchAll();
    
    $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
             WHERE user_id = :user_id AND is_deleted = FALSE";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $total_posts = $stmt->fetch()['total'];
    
    return array(
      'posts' => $posts,
      'total' => $total_posts,
      'page' => $page,
      'limit' => $limit,
      'total_pages' => ceil($total_posts / $limit)
    );
  }
}
?>