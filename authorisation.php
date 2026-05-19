<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grace</title>
    <link rel="stylesheet" href="./css/authorisation.css">
    <link rel="stylesheet" href="./css/style.css">
    <script defer src="js/colorTheme.js"></script>

</head>

<body>
    <div>
        <h1>Grace</h1>
        <?php
        $action = $_GET["action"];
        if ($action == "1") {
            require("./includes/login.php");
        } elseif ($action == "2") {
            require("./includes/register.php");
        } else {
            require("./includes/logout.php");
        }
        ?>
    </div>
</body>

</html>