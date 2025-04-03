<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle ?? 'CodeForum'; ?></title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <?php 
    if (isset($extraStyles)): 
        if (is_array($extraStyles)):
            foreach ($extraStyles as $style):
                echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">';
            endforeach;
        else:
            echo $extraStyles; 
        endif;
    endif; 
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
</head>
<body>
    <header>
        <a href="index.php"><h1>CodeForum</h1></a>
        <div class="search-container">
            <input type="text" placeholder="Search forums...">
            <button class="search-btn"><i class="fa fa-search"></i></button>
        </div>
        <div id="nav-button-container">
            <a href="createPost.php"><button id="create">Create Post</button></a>
            <a href="login.php"><button id="account">Log In</button></a>
        </div>
    </header>

    <div class="content">