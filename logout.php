<?php
require_once 'includes/utils.php';
require_once 'includes/config/database.php';
require_once 'includes/classes/User.php';
ensure_session_started();

if (is_logged_in()) {
  $database = new Database();
  $conn = $database->get_connection();
  $user = new User($conn);
  $user->logout();
}

redirect("login.php");
?>