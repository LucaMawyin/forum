<?php
$pageTitle = 'CodeForum - Community';
$extraStyles = ['/assets/css/community.css'];

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

$page_title = "Course: " . $course['course_code'];
?>

<main class="home-content">
    <div class="community-header">
        <div class="community-info">
            <h1 id="community-title"><?php echo htmlspecialchars($course['course_code']); ?>: <?php echo htmlspecialchars($course['course_name']);?></h1>
            <p id="community-description"><?php echo htmlspecialchars($course['description']); ?></p>
        </div>
        <div class="community-stats">
            <div class="stat">
                <span class="stat-value" id="post-count">0</span>
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
            <select id="sort-by">
                <option value="recent">Most Recent</option>
                <option value="popular">Most Popular</option>
                <option value="unanswered">Unanswered</option>
            </select>
        </div>
    </div>
    
    <div class="posts-container">
        <div class="loading-indicator">
            <div class="spinner"></div>
            <p>Loading discussions...</p>
        </div>
    </div>
</main>

<?php
include 'includes/partials/footer.php';
?>