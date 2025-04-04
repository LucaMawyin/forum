<?php
require_once 'includes/db.php'; // Include the database connection

// Fetch all posts from the database
$posts = [];
try {
    $stmt = $pdo->query("SELECT p.post_id, p.title, p.content, c.course_code, u.username
                         FROM posts p
                         JOIN courses c ON p.course_id = c.course_id
                         JOIN users u ON p.user_id = u.user_id
                         ORDER BY p.created_at DESC"); // Fetch posts in descending order by date
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum Board Home</title>
    <link rel="stylesheet" href="css/home.css">
    <script src="js/home.js" defer></script>
</head>

<header>
    <a href="index.php"><h1>Forum Board</h1></a>
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