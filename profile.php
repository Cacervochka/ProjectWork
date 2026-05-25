<?php
$pageTitle = 'Profile';
session_start();
include_once __DIR__ . '/includes/db.php';

$error = '';

function safeRedirectTarget(?string $target): string
{
    $target = trim($target ?? '');
    if ($target === '' || preg_match('/^[a-z][a-z0-9+.-]*:/i', $target) || str_starts_with($target, '//')) {
        return 'profile.php';
    }

    return $target;
}

/* ======================
   LOGIN
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_admin FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
        ];

        header('Location: ' . safeRedirectTarget($_POST['redirect'] ?? null));
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
            'is_admin' => false,
        ];

        header('Location: ' . safeRedirectTarget($_POST['redirect'] ?? null));
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
   REVIEW SAVE
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_review'])) {
    $user = $_SESSION['user'] ?? null;
    if (!$user) {
        header('Location: authorisation.php?action=1');
        exit;
    }

    $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $text = trim($_POST['review_text'] ?? '');
    $rating = max(0, min(5, (int) ($_POST['rating'] ?? 0)));

    if ($movieId && $text !== '') {
        $ticketCheck = $pdo->prepare('
            SELECT COUNT(*)
            FROM tickets t
            JOIN schedules s ON t.schedule_id = s.id
            WHERE t.user_id = :user_id
              AND s.movie_id = :movie_id
              AND s.show_time < NOW()
        ');
        $ticketCheck->execute([
            'user_id' => $user['id'],
            'movie_id' => $movieId,
        ]);

        if ((int) $ticketCheck->fetchColumn() > 0) {
            $existingReview = $pdo->prepare('
                SELECT id FROM reviews
                WHERE movie_id = :movie_id AND user_id = :user_id
                LIMIT 1
            ');
            $existingReview->execute([
                'movie_id' => $movieId,
                'user_id' => $user['id'],
            ]);

            if ($existingReview->fetch()) {
                $reviewStmt = $pdo->prepare('
                    UPDATE reviews
                    SET review_text = :review_text
                    WHERE movie_id = :movie_id AND user_id = :user_id
                ');
            } else {
                $reviewStmt = $pdo->prepare('
                    INSERT INTO reviews (movie_id, user_id, review_text, created_at)
                    VALUES (:movie_id, :user_id, :review_text, NOW())
                ');
            }

            $reviewStmt->execute([
                'movie_id' => $movieId,
                'user_id' => $user['id'],
                'review_text' => $text,
            ]);

            $ratingCheck = $pdo->prepare('
                SELECT id FROM movie_ratings
                WHERE movie_id = :movie_id AND user_id = :user_id
                LIMIT 1
            ');
            $ratingCheck->execute([
                'movie_id' => $movieId,
                'user_id' => $user['id'],
            ]);

            if ($ratingCheck->fetch()) {
                $ratingStmt = $pdo->prepare('
                    UPDATE movie_ratings
                    SET rating = :rating
                    WHERE movie_id = :movie_id AND user_id = :user_id
                ');
            } else {
                $ratingStmt = $pdo->prepare('
                    INSERT INTO movie_ratings (movie_id, user_id, rating)
                    VALUES (:movie_id, :user_id, :rating)
                ');
            }

            $ratingStmt->execute([
                'movie_id' => $movieId,
                'user_id' => $user['id'],
                'rating' => $rating,
            ]);
        }
    }

    header('Location: profile.php?viewSection=2');
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

include_once __DIR__ . '/includes/header.php';

?>

<section class="section profileSection">
    <div class="profileContent">


        <?php if ($user): ?>
            <div class="profile-card">
                <h2><span data-i18n="profile.welcome">Welcome</span>, <?= htmlspecialchars($user['name']) ?></h2>
                <?php if ($section == "1"){
                        include_once __DIR__ . '/includes/profileTickets.php';
                    } elseif ($section == "2") {
                        include_once __DIR__ . '/includes/profileReviews.php';
                    } elseif($section == "3") {
                        include_once __DIR__ . '/includes/profileSettings.php';
                    }?>
            </div>
        <?php else: ?>
            <h1 data-i18n="profile.title">Profile</h1>
            <div class="noUserContent">
                <span class="guestUserIcon"><svg viewBox="0 0 512 512">
                        <path d="M256,0C114.62,0,0,114.62,0,256a256,256,0,1,0,512,0C512,114.62,397.38,0,256,0Zm0,74.27a82.21,82.21,0,1,1-82.21,82.21A82.21,82.21,0,0,1,256,74.27Zm0,408.05A225.77,225.77,0,0,1,85.79,405.16a186.92,186.92,0,0,1,340.42,0A225.77,225.77,0,0,1,256,482.32Z" />
                    </svg></span>
                <h3 data-i18n="profile.guest.title">Guest View</h3>
                <p data-i18n="profile.guest.text">You are currently viewing this website as a guest. Please log into an account to view profile information.</p>
                <div>
                    <button><a href="authorisation.php?action=1" data-i18n="auth.register.login">Log in</a></button>
                    <button><a href="authorisation.php?action=2" data-i18n="auth.register.button">Register</a></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

</section>

<?php include_once __DIR__ . '/includes/footer.php';
