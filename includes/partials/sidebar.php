<?php
$currentCommunity = $_GET['community'] ?? null;
?>

<nav class="communities">
    <h2>Courses</h2>
    <ul>
        <li><a href="community.php?community=1XC3" <?php if ($currentCommunity === '1XC3') echo 'class="active"'; ?>>1XC3</a></li>
        <li><a href="community.php?community=1DM3" <?php if ($currentCommunity === '1DM3') echo 'class="active"'; ?>>1DM3</a></li>
        <li><a href="community.php?community=2C03" <?php if ($currentCommunity === '2C03') echo 'class="active"'; ?>>2C03</a></li>
        <li><a href="community.php?community=3FP3" <?php if ($currentCommunity === '3FP3') echo 'class="active"'; ?>>3FP3</a></li>
    </ul>
</nav>