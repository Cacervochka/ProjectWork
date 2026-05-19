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
    <script defer src="js/colorTheme.js"></script>
    <script defer src="js/lang.js"></script>
</head>
<body class="darkTheme <?= htmlspecialchars($bodyClass ?? '') ?>">
<header class="site-header">
    <div class="container header-inner">
        <a class="brand fjalla-one-regular" href="index.php">CineView</a>
        <div class="header-actions">
            <nav class="site-nav fjalla-one-regular" id="siteNav">
                <a class="<?= navActive('index.php') ?>" href="index.php" data-i18n="nav.home">Home</a>
                <a class="<?= navActive('programs.php') ?>" href="programs.php" data-i18n="nav.programs">Programs</a>
                <a class="<?= navActive('current.php') ?>" href="current.php" data-i18n="nav.current">Current</a>
                <div class="nav-item nav-item-dropdown">
                    <a class="<?= navActive('menu.php') ?>" href="menu.php" aria-haspopup="true" data-i18n="nav.menu">Menu</a>
                    <div class="nav-dropdown" aria-label="Menu categories">
                        <a href="menu.php#snacks" data-i18n="menu.snacks">Snacks</a>
                        <a href="menu.php#drinks" data-i18n="menu.drinks">Drinks</a>
                        <a href="menu.php#combo-deals" data-i18n="menu.combo">Combo Deals</a>
                        <a href="menu.php#desserts" data-i18n="menu.desserts">Desserts</a>
                    </div>
                </div>
                <a class="<?= navActive('info.php') ?>" href="info.php" data-i18n="nav.info">Info</a>
                <a class="<?= navActive('profile.php') ?>" href="profile.php" data-i18n="nav.profile">Profile</a>
            </nav>
            <form class="header-search fjalla-one-regular" action="programs.php" method="get">
                <input
                    type="search"
                    name="q"
                    placeholder="Search movies..."
                    aria-label="Search movies"
                    data-i18n-placeholder="search.placeholder"
                    data-i18n-aria="search.aria"
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                >
                <button class="search-button" type="submit" aria-label="Search" data-i18n-aria="search.button">
                    <img src="ico/search-icon.png" alt="" aria-hidden="true">
                </button>
            </form>
            <button class="toggle-button" id="themeToggle" aria-label="Toggle dark/light theme" data-i18n-aria="theme.toggle">
                <img class="toggleButton" src="ico/colorThemeSwitchWhite.svg" alt="" aria-hidden="true">
            </button>
            <button class="toggle-button" id="langToggle" aria-label="Toggle language" data-i18n-aria="language.toggle">
                <img class="toggleButton" src="ico/change-language.svg" alt="" aria-hidden="true">
                <span class="lang-code" data-lang-code>EN</span>
            </button>
        </div>
    </div>
</header>
<main class="<?= htmlspecialchars($mainClass ?? 'container') ?>">
