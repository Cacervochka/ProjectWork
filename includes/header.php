<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentFile = basename($_SERVER['PHP_SELF']);
function navActive($filename)
{
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
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/aboutUs.css">
    <link rel="stylesheet" href="css/admin.css">
    <script defer src="js/app.js"></script>
    <script defer src="js/colorTheme.js"></script>
    <script defer src="js/lang.js"></script>


    <script defer src="js/starReview.js"></script>
</head>

<body class="<?= htmlspecialchars($bodyClass ?? '') ?>">
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php">Grace</a>

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
                    <?php if (!empty($_SESSION['user'])): ?>
                        <a class="<?= navActive('admin.php') ?>" href="admin.php" data-i18n="nav.admin">Admin</a>
                    <?php endif; ?>
                </nav>
                <form class="header-search fjalla-one-regular" action="programs.php" method="get">
                    <input
                        type="search"
                        name="q"
                        placeholder="Search movies..."
                        aria-label="Search movies"
                        data-i18n-placeholder="search.placeholder"
                        data-i18n-aria="search.aria"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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

                    <span class="colorTheme"><svg viewBox="0 0 511.44 511.44">
                            <path id="path_1" data-name="path 1" d="M255.72,97.72a158,158,0,0,0-158,158c0,87.12,70.88,158,158,158a158.19,158.19,0,0,0,140.73-86.2,157.48,157.48,0,0,0,17.27-71.8C413.72,168.6,342.84,97.72,255.72,97.72Zm0,300c-78.3,0-142-63.7-142-142A141.92,141.92,0,0,1,173.79,139.8a157.29,157.29,0,0,0-7.14,47c0,87.12,70.88,158,158,158a157.29,157.29,0,0,0,47-7.14A141.92,141.92,0,0,1,255.72,397.72Z" />
                            <rect id="path5" x="248.87" y="436.89" width="17.57" height="74.55" rx="8.79" transform="translate(519.51 946.04) rotate(179.49)" />
                            <rect id="path6" x="93.84" y="374.28" width="17.57" height="74.55" rx="8.79" transform="translate(-112.59 777.06) rotate(-135.51)" />
                            <rect id="path7" x="28.49" y="220.38" width="17.57" height="74.55" rx="8.79" transform="translate(-220.04 297.22) rotate(-90.51)" />
                            <rect id="path8" x="91.1" y="65.35" width="17.57" height="74.55" rx="8.79" transform="matrix(0.7, -0.71, 0.71, 0.7, -43.32, 101.96)" />
                            <rect id="path9" x="245" width="17.57" height="74.55" rx="8.79" transform="translate(-0.32 2.25) rotate(-0.51)" />
                            <rect id="path10" x="400.03" y="62.61" width="17.57" height="74.55" rx="8.79" transform="translate(187.19 -257.87) rotate(44.49)" />
                            <rect id="path11" x="465.38" y="216.51" width="17.57" height="74.55" rx="8.79" transform="translate(723.74 -222.61) rotate(89.49)" />
                            <rect id="path12" x="402.77" y="371.54" width="17.57" height="74.55" rx="8.79" transform="translate(991.61 401.74) rotate(134.49)" />
                        </svg></span>

            </div>
        </div>
    </header>
    <main class="<?= htmlspecialchars($mainClass ?? 'container') ?>">
