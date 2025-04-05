<?php
$current_course = $_GET['community'] ?? null;

$db = new Database();
$conn = $db->get_connection();
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