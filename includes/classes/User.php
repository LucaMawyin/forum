<?php
include_once "includes/utils.php";
class User {
  private $conn;
  private $table_name = "users";

  public $user_id;
  public $username;
  public $email;
  public $password;
  public $registration_date;
  public $last_login;
  public $role;

  public function __construct($db, $user_id = null) {
    $this->conn = $db;
    if ($user_id) {
      $this->user_id = $user_id;
      $this->load_user_data();
    }
  }
  
  private function load_user_data() {
    $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $this->username = $row['username'];
      $this->email = $row['email'];
      $this->registration_date = $row['registration_date'];
      $this->last_login = $row['last_login'];
      $this->role = $row['role'];
    }
  }

  public function register($username, $email, $password) {
    if (empty($username) || empty($email) || empty($password)) {
      return return_response(false, "All fields are required");
    }

    if ($this->username_exists($username)) {
      return return_response(false, "Username already exists");
    }

    if ($this->email_exists($email)) {
      return return_response(false, "Email already exists");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO " . $this->table_name . "
              (username, email, password)
              VALUES (:username, :email, :password)";
    
    $stmt = $this->conn->prepare($query);
    
    $username = clean_input($username);
    $email = clean_input($email);

    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashed_password);

    if ($stmt->execute()) {
      return array(
        "success" => true,
        "user_id" => $this->conn->lastInsertId(),
        "message" => "Registration successful"
      );
    }

    return return_response(false, "Registration failed");
  }

  public function login($email, $password) {
    if (empty($email) || empty($password)) {
      return return_response(false, "All fields are required");
    }

    $query = "SELECT user_id, username, email, password, role
              FROM " . $this->table_name . "
              WHERE email = :email";
    
    $stmt = $this->conn->prepare($query);
    
    $email = clean_input($email);
    $stmt->bindParam(":email", $email);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
      $row = $stmt->fetch();

      if (password_verify($password, $row['password'])) {
        $this->update_last_login($row['user_id']);

        ensure_session_started();
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];

        return array(
          "success" => true,
          "user_id" => $row['user_id'],
          "username" => $row['username'],
          "message" => "Login successful"
        );
      } else {
        return return_response(false, "Invalid password");
      }
    }

    return return_response(false, "User not found");
  }

  public function logout() {
    ensure_session_started();
    $_SESSION = array();
    session_destroy();
    
    if (isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
      $this->clear_remember_token($_COOKIE['user_id']);
      
      setcookie('remember_token', '', time() - 3600, '/');
      setcookie('user_id', '', time() - 3600, '/');
    }
    
    return return_response(true, "Logout successful");
  }

  public function get_user_by_id($user_id) {
    $query = "SELECT user_id, username, email, registration_date, last_login, role
              FROM " . $this->table_name . "
              WHERE user_id = :user_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetch();
  }

  public function get_user_courses($user_id) {
    $query = "SELECT c.course_id, c.course_code, c.course_name, c.description,
                     uc.role as user_role, uc.encrollement_date
              FROM courses c
              JOIN user_courses uc ON c.course_id = uc.course_id
              WHERE uc.user_id = :user_id
              ORDER BY c.course_code";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function enroll_in_course($user_id, $course_id, $role = 'student') {
    $query = "SELECT * FROM user_courses
              WHERE user_id = :user_id AND course_id = :course_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      return return_response(false, "Already enrolled in this course");
    }

    $query = "INSERT INTO user_courses (user_id, course_id, role)
              VALUES (:user_id, :course_id, :role)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->bindParam(":role", $role);

    if ($stmt->execute()) {
      return return_response(true, "Successfuly enrolled");
    }

    return return_response(false, "Enrollment failed");
  }

  public function is_enrolled($user_id, $course_id) {
    $query = "SELECT * FROM user_courses
              WHERE user_id = :user_id AND course_id = :course_id";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":course_id", $course_id);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }

  public function get_user_stats($user_id) {
    $query = "SELECT COUNT(*) as post_count FROM posts WHERE user_id = :user_id AND is_deleted = FALSE";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $post_count = $stmt->fetch()['post_count'];

    $query = "SELECT COUNT(*) as comment_count FROM comments WHERE user_id = :user_id AND is_deleted = FALSE";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $comment_count = $stmt->fetch()['comment_count'];

    $query = "SELECT COUNT(*) as course_count FROM user_courses WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $course_count = $stmt->fetch()['course_count'];

    return array(
      "post_count" => $post_count,
      "comment_count" => $comment_count,
      "course_count" => $course_count
    );
  }

  private function update_last_login($user_id) {
    $query = "UPDATE " . $this->table_name . "
              SET last_login = CURRENT_TIMESTAMP
              WHERE user_id = :user_id";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
  }

  private function username_exists($username) {
    $query = "SELECT user_id FROM " . $this->table_name . " WHERE username = :username";
    
    $stmt = $this->conn->prepare($query);
    $username = clean_input($username);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }

  private function email_exists($email) {
    $query = "SELECT user_id FROM " . $this->table_name . " WHERE email = :email";

    $stmt = $this->conn->prepare($query);
    $email = clean_input($email);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    return $stmt->rowCount() > 0;
  }
  
  public function set_remember_token($user_id, $token) {
    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
    
    $query = "UPDATE " . $this->table_name . "
              SET remember_token = :token, token_expiry = :expiry
              WHERE user_id = :user_id";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->bindParam(":expiry", $expiry);
    $stmt->bindParam(":user_id", $user_id);
    
    return $stmt->execute();
  }
  
  public function verify_remember_token($user_id, $token) {
    $query = "SELECT user_id, username, email, role, token_expiry
              FROM " . $this->table_name . "
              WHERE user_id = :user_id AND remember_token = :token
              AND token_expiry > CURRENT_TIMESTAMP";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
      $row = $stmt->fetch();
      $this->update_last_login($user_id);
      
      ensure_session_started();
      $_SESSION['user_id'] = $user_id;
      $_SESSION['username'] = $row['username'];
      $_SESSION['email'] = $row['email'];
      $_SESSION['role'] = $row['role'];
      
      return true;
    }
    
    return false;
  }
  
  public function clear_remember_token($user_id) {
    $query = "UPDATE " . $this->table_name . "
              SET remember_token = NULL, token_expiry = NULL
              WHERE user_id = :user_id";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    
    return $stmt->execute();
  }
  
  // Admin functions for user management
  
  public function get_all_users() {
    $query = "SELECT * FROM " . $this->table_name . " ORDER BY username";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function update_role($role) {
    if (!in_array($role, ['user', 'moderator', 'admin'])) {
      return return_response(false, "Invalid role");
    }
    
    $query = "UPDATE " . $this->table_name . "
              SET role = :role
              WHERE user_id = :user_id";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":user_id", $this->user_id);
    
    if ($stmt->execute()) {
      $this->role = $role;
      return return_response(true, "Role updated successfully");
    }
    
    return return_response(false, "Failed to update role");
  }
  
  public function delete_user() {
    // First anonymize all posts and comments
    $query = "UPDATE posts SET author_id = NULL WHERE author_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->execute();
    
    $query = "UPDATE comments SET user_id = NULL WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->execute();
    
    // Remove from course enrollments
    $query = "DELETE FROM user_courses WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    $stmt->execute();
    
    // Delete the user
    $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $this->user_id);
    
    if ($stmt->execute()) {
      return return_response(true, "User deleted successfully");
    }
    
    return return_response(false, "Failed to delete user");
  }
  
  public function get_data() {
    return [
      'id' => $this->user_id,
      'username' => $this->username,
      'email' => $this->email,
      'role' => $this->role,
      'registration_date' => $this->registration_date,
      'last_login' => $this->last_login
    ];
  }
}
?>