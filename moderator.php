<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Post.php';

session_start();

// Redirect if not moderator or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'moderator' && $_SESSION['role'] !== 'admin')) {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn, $_SESSION['user_id']);

// Set the active tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';

// Get recent posts
$post = new Post($conn);
$recent_posts = $post->get_recent_posts(20);

// Page title
$page_title = 'Moderator Panel';

include 'includes/partials/header.php';
?>

<div class="container mt-4">
    <h1>Moderator Panel</h1>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'posts' ? 'active' : ''; ?>" href="moderator.php?tab=posts">Manage Posts</a>
        </li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li class="nav-item">
            <a class="nav-link" href="admin.php">Admin Dashboard</a>
        </li>
        <?php endif; ?>
    </ul>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $success = $_GET['success'];
        switch ($success) {
            case 'post_updated':
                echo "Post updated successfully!";
                break;
            case 'post_deleted':
                echo "Post deleted successfully!";
                break;
            default:
                echo "Operation completed successfully!";
        }
        ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Recent Posts</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_posts as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['title']); ?></td>
                        <td><?php 
                            $author = new User($conn, $p['author_id']);
                            $author_data = $author->get_data();
                            echo htmlspecialchars($author_data['username']); 
                        ?></td>
                        <td><?php 
                            $post_course = new Course($conn, $p['course_id']);
                            $course_data = $post_course->get_course_data();
                            echo htmlspecialchars($course_data['title']); 
                        ?></td>
                        <td>
                            <?php if ($p['is_pinned']): ?>
                                <span class="badge bg-info">Pinned</span>
                            <?php endif; ?>
                            <?php if ($p['is_closed']): ?>
                                <span class="badge bg-secondary">Closed</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($p['created_at'])); ?></td>
                        <td>
                            <a href="community.php?course_id=<?php echo $p['course_id']; ?>&post_id=<?php echo $p['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <form method="post" action="includes/controllers/moderator.php?course_id=<?php echo $p['course_id']; ?>&post_id=<?php echo $p['id']; ?>" class="d-inline">
                                <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="<?php echo $p['is_pinned'] ? 'unpin_post' : 'pin_post'; ?>" class="btn btn-sm <?php echo $p['is_pinned'] ? 'btn-secondary' : 'btn-success'; ?>">
                                    <?php echo $p['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                                </button>
                                <button type="submit" name="<?php echo $p['is_closed'] ? 'reopen_post' : 'close_post'; ?>" class="btn btn-sm <?php echo $p['is_closed'] ? 'btn-warning' : 'btn-secondary'; ?>">
                                    <?php echo $p['is_closed'] ? 'Reopen' : 'Close'; ?>
                                </button>
                                <button type="submit" name="delete_post" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>