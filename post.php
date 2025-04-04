<!DOCTYPE html>
<html lang="en">
<?php
    include "includes/db.php";

    // Initialize the post variable
    $post = null;
    try {

        $postid = (int)$_GET['post_id'];  // Get post_id from URL

        // Fetch post details from the database
        $cmd = "SELECT p.post_id, p.title, p.content, u.username
                FROM posts p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.post_id = ?";
        $stmt = $pdo->prepare($cmd);
        $stmt->execute([$postid]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the post exists
        if (!$post) {
            die("Post not found.");
        }

        // Handle comment submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userid = filter_input(INPUT_POST, "userid", FILTER_VALIDATE_INT);
            $content = filter_input(INPUT_POST, "content", FILTER_SANITIZE_SPECIAL_CHARS);

            // Prepare the SQL command to insert a new comment
            $cmd = "INSERT INTO comments (user_id, content, post_id) VALUES (?, ?, ?);";
            $args = [$userid, $content, $postid];
            $stmt = $pdo->prepare($cmd);

            // Execute the command
            $success = $stmt->execute($args);

            // Check if the insertion was successful
            if (!$success) {
                die("Oops, SQL command failed.");
            }

            // Redirect to the same page to avoid re-submission of the form
            header("Location: post.php?post_id=$postid");
            exit;
        }

        // Fetch comments for the post
        $cmd = "SELECT c.user_id, c.content, c.created_at, u.username
                FROM comments c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.post_id = ?
                ORDER BY c.created_at DESC";
        $stmt_comments = $pdo->prepare($cmd); // Use a different statement for comments
        $stmt_comments->execute([$postid]);
        $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    // Fetching communities
    $communities = [];
    try {
        $stmt = $pdo->query("SELECT course_code FROM courses");
        $communities = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        $error_message = "Error fetching communities: " . $e->getMessage();
    }
?>

<head>
    <meta charset="utf-8" name="viewport" content="width=device-width">
    <title>Forum Board Post</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="js/post.js"></script>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/post.css">
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

        <div id="post-content">

            <?php if ($post): ?>
                <div id="post">
                    <div class="user-info">
                        <img src="images/user.png" alt="">
                        <p><?= htmlspecialchars($post['username']) ?></p>
                    </div>
                    <div class="text-content">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                    </div>
                    <div id="image-content"></div>

                    <div class="buttons">
                        <input type="button" value="Reply">
                        <input type="button" value="Like">
                        <input type="button" value="Report">
                    </div>
                </div>
            <?php else: ?>
                <p>Post not found.</p>
            <?php endif; ?>
            <div id="reply-tab">
                <span style="font-size: 1.25em; margin-right: 1vw;">&#x25B2;</span>
                <h3>Replies</h3>
                <button id="addreply">Add Reply</button>
            </div>

            <div id="replies">
                <form id="replyeditor" method="post" action="post.php?post_id=<?= $postid ?>">
                    <input name="userid" type="hidden" value="1">
                    <div id="replycontent">
                        <textarea name="content" placeholder="Write your reply here..." required></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-submit">Post</button>
                    </div>
                </form>
                <?php foreach ($comments as $comment): ?>
                    <div class="reply">
                        <div class="user-info">
                            <img src="images/user.png" alt="">
                            <p><?= htmlspecialchars($comment['username']) ?></p>
                        </div>
                        <div class="image-content"></div>

                        <div class="text-content">
                            <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        </div>
                        <div class="buttons">
                            <input type="button" value="Reply">
                            <input type="button" value="Like">
                            <input type="button" value="Report">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    
        
    </div>

</body>

</html>