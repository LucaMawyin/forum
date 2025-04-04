<?php
require_once 'includes/db.php';

// Fetching communities
$communities = [];
try {
    $stmt = $pdo->query("SELECT course_code FROM courses"); // Fetch course_code (communities)
    $communities = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch course_code as an array
} catch (PDOException $e) {
    $error_message = "Error fetching communities: " . $e->getMessage();
}

// Getting community name
$community = $_GET['community'] ?? '';
if (empty($community)) {
    $community = 'Unknown Community';
}

// Fetch course_id based on the community (course_code)
$course_id = null;
if (!empty($community)) {
    try {
        $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_code = :community");
        $stmt->execute(['community' => $community]);
        $course_id = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $error_message = "Error fetching course_id: " . $e->getMessage();
    }
}

// Fetching posts for the selected community (course_id)
$posts = [];
if ($course_id) {
    try {
        $stmt = $pdo->prepare("SELECT p.post_id, p.title, p.content, u.username
                               FROM posts p
                               JOIN users u ON p.user_id = u.user_id
                               WHERE p.course_id = :course_id
                               ORDER BY p.created_at DESC");
        $stmt->execute(['course_id' => $course_id]);
        $posts = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error fetching posts: " . $e->getMessage();
    }
} else {
    $error_message = "Community not found.";
}

?>

<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum Board Communtiy</title>
    <link rel="stylesheet" href="css/home.css">
</head>

<header>
    <a href="index.php"><h1>
        <?php 
            $community = $_GET['community'] ?? 'ERROR';
            echo "Class: " . htmlspecialchars($community);
        ?>
    </h1></a>
    <input type="text" placeholder="Search">
    <div id="nav-button-container">
        <a href="createPost.php"><button id="create">Create</button></a>
        <a href="login.html"><button id="account">Log In</button></a>
    </div>
</header>

<body>

    <div class="content">
        <nav class="communities">
            <ul>
                <?php if (!empty($communities)): ?>
                    <?php foreach ($communities as $community): ?>
                        <li>
                            <a href="community.php?community=<?= urlencode($community) ?>"><?= htmlspecialchars($community) ?></a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No communities available.</li>
                <?php endif; ?>
            </ul>
        </nav>
    
        <div class="home-content">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <a href="post.php?post_id=<?= $post['post_id'] ?>" class="post-link">
                        <div class="post-tile">
                            <h1><?= htmlspecialchars($post['title']) ?></h1>
                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </div>
    </div>



</body>


</html>