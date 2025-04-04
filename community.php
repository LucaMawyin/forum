<?php
$pageTitle = 'CodeForum - Community';
$extraStyles = ['assets/css/community.css'];

require_once 'includes/utils.php';
require_once 'includes/config/database.php';

include 'includes/partials/header.php';
include 'includes/partials/sidebar.php';

$db = new Database();
$conn = $db->get_connection();

if (!isset($_GET['id'])) {
  redirect("index.php");
}

require_once 'includes/classes/Course.php';
$course_obj = new Course($conn);
$course_id = $_GET['id'];
$course = $course_obj->get_course_by_id($course_id);

if (!$course) {
  $page_title = "Course Not Found";
  echo '<div class="content">';
  echo '<main class="home-content">';
  echo '<div class="error-message">';
  echo '<h2>Course Not Found</h2>';
  echo '<p>The course you are looking for does not exist.</p>';
  echo '</div>';
  echo '</main>';
  echo '</div>';
  include 'includes/partials/footer.php';

  exit;
}

$course_stats = $course_obj->get_post_stats($course_id);

require_once 'includes/classes/Post.php';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recent';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$post_obj = new Post($conn);
$result = $post_obj->get_posts_by_course($course_id, $sort_by, $page);
$posts = $result['posts'];
$total_pages = $result['total_pages'];

$page_title = "Course: " . $course['course_code'];
?>

<main class="home-content">
  <div class="community-header">
    <div class="community-info">
      <h1 id="community-title"><?php echo htmlspecialchars($course['course_code']); ?>: <?php echo htmlspecialchars($course['course_name']); ?></h1>
      <p id="community-description"><?php echo htmlspecialchars($course['description']); ?></p>
    </div>
    <div class="community-stats">
      <div class="stat">
        <span class="stat-value" id="post-count"><?php echo $course_stats['total_posts']; ?></span>
        <span class="stat-label">Posts</span>
      </div>
      <div class="stat">
        <span class="stat-value" id="member-count"><?php echo $course['student_count']; ?></span>
        <span class="stat-label">Members</span>
      </div>
    </div>
  </div>

  <div class="content-header">
    <h2>Discussions</h2>
    <div class="filters">
      <form method="GET" action="" id="sort-form">
        <input type="hidden" name="id" value="<?php echo $course_id; ?>">
        <select id="sort-by" name="sort" onchange="this.form.submit()">
          <option value="recent" <?php if ($sort_by == 'recent') echo 'selected'; ?>>Most Recent</option>
          <option value="popular" <?php if ($sort_by == 'popular') echo 'selected'; ?>>Most Popular</option>
          <option value="unanswered" <?php if ($sort_by == 'unanswered') echo 'selected'; ?>>Most Unaswered</option>
        </select>
      </form>
    </div>
  </div>

  <div class="posts-container">
    <?php if (count($posts) > 0): ?>
      <?php foreach ($posts as $post): ?>
        <a href="post.php?id=<?php echo $post['post_id']; ?>" class="post-link">
          <div class="post-tile <?php echo $post['is_pinned'] ? 'pinned' : ''; ?>">
            <h3>
              <?php echo htmlspecialchars($post['title']); ?>
              <?php if ($post['is_pinned']): ?>
                <span class="pinned-badge">Pinned</span>
              <?php endif; ?>
            </h3>
            <div class="post-meta">
              <div class="post-author">
                <img src="assets/images/user.png" alt="User Avatar">
                <span>
                  <?php echo htmlspecialchars($post['username']); ?> •
                  <?php echo get_relative_time($post['created_at']); ?>
                </span>
              </div>
              <div class="post-stats">
                <span><i class="fas fa-eye"></i> <?php echo $post['view_count']; ?></span>
                <span><i class="fas fa-comment"></i> <?php echo $post['comment_count']; ?></span>
              </div>
            </div>
            <div class="post-content">
              <?php
              $preview_content = substr($post['content'], 0, 200);
              echo format_post_content($preview_content);
              if (strlen($post['content']) > 200): ?>...<?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?id=<?php echo $course_id; ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $page - 1; ?>" class="page-link">&laquo; Previous</a>
          <?php endif; ?>

          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i == $page): ?>
              <span class="page-link active"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="?id=<?php echo $course_id; ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <a href="?id=<?php echo $course_id; ?>&sort=<?php echo $sort_by; ?>&page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-comments"></i>
        <h3>No discussions yet</h3>
        <p>Be the first to start a discussion in this community!</p>
        <a href="create-post.php?course=<?php echo $course_id; ?>" class="btn btn-primary">Create Post</a>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php
include 'includes/partials/footer.php';
?>