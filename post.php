<?php
$extra_styles = ['assets/css/community.css'];
require_once 'includes/utils.php';
require_once 'includes/config/database.php';
require_once 'includes/classes/Post.php';
require_once 'includes/classes/User.php';

if (!isset($_GET['id'])) {
    redirect("index.php");
}

$post_id = $_GET['id'];
$db = new Database();
$conn = $db->getConnection();
$post_obj = new Post($conn);
$post = $post_obj->get_post_by_id($post_id);

if (!$post) {
    redirect("index.php");
}

$page_title = $post['title'] . ' - CodeForum';

// Check if user is logged in
$is_logged_in = is_logged_in();
$is_author = $is_logged_in && $_SESSION['user_id'] == $post['user_id'];

// Check if user is moderator or admin
$can_moderate = false;
if ($is_logged_in) {
    // Check site-wide role
    if ($_SESSION['role'] === 'moderator' || $_SESSION['role'] === 'admin') {
        $can_moderate = true;
    } else {
        // Check course-specific role
        $user_obj = new User($conn);
        $user_courses = $user_obj->get_user_courses($_SESSION['user_id']);
        foreach ($user_courses as $course) {
            if ($course['course_id'] == $post['course_id'] && 
                ($course['user_role'] === 'moderator' || $course['user_role'] === 'admin')) {
                $can_moderate = true;
                break;
            }
        }
    }
}

// Handle post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_comment']) && $is_logged_in) {
        $content = $_POST['comment_content'];
        if (!empty($content)) {
            $comment_obj = new Comment($conn);
            $result = $comment_obj->create_comment($_SESSION['user_id'], $post_id, $content);
            if ($result['success']) {
                redirect("post.php?id=$post_id#comment-" . $result['comment_id']);
            }
        }
    }
    
    if ($can_moderate) {
        // Handle pin/unpin
        if (isset($_POST['pin_post']) || isset($_POST['unpin_post'])) {
            $post_obj->toggle_pin($post_id, $_SESSION['user_id']);
            redirect("post.php?id=$post_id&action=pin");
        }
        
        // Handle close/reopen
        if (isset($_POST['close_post']) || isset($_POST['reopen_post'])) {
            $post_obj->toggle_close($post_id, $_SESSION['user_id']);
            redirect("post.php?id=$post_id&action=close");
        }
    }
    
    // Handle delete
    if ((isset($_POST['delete_post']) && ($is_author || $can_moderate))) {
        $post_obj->delete_post($post_id, $_SESSION['user_id']);
        redirect("community.php?id=" . $post['course_id']);
    }
}

// Get comments
require_once 'includes/classes/Comment.php';
$comment_obj = new Comment($conn);
$comments = $comment_obj->get_comments_by_post($post_id);

include 'includes/partials/header.php';
include 'includes/partials/sidebar.php';
?>

<main class="post-content">
    <?php if (isset($_GET['action'])): ?>
    <div class="alert alert-success">
        <?php if ($_GET['action'] === 'pin'): ?>
            Post has been <?php echo $post['is_pinned'] ? 'pinned' : 'unpinned'; ?>.
        <?php elseif ($_GET['action'] === 'close'): ?>
            Post has been <?php echo $post['is_closed'] ? 'closed' : 'reopened'; ?>.
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="post-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="post-meta">
            <div class="post-author">
                <img src="assets/images/user.png" alt="User Avatar">
                <span>
                    <?php echo htmlspecialchars($post['username']); ?> • 
                    <?php echo get_relative_time($post['created_at']); ?>
                </span>
            </div>
            <div class="post-actions">
                <?php if ($can_moderate): ?>
                <form method="post" class="d-inline">
                    <button type="submit" name="<?php echo $post['is_pinned'] ? 'unpin_post' : 'pin_post'; ?>" class="btn btn-sm <?php echo $post['is_pinned'] ? 'btn-secondary' : 'btn-success'; ?>">
                        <?php echo $post['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                    </button>
                    <button type="submit" name="<?php echo $post['is_closed'] ? 'reopen_post' : 'close_post'; ?>" class="btn btn-sm <?php echo $post['is_closed'] ? 'btn-warning' : 'btn-secondary'; ?>">
                        <?php echo $post['is_closed'] ? 'Reopen' : 'Close'; ?>
                    </button>
                </form>
                <?php endif; ?>
                
                <?php if ($is_author || $can_moderate): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?')">
                    <button type="submit" name="delete_post" class="btn btn-sm btn-danger">Delete</button>
                </form>
                <?php endif; ?>
                
                <?php if ($is_author): ?>
                <a href="edit-post.php?id=<?php echo $post_id; ?>" class="btn btn-sm btn-primary">Edit</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($post['is_pinned'] || $post['is_closed']): ?>
        <div class="post-status">
            <?php if ($post['is_pinned']): ?>
            <span class="badge bg-info">Pinned</span>
            <?php endif; ?>
            <?php if ($post['is_closed']): ?>
            <span class="badge bg-secondary">Closed</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="post-body">
        <?php echo format_post_content($post['content']); ?>
    </div>
    
    <div class="post-stats">
        <span><i class="fas fa-eye"></i> <?php echo $post['view_count']; ?> views</span>
        <span><i class="fas fa-comment"></i> <?php echo count($comments); ?> comments</span>
    </div>
    
    <hr>
    
    <div class="comments-section">
        <h3>Comments</h3>
        
        <?php if ($post['is_closed']): ?>
        <div class="closed-message">
            <p><i class="fas fa-lock"></i> This post is closed. New comments are not allowed.</p>
        </div>
        <?php elseif ($is_logged_in): ?>
        <div class="comment-form">
            <form method="post">
                <div class="form-group">
                    <textarea name="comment_content" rows="3" class="form-control" placeholder="Write your comment here..."></textarea>
                </div>
                <button type="submit" name="submit_comment" class="btn btn-primary">Post Comment</button>
            </form>
        </div>
        <?php else: ?>
        <div class="login-message">
            <p>Please <a href="login.php">log in</a> to post a comment.</p>
        </div>
        <?php endif; ?>
        
        <div class="comments-list">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                <div class="comment" id="comment-<?php echo $comment['comment_id']; ?>">
                    <div class="comment-header">
                        <div class="comment-author">
                            <img src="assets/images/user.png" alt="User Avatar">
                            <span>
                                <?php echo htmlspecialchars($comment['username']); ?> • 
                                <?php echo get_relative_time($comment['created_at']); ?>
                            </span>
                        </div>
                        <?php if ($is_logged_in && ($_SESSION['user_id'] == $comment['user_id'] || $can_moderate)): ?>
                        <div class="comment-actions">
                            <form method="post" action="includes/controllers/comment.php">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                                <button type="submit" name="delete_comment" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-body">
                        <?php echo format_post_content($comment['content']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-comments">
                    <p>No comments yet. Be the first to comment!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/partials/footer.php'; ?>