<?php
$pageTitle = 'CodeForum - Community';
$extraStyles = ['/assets/css/community.css'];
include 'includes/partials/header.php';
include 'includes/partials/sidebar-empty.php';
?>

<main class="home-content">
    <div class="community-header">
        <div class="community-info">
            <h1 id="community-title">Course Title</h1>
            <p id="community-description">Course description will appear here.</p>
        </div>
        <div class="community-stats">
            <div class="stat">
                <span class="stat-value" id="post-count">0</span>
                <span class="stat-label">Posts</span>
            </div>
            <div class="stat">
                <span class="stat-value" id="member-count">0</span>
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
$extraScripts = ['/assets/js/home.js'];
include 'includes/partials/footer.php';
?>