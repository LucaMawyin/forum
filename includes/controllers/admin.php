<?php
require_once 'includes/config/database.php';
require_once 'includes/classes/User.php';
require_once 'includes/classes/Course.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create course
    if (isset($_POST['create_course'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $course = new Course($conn);
        $result = $course->create_course($title, $description, $_SESSION['user_id']);
        
        header("Location: admin.php?tab=courses&success=course_created");
        exit();
    }
    
    // Delete course
    if (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        
        $course = new Course($conn, $course_id);
        $result = $course->delete_course();
        
        header("Location: admin.php?tab=courses&success=course_deleted");
        exit();
    }
    
    // Update user role
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        
        $target_user = new User($conn, $user_id);
        $result = $target_user->update_role($role);
        
        header("Location: admin.php?tab=users&success=role_updated");
        exit();
    }
    
    // Delete user
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        $target_user = new User($conn, $user_id);
        $result = $target_user->delete_user();
        
        header("Location: admin.php?tab=users&success=user_deleted");
        exit();
    }
}

// No direct access to this file
header("Location: admin.php");
exit();
?>