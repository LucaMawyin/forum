<?php
class Database {
  private $host = "localhost";
  private $db_name = "dovbenys_db";
  private $username = "root";
  private $password = "";
  private $conn;

  public function get_connection() {
    $this->conn = null;

    try {
      $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
      echo "Connection Error: " . $e->getMessage();
    }

    return $this->conn;
  }
}
