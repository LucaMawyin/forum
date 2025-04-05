<?php
// NOTE: register.php and login.php have SEPERATE header
require_once 'includes/config/database.php';
require_once 'includes/utils.php';

$database = new Database();
$conn = $database->get_connection();

// All Courses Variable for Sidebar
require_once "includes/classes/Course.php";
$course_obj = new Course($conn);
$all_courses = $course_obj->get_all_courses();

$is_logged_in = isset($_SESSION['user_id']);
$current_user = null;

if ($is_logged_in) {
  $user_id = $_SESSION['user_id'];
  $username = $_SESSION['username'];
  $user_role = $_SESSION['role'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $page_title ?? 'CodeForum'; ?></title>
  <link rel="stylesheet" href="assets/css/global.css">
  <?php
  if (isset($extra_styles)):
    if (is_array($extra_styles)):
      foreach ($extra_styles as $style):
        echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">';
      endforeach;
    else:
      echo $extra_styles;
    endif;
  endif;
  ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
</head>

<body>
  <header>
    <a href="index.php">
      <h1>CodeForum</h1>
    </a>
    <div class="search-container">
      <input type="text" placeholder="Search forums...">
      <button class="search-btn"><i class="fa fa-search"></i></button>
    </div>
    <div id="nav-button-container">
      <?php if ($is_logged_in): ?>
        TODO
      <?php else: ?>
        <a href="register.php"><button class="account">Register</button></a>
        <a href="login.php"><button class="account">Log In</button></a>
      <?php endif; ?>
    </div>
  </header>

  <div class="content">