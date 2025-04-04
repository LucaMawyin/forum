<?php
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
      return array("success" => false, "message" => "Course code and name are required");
    }

    if ($this->course_code_exists($course_code)) {
      return array("success" => false, "message" => "Course code already exists");
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
        "message" => "Course created successfully",
      );
    }

    return array("success" => false, "message" => "Unable to create course");
  }

  // update_course

  // delete_course

  // gen_enrolled_users

  // get_post_stats

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