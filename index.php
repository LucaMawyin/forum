<?php
$pageTitle = 'CodeForum - Home';
$extraStyles = '';
include 'includes/partials/header.php';
include 'includes/partials/sidebar-empty.php';
?>

<main class="home-content">
    <div class="content-header">
        <h2>Recent Discussions</h2>
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
$extraScripts = '<script src="/assets/js/home.js" defer></script>';
include 'includes/partials/footer.php';
?>