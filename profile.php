<?php
session_start();
$pageTitle = 'Profile';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$loginError = '';
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
    }

    $loginError = 'Incorrect email or password.';
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: profile.php');
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<section class="section">
    <h1 data-i18n="profile.title">Profile</h1>

    <?php if ($user): ?>
        <div class="profile-card">
            <h2><span data-i18n="profile.welcome">Welcome</span>, <?= htmlspecialchars($user['name']) ?></h2>
            <p><strong data-i18n="profile.email">Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p data-i18n="profile.manage">You can manage your reservations and preferences from here.</p>
            <a href="profile.php?logout=1" class="button" data-i18n="profile.logout">Log Out</a>
        </div>
    <?php else: ?>
        <div class="login-form">
            <h2 data-i18n="profile.login.title">Member Login</h2>
            <?php if ($loginError): ?>
                <p class="error" data-i18n="profile.login.error"><?= htmlspecialchars($loginError) ?></p>
            <?php endif; ?>
            <form method="post" action="profile.php">
                <label for="email" data-i18n="profile.login.email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password" data-i18n="profile.login.password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" name="login" data-i18n="profile.login.button">Log In</button>
            </form>
            <p class="note"><span data-i18n="profile.login.note">Use a registered account or add one directly to the database in</span> <code>sql/schema.sql</code>.</p>
        </div>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
