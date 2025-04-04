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