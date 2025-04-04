<?php

require_once 'includes/db.php';

// HARDCODED USER ID
$user_id = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Defaults inputes to empty
    $course_id = $_POST['course_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Making sure inputs are filled
    if ($course_id && $title && $content) {
        try{
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, course_id, title, content) VALUES (:user_id, :course_id, :title, :content)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':course_id' => $course_id,
                ':title' => $title,
                ':content' => $content
            ]);

            // Redirect to home once uploaded
            header("Location: index.php");
            exit;
        }catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } 
    
    // Error if user does not enter requirements
    else{
        $error_message = "Please fill in all required fields.";
    }
}

// Setting options to available courses
$courses = [];
try {
    $stmt = $pdo->query("SELECT course_id, course_code FROM courses");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Could not load communities.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Forum Post</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/create.css">
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
        <h2>Create a New Post</h2>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form class="post-form" method="POST" action="createPost.php">
            <div>
                <label for="community">Community</label>
                <select id="community-menu" name="course_id" required>
                    <option value="">-- Select a Community --</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>">
                            <?= htmlspecialchars($course['course_code']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="title">Title</label>
                <input type="text" id="title" name="title" placeholder="Enter post title" required>
            </div>

            <div>
                <label for="content">Post Content</label>
                <textarea id="content" name="content" placeholder="Write your post here..." required></textarea>
            </div>

            <div class="form-buttons">
                <button type="reset" class="btn-cancel"><a href="index.php" style="color:white;">Cancel</a></button>
                <button type="submit" class="btn-submit">Post</button>
            </div>
        </form>
    </div>
</body>
</html>
