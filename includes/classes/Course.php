<?php
include_once "includes/utils.php";

class Course {
  private $conn;
  private $table_name = "courses";

  public $course_id;
  public $course_code;
  public $course_name;
  public $description;
  public $instructor_id;
  public $created_at;
  public $updated_at;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function get_all_courses() {
    $query = "SELECT c.*, u.username as instructor_name,
              (SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.course_id) as student_count
              FROM " . $this->table_name . " c
              LEFT JOIN users u ON c.instructor_id = u.user_id
              ORDER BY c.course_code";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function get_course_by_id($course_id) {
    $query = "SELECT c.*, u.username as instructor_name,
              (SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.course_id) as student_count
              FROM " . $this->table_name . " c
              LEFT JOIN users u ON c.instructor_id = u.user_id
              WHERE c.course_id = :course_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->execute();

    return $stmt->fetch();
  }

  public function get_course_by_code($course_code) {
    $query = "SELECT c.*, u.username as instructor_name,
              (SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.course_id) as student_count
              FROM " . $this->table_name . " c
              LEFT JOIN users u ON c.instructor_id = u.user_id
              WHERE c.course_code = :course_code";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":course_code", $course_code);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function create_course($course_code, $course_name, $description = NULL, $instructor_id = null) {
    if (empty($course_code) || empty($course_name)) {
      return return_response(false, "Course code and name are required");
    }

    if ($this->course_code_exists($course_code)) {
      return return_response(false, "Course code already exists");
    }

    $query = "INSERT INTO " . $this->table_name . "
              (course_code, course_name, description, instructor_id)
              VALUES (:course_code, :course_name, :description, :instructor_id)";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":course_code", $course_code);
    $stmt->bindParam(":course_name", $course_name);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":instructor_id", $instructor_id);

    if ($stmt->execute()) {
      return array(
        "success" => true,
        "course_id" => $this->conn->lastInsertId(),
        "message" => "Course created successfuly",
      );
    }

    return return_response(false, "Unable to create course");
  }

  public function update_course($course_id, $course_code, $course_name, $description, $instructor_id = null) {
    if (empty($course_id) || empty($course_code) || empty($course_name)) {
      return return_response(false, "Course ID, code, and name are required");
    }

    $existing_course = $this->get_course_by_id($course_id);
    if (!$existing_course) {
      return return_response(false, "Course not found");
    }

    if ($course_code !== $existing_course['course_code'] && $this->course_code_exists($course_code)) {
      return return_response(true, "Course code already exists");
    }

    $query = "UPDATE " . $this->table_name . "
              SET course_code = :course_code,
                  course_name = :course_name,
                  description = :description,
                  instructor_id = :instructor_id,
                  updated_at = CURRENT_TIMESTAMP
              WHERE course_id = :course_id";
    
    $stmt = $this->conn->prepare($query);
    $course_code = clean_input($course_code);
    $course_name = clean_input($course_name);
    $description = clean_input($description);

    $stmt->bindParam(":course_id", $course_id);
    $stmt->bindParam(":course_code", $course_code);
    $stmt->bindParam(":course_name", $course_name);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":instructor_id", $instructor_id);

    if ($stmt->execute()) {
      return return_response(true, "Course updated successfuly");
    }

    return return_response(false, "Unable to update course");
  }

  public function delete_course($course_id) {
    $existing_course = $this->get_course_by_id($course_id);
    if (!$existing_course) {
      return return_response(false, "Course not found");
    }

    try {
      $this->conn->beginTransaction();

      $query = "DELETE c from comments c
                JOIN posts p ON c.post_id = p.post_id
                WHERE p.course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':course_id', $course_id);
      $stmt->execute();

      $query = "DELETE FROM posts WHERE course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':course_id', $course_id);
      $stmt->execute();
      
      $query = "DELETE FROM user_courses WHERE course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':course_id', $course_id);
      $stmt->execute();
      
      $query = "DELETE FROM " . $this->table_name . " WHERE course_id = :course_id";
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(':course_id', $course_id);
      $stmt->execute();

      $this->conn->commit();

      return return_response(true, "Course deleted successfuly");
    } catch (Exception $e) {
      $this->conn->rollBack();
      return array("success" => false, "message" => "Error deleting course: " . $e->getMessage());
    }
  }

  public function get_enrolled_users($course_id) {
    $query = "SELECT u.user_id, u.username, u.email, uc.role as course_role, uc.enrollement_date
              FROM users u
              JOIN user_courses uc ON u.user_id = uc.user_id
              WHERE uc.course_id = :course_id
              ORDER BY uc.role, u.username";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function get_post_stats($course_id) {
    $query = "SELECT COUNT(*) as total_posts FROM posts 
             WHERE course_id = :course_id AND is_deleted = FALSE";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $total_posts = $stmt->fetch()['total_posts'];
    
    $query = "SELECT COUNT(*) as recent_posts FROM posts 
             WHERE course_id = :course_id AND is_deleted = FALSE 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $recent_posts = $stmt->fetch()['recent_posts'];
    
    $query = "SELECT COUNT(*) as unanswered_posts FROM posts p
             WHERE p.course_id = :course_id AND p.is_deleted = FALSE
             AND NOT EXISTS (
                 SELECT 1 FROM comments c 
                 WHERE c.post_id = p.post_id AND c.is_deleted = FALSE
             )";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->execute();
    $unanswered_posts = $stmt->fetch()['unanswered_posts'];
    
    return array(
        'total_posts' => $total_posts,
        'recent_posts' => $recent_posts,
        'unanswered_posts' => $unanswered_posts
    );
  }

  private function course_code_exists($course_code) {
    $query = "SELECT course_id FROM " . $this->table_name . " WHERE course_code = :course_code";
    
    $stmt = $this->conn->prepare($query);
    $course_code = clean_input($course_code);
    $stmt->bindParam(":course_code", $course_code);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }
}
?>