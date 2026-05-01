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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Fjalla+One&family=Story+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/hero.css">
    <script defer src="js/app.js"></script>
</head>
<body class="darkTheme <?= htmlspecialchars($bodyClass ?? '') ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand fjalla-one-regular" href="index.php">CineView</a>
        <div class="header-actions">
            <nav class="site-nav bebas-neue-regular" id="siteNav">
                <a class="<?= navActive('index.php') ?>" href="index.php">Home</a>
                <a class="<?= navActive('programs.php') ?>" href="programs.php">Programs</a>
                <a class="<?= navActive('current.php') ?>" href="current.php">Current</a>
                <div class="nav-item nav-item-dropdown">
                    <a class="<?= navActive('menu.php') ?>" href="menu.php" aria-haspopup="true">Menu</a>
                    <div class="nav-dropdown" aria-label="Menu categories">
                        <a href="menu.php#snacks">Snacks</a>
                        <a href="menu.php#drinks">Drinks</a>
                        <a href="menu.php#combo-deals">Combo Deals</a>
                        <a href="menu.php#desserts">Desserts</a>
                    </div>
                </div>
                <a class="<?= navActive('info.php') ?>" href="info.php">Info</a>
                <a class="<?= navActive('profile.php') ?>" href="profile.php">Profile</a>
            </nav>
            <form class="header-search fjalla-one-regular" action="programs.php" method="get">
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
<main class="<?= htmlspecialchars($mainClass ?? 'container') ?>">
