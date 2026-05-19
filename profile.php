<?php
session_start();
$pageTitle = 'Profile';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$error = '';

/* ======================
   LOGIN
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ];

        header('Location: profile.php');
        exit;
    } else {
        $error = 'Incorrect email or password.';
    }
}

/* ======================
   REGISTER
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        $error = 'Email already registered.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password_hash, created_at)
            VALUES (:name, :email, :password, NOW())
        ');

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash
        ]);

        // auto login after register
        $_SESSION['user'] = [
            'id' => $pdo->lastInsertId(),
            'name' => $name,
            'email' => $email,
        ];

        header('Location: profile.php');
        exit;
    }
}

/* ======================
   LOGOUT
====================== */
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: profile.php');
    exit;
}

/* ======================
   CURRENT USER
====================== */
$user = $_SESSION['user'] ?? null;

/* view part */

if (isset($_GET['viewSection'])) {
    $sections = array("1", "2", "3");
    if (in_array($_GET['viewSection'], $sections)) {
        $section = $_GET['viewSection'];
    } else {
        $section = "1";
    }
} else {
    $section = "1";
}


?>

<section class="section profileSection">
    <div class="profileContent">


        <?php if ($user): ?>
            <div class="profile-card">
                <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
                <?php if ($section == "1"){
                        include_once __DIR__ . '/includes/profileTickets.php';
                    } elseif ($section == "2") {
                        include_once __DIR__ . '/includes/profileReviews.php';
                    } elseif($section == "3") {
                        include_once __DIR__ . '/includes/profileSettings.php';
                    }?>
            </div>
        <?php else: ?>
            <h1>Profile</h1>
            <div class="noUserContent">
                <span class="guestUserIcon"><svg viewBox="0 0 512 512">
                        <path d="M256,0C114.62,0,0,114.62,0,256a256,256,0,1,0,512,0C512,114.62,397.38,0,256,0Zm0,74.27a82.21,82.21,0,1,1-82.21,82.21A82.21,82.21,0,0,1,256,74.27Zm0,408.05A225.77,225.77,0,0,1,85.79,405.16a186.92,186.92,0,0,1,340.42,0A225.77,225.77,0,0,1,256,482.32Z" />
                    </svg></span>
                <h3>Guest View</h3>
                <p>You are currently viewing this website as a guest. <br> Please log into an account to view profile information.</p>
                <div>
                    <button><a href="authorisation.php?action=1">Log in</a></button>
                    <button><a href="authorisation.php?action=2">Register</a></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
