<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Course.php';
require_once 'includes/classes/Post.php';

session_start();

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn, $_SESSION['user_id']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new course
    if (isset($_POST['create_course'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $course = new Course($conn);
        $course->create_course($title, $description, $_SESSION['user_id']);
        header("Location: admin.php?tab=courses&success=course_created");
        exit();
    }
    
    // Update user role
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        $target_user = new User($conn, $user_id);
        $target_user->update_role($role);
        header("Location: admin.php?tab=users&success=role_updated");
        exit();
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $target_user = new User($conn, $user_id);
        $target_user->delete_user();
        header("Location: admin.php?tab=users&success=user_deleted");
        exit();
    }
    
    // Delete course
    if (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        $course = new Course($conn, $course_id);
        $course->delete_course();
        header("Location: admin.php?tab=courses&success=course_deleted");
        exit();
    }
}

// Get active tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// Get users list
$users = $user->get_all_users();

// Get courses list
$course = new Course($conn);
$courses = $course->get_all_courses();

// Get recent posts
$post = new Post($conn);
$recent_posts = $post->get_recent_posts(10);

include 'includes/partials/header.php';
?>

<div class="container mt-4">
    <h1>Admin Dashboard</h1>
    
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'dashboard' ? 'active' : ''; ?>" href="admin.php?tab=dashboard">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'users' ? 'active' : ''; ?>" href="admin.php?tab=users">Manage Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'courses' ? 'active' : ''; ?>" href="admin.php?tab=courses">Manage Courses</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'posts' ? 'active' : ''; ?>" href="admin.php?tab=posts">Recent Posts</a>
        </li>
    </ul>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $success = $_GET['success'];
        switch ($success) {
            case 'course_created':
                echo "Course created successfully!";
                break;
            case 'role_updated':
                echo "User role updated successfully!";
                break;
            case 'user_deleted':
                echo "User deleted successfully!";
                break;
            case 'course_deleted':
                echo "Course deleted successfully!";
                break;
            default:
                echo "Operation completed successfully!";
        }
        ?>
    </div>
    <?php endif; ?>
    
    <?php if ($tab === 'dashboard'): ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Total users: <?php echo count($users); ?></p>
                    <a href="admin.php?tab=users" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Courses</h5>
                    <p class="card-text">Total courses: <?php echo count($courses); ?></p>
                    <a href="admin.php?tab=courses" class="btn btn-primary">Manage Courses</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Posts</h5>
                    <p class="card-text">Recent posts: <?php echo count($recent_posts); ?></p>
                    <a href="admin.php?tab=posts" class="btn btn-primary">View Recent Posts</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tab === 'users'): ?>
    <div class="card">
        <div class="card-header">
            <h5>Manage Users</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['role']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUser<?php echo $u['id']; ?>">
                                Edit
                            </button>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUser<?php echo $u['id']; ?>">
                                Delete
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUser<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit User Role</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-select" name="role" id="role">
                                                <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="moderator" <?php echo $u['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="update_role" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delete User Modal -->
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <div class="modal fade" id="deleteUser<?php echo $u['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete user <strong><?php echo htmlspecialchars($u['username']); ?></strong>?</p>
                                    <p class="text-danger">This action cannot be undone!</p>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tab === 'courses'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Manage Courses</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourse">
                Create New Course
            </button>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Creator</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['title']); ?></td>
                        <td><?php 
                            $creator = new User($conn, $c['creator_id']);
                            $creator_data = $creator->get_data();
                            echo htmlspecialchars($creator_data['username']); 
                        ?></td>
                        <td><?php echo date('Y-m-d', strtotime($c['created_at'])); ?></td>
                        <td>
                            <a href="community.php?course_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-info">View</a>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCourse<?php echo $c['id']; ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Delete Course Modal -->
                    <div class="modal fade" id="deleteCourse<?php echo $c['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Course</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete course <strong><?php echo htmlspecialchars($c['title']); ?></strong>?</p>
                                    <p class="text-danger">This will permanently delete all posts and discussions in this course!</p>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="delete_course" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Create Course Modal -->
    <div class="modal fade" id="createCourse" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($tab === 'posts'): ?>
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
                            <form method="post" action="community.php?course_id=<?php echo $p['course_id']; ?>&post_id=<?php echo $p['id']; ?>" class="d-inline">
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
    <?php endif; ?>
</div>

<?php include 'includes/partials/footer.php'; ?>