<!DOCTYPE html>
<html lang="en">
<?php
    include "includes/db.php";
    try {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userid = filter_input(INPUT_POST, "userid", FILTER_VALIDATE_INT);
            $content = filter_input(INPUT_POST, "content", FILTER_SANITIZE_SPECIAL_CHARS);
            $postid = 1;
    
            //2 prepare the command
            $cmd = "INSERT INTO comments (user_id, content, post_id) VALUES (?, ?, ?);";
            $args = [$userid, $content, $postid];
            $stmt = $pdo->prepare($cmd);
        
            //3 execute the command
            $success = $stmt->execute($args); 
        
            //4 check the result
            if (!$success) {
                die("oops, SQL command failed.");
            }
            
        }
        // GET list of all replies to display
        
    
        //2 prepare the command
        $cmd = "SELECT user_id, content, parent_comment_id FROM comments ORDER BY created_at DESC LIMIT 10;";
        $stmt = $pdo->prepare($cmd);
    
        //3 execute the command
        $success = $stmt->execute(); 
    
        //4 check the result
        if (!$success) {
            die("oops, SQL command failed.");
        }      
    }
    catch (Exception $e) {
        echo("$e");
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
            </ul>
        </nav>

        <div id="post-content">

            <div id="post">
                <div class="user-info">
                    <img src="images/user.png" alt="">
                    <p>FirstName LastName</p>
                </div>
                <div class="text-content">
                    <h3>
                        title
                    </h3>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sagittis augue lectus, ac tincidunt tellus ornare volutpat. 
                        Phasellus condimentum mauris ut magna efficitur cursus. Nulla facilisi. Etiam fringilla ante tristique, interdum quam vitae, interdum ipsum. 
                        Donec et lorem quam. Donec efficitur pharetra odio, sagittis vulputate mi ornare eget. Quisque id tincidunt dui. Pellentesque ac nulla erat. 
                        Aenean porttitor mattis bibendum. Duis varius ipsum risus, eu pretium diam ultrices ut. Aliquam rhoncus ullamcorper nisl, in faucibus nibh rutrum at. 
                        Curabitur aliquet eu magna ut porttitor. Fusce ut neque id nulla commodo fermentum sed nec elit. Nunc at orci fermentum, cursus urna ac, imperdiet arcu. 
                        Nullam lectus lorem, blandit vitae felis ullamcorper, vulputate condimentum lacus. Curabitur convallis, justo in rhoncus facilisis, ligula lectus tincidunt velit, eget fermentum massa nisl id tortor. 
                        Nulla pulvinar hendrerit suscipit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer auctor orci id nisl pulvinar tempor. Nullam eros nulla, porttitor et tristique eget, maximus sed leo.
                    </p>
                </div>
                <div id="image-content"></div>
        
                <div class="buttons">
                    <input type="button"  value="Reply">
                    <input type="button" value="Like">
                    <input type="button" value="Report">
                </div>
            </div>
            <div id="reply-tab">
                <span style="font-size: 1.25em; margin-right: 1vw;">&#x25B2;</span>
                <h3>Replies</h3>
                <button id="addreply">Add Reply</button>
            </div>

            <div id="replies">
                <form id="replyeditor" method="post" action="post.php">
                    <input name="userid" type="hidden" value="1">
                    <div id="replycontent">
                        <textarea name="content" placeholder="Write your reply here..." required></textarea>
                    </div>
                    <div class="form-buttons">
                        <button type="button" class="btn-cancel">Cancel</button>
                        <button type="submit" class="btn-submit">Post</button>
                    </div>
                </form>
                <?php
                    while ($result = $stmt->fetch()) { ?>         
                        <div class="reply">
                            <div class="user-info">
                                <img src="images/user.png" alt="">
                                <p><?=$result["user_id"]?></p>
                            </div>
                            <div class="image-content"></div>
                
                            <div class="text-content">
                                <p><?=$result["content"]?></p>
                            </div>
                            <div class="buttons">
                                <input type="button" value="Reply">
                                <input type="button" value="Like">
                                <input type="button" value="Report">
                            </div>
                        </div>
     
                <?php 
                    } ?>
                
                <div class="reply">
                    <div class="user-info">
                        <img src="images/user.png" alt="">
                        <p>FirstName LastName</p>
                    </div>
                    <div class="image-content"></div>
            
        
                    <div class="text-content">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sagittis augue lectus, ac tincidunt tellus ornare volutpat. 
                            neque id nulla commodo fermentum sed nec elit. Nunc at orci fermentum, cursus urna ac, imperdiet arcu. 
                            Nullam lectus lorem, blandit vitae felis ullamcorper, vulputate condimentum lacus. Curabitur convallis, justo in rhoncus facilisis, ligula lectus tincidunt velit, eget fermentum massa nisl id tortor. 
                            Nulla pulvinar hendrerit suscipit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer auctor orci id nisl pulvinar tempor. Nullam eros nulla, porttitor et tristique eget, maximus sed leo.
                        </p>
                    </div>
                    <div class="buttons">
                        <input type="button"  value="Reply">
                        <input type="button" value="Like">
                        <input type="button" value="Report">
                    </div>
                </div>
                
                <div class="reply">
                    <div class="user-info">
                        <img src="images/user.png" alt="">
                        <p>FirstName LastName</p>
                    </div>
                    <div class="image-content"></div>
            
        
                    <div class="text-content">
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sagittis augue lectus, ac tincidunt tellus ornare volutpat. 
                            neque id nulla commodo fermentum sed nec elit. Nunc at orci fermentum, cursus urna ac, imperdiet arcu. 
                        </p>
                    </div>
                    <div class="buttons">
                        <input type="button"  value="Reply">
                        <input type="button" value="Like">
                        <input type="button" value="Report">
                    </div>

                </div>
            </div>
        </div>
    
        
    </div>

</body>

</html>