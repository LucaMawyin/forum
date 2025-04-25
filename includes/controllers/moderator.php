<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Post.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin or moderator
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'moderator') {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn, $_SESSION['user_id']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pin/unpin post
    if (isset($_POST['pin_post']) || isset($_POST['unpin_post'])) {
        $post_id = $_POST['post_id'];
        $post = new Post($conn);
        $result = $post->toggle_pin($post_id, $_SESSION['user_id']);
        
        if (isset($_GET['course_id']) && isset($_GET['post_id'])) {
            header("Location: community.php?course_id=" . $_GET['course_id'] . "&post_id=" . $_GET['post_id'] . "&action=pin");
        } else {
            header("Location: admin.php?tab=posts&success=post_updated");
        }
        exit();
    }
    
    // Close/reopen post
    if (isset($_POST['close_post']) || isset($_POST['reopen_post'])) {
        $post_id = $_POST['post_id'];
        $post = new Post($conn);
        $result = $post->toggle_close($post_id, $_SESSION['user_id']);
        
        if (isset($_GET['course_id']) && isset($_GET['post_id'])) {
            header("Location: community.php?course_id=" . $_GET['course_id'] . "&post_id=" . $_GET['post_id'] . "&action=close");
        } else {
            header("Location: admin.php?tab=posts&success=post_updated");
        }
        exit();
    }
    
    // Delete post
    if (isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];
        $post = new Post($conn);
        $result = $post->delete_post($post_id, $_SESSION['user_id']);
        
        if (isset($_GET['course_id'])) {
            header("Location: community.php?course_id=" . $_GET['course_id'] . "&action=delete");
        } else {
            header("Location: admin.php?tab=posts&success=post_deleted");
        }
        exit();
    }
}

// No direct access to this file
header("Location: index.php");
exit();
?>