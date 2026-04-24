<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentFile = basename($_SERVER['PHP_SELF']);
function navActive($filename) {
    global $currentFile;
    return $currentFile === $filename ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CineView') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/app.js"></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">CineView</a>
        <div class="header-actions">
            <nav class="site-nav" id="siteNav">
                <a class="<?= navActive('index.php') ?>" href="index.php">Home</a>
                <a class="<?= navActive('programs.php') ?>" href="programs.php">Programs</a>
                <a class="<?= navActive('current.php') ?>" href="current.php">Current</a>
                <a class="<?= navActive('menu.php') ?>" href="menu.php">Menu</a>
                <a class="<?= navActive('info.php') ?>" href="info.php">Info</a>
                <a class="<?= navActive('profile.php') ?>" href="profile.php">Profile</a>
            </nav>
            <form class="header-search" action="programs.php" method="get">
                <input
                    type="search"
                    name="q"
                    placeholder="Search movies..."
                    aria-label="Search movies"
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                >
                <button class="search-button" type="submit" aria-label="Search">
                    <img src="ico/search-icon.png" alt="" aria-hidden="true">
                </button>
            </form>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
    </div>
</header>
<main class="container">
