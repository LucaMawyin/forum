<?php
$page_title = "Create Post";
$extra_styles = ["assets/css/create.css"];
$extra_scripts = ["assets/js/create-post.js"];
require_once 'includes/partials/header.php';
require_once 'includes/partials/sidebar.php';

// Post submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $course_id = isset($_POST['course_id']) ? clean_input($_POST['course_id']) : '';
  $title = isset($_POST['title']) ? clean_input($_POST['title']) : '';
  $content = isset($_POST['content']) ? clean_input($_POST['content']) : '';

  if (empty($course_id) || empty($title) || empty($content)) {
    $_SESSION['post_error'] = "All fields are required.";
  }

  if (strlen($title) > 100) {
    $_SESSION['post_error'] = "Title must be 100 characters or less.";
  }

  require_once 'includes/classes/Post.php';
  $post = new Post($conn);
  $result = $post->create_post(1, $course_id, $title, $content);
  if ($result['success']) {
    redirect("post.php?id=" . $result['post_id']);
  } else {
    $_SESSION['post_error'] = $result['message'];
  }
}

require_once 'includes/classes/Course.php';
$course_obj = new Course($conn);
$all_courses = $course_obj->get_all_courses();

$selected_course = null;
if (isset($_GET['course'])) {
  $course_id = $_GET['course'];
  foreach ($all_courses as $course) {
    if ($course_id == $course['course_id']) {
      $selected_course = $course;
      break;
    }
  }
}
?>

<div class="content">
  <main class="create-content">
    <div class="create-header">
      <h2>Create a New Post</h2>
      <p>Share your question, idea, or code with the community.</p>
    </div>

    <?php if (isset($_SESSION['post_error'])): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['post_error']; ?>
      </div>
      <?php unset($_SESSION['post_error']); ?>
    <?php endif; ?>

    <form class="post-form" action="create-post.php" method="post">
      <div class="form-group">
        <label for="course">Course</label>
        <select id="course" name="course_id" required>
          <option value="" disabled <?php echo $selected_course ? '' : 'selected'; ?>>Select a course</option>
          <?php foreach ($all_courses as $course): ?>
            <option value="<?php echo $course['course_id'] ;?>" <?php echo ($selected_course && $selected_course['course_id'] == $course['course_id']);?>>
              <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" placeholder="Be specific and concise" required maxlength="100">
        <div class="input-help">Good titles are specific and summarize the problem</div>
      </div>
      <div class="form-group">
        <label for="content">Post Content</label>
        <textarea id="content" name="content" placeholder="Describe your problem or share your code..." required></textarea>
        <div class="input-help">Be clear and detailed. Include relevant code and explain what you've tried.</div>
      </div>
      <div class="form-preview">
        <h3>Preview</h3>
        <div id="content-preview" class="preview-area">
          <p class="preview-placeholder">Your content preview will appear here...</p>
        </div>
      </div>
      <div class="form-buttons">
        <a href="index.php" class="btn btn-cancel">Cancel</a>
        <button type="submit" class="btn btn-submit">Post</button>
      </div>
    </form>
  </main>
</div>

<?php
require_once 'includes/partials/footer.php';
?>