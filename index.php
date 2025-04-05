<?php
$pageTitle = 'CodeForum - Home';
include 'includes/partials/header.php';
include 'includes/partials/sidebar.php';

require_once 'includes/classes/Post.php';
$sort_by = isset($_GET['sort']) ? clean_input($_GET['sort']) : 'recent';
$post_obj = new Post($conn);
$posts = $post_obj->get_recent_posts(null, 10, $sort_by);
?>

<main class="home-content">
  <div class="content-header">
    <h2>Recent Discussions</h2>
    <div class="filters">
      <form method="GET" action="" id="sort-form">
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
              <?php echo format_title_content($post['title']); ?>
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
            <div class="post-tags">
              <i class="fas fa-book"></i>
              <span class="post-tag">
                <?php echo htmlspecialchars($post['course_code']);?>
              </span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
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