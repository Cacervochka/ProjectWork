<?php
session_start();
$action = $_GET["action"] ?? "1";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/authorisation.css">
    <script defer src="js/colorTheme.js"></script>
    <script defer src="js/lang.js"></script>

</head>

<body class="auth-page">
    <main class="auth-shell">
        <div class="auth-topbar">
            <a class="auth-brand" href="index.php">Grace</a>
            <button class="auth-lang-toggle" id="langToggle" type="button" aria-label="Toggle language" data-i18n-aria="language.toggle">
                <img src="ico/change-language.svg" alt="" aria-hidden="true">
                <span data-lang-code>EN</span>
            </button>
        </div>
        <?php
        if ($action == "1") {
            require("./includes/login.php");
        } elseif ($action == "2") {
            require("./includes/register.php");
        } else {
            require("./includes/logout.php");
        }
        ?>
    </main>
</body>

</html>
