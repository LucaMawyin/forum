<?php
$current_course = $_GET['community'] ?? null;

$db = new Database();
$conn = $db->get_connection();
require_once "includes/classes/Course.php";
$course_obj = new Course($conn);
$all_courses = $course_obj->get_all_courses();
?>

<nav class="communities">
  <h2>Courses</h2>
  <ul>
    <?php foreach ($all_courses as $course): ?>
      <li>
        <a href="community.php?id=<?php echo $course['course_id']; ?>"
          <?php if (isset($current_count) && $current_course == $course['course_id']) echo "class=active"; ?>>
          <?php echo htmlspecialchars($course['course_code']); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>