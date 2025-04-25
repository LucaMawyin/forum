<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/Comment.php';
require_once 'includes/classes/User.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete comment
    if (isset($_POST['delete_comment']) && isset($_POST['comment_id']) && isset($_POST['post_id'])) {
        $comment_id = $_POST['comment_id'];
        $post_id = $_POST['post_id'];
        
        $comment = new Comment($conn);
        $result = $comment->delete_comment($comment_id, $_SESSION['user_id']);
        
        header("Location: post.php?id=$post_id&comment_action=deleted#comments");
        exit();
    }
}

// Redirect back to the home page if accessed directly
header("Location: index.php");
exit();
?>