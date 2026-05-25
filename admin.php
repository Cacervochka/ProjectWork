<?php
$pageTitle = 'Admin Panel';
$mainClass = 'admin-main';
session_start();
include_once __DIR__ . '/includes/db.php';

$currentUser = $_SESSION['user'] ?? null;
$adminEmailsRaw = $_ENV['ADMIN_EMAILS'] ?? ($_ENV['ADMIN_EMAIL'] ?? '');
$adminEmails = array_filter(array_map('trim', explode(',', $adminEmailsRaw)));
$isAdmin = $currentUser && (!$adminEmails || in_array($currentUser['email'], $adminEmails, true));

if (!$currentUser) {
    header('Location: authorisation.php?action=1');
    exit;
}

if (!$isAdmin) {
    http_response_code(403);
}

if (!isset($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
}

function redirectAdmin(string $message, string $type = 'success'): void
{
    $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
    header('Location: admin.php');
    exit;
}

function requireAdminToken(): void
{
    if (!hash_equals($_SESSION['admin_csrf'] ?? '', $_POST['csrf'] ?? '')) {
        redirectAdmin('Security check failed. Please try again.', 'error');
    }
}

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireAdminToken();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_movie') {
            $title = trim($_POST['title'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $duration = (int) ($_POST['duration'] ?? 0);
            $rating = trim($_POST['rating'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isCurrent = isset($_POST['is_current']) ? 1 : 0;

            if ($title === '' || $genre === '' || $duration <= 0 || $rating === '') {
                redirectAdmin('Fill in all required movie fields.', 'error');
            }

            $stmt = $pdo->prepare('
                INSERT INTO movies (title, genre, duration, rating, description, is_current)
                VALUES (:title, :genre, :duration, :rating, :description, :is_current)
            ');
            $stmt->execute([
                'title' => $title,
                'genre' => $genre,
                'duration' => $duration,
                'rating' => $rating,
                'description' => $description,
                'is_current' => $isCurrent,
            ]);

            redirectAdmin('Movie added.');
        }

        if ($action === 'delete_movie') {
            $movieId = (int) ($_POST['movie_id'] ?? 0);
            if ($movieId <= 0) {
                redirectAdmin('Choose a movie to delete.', 'error');
            }

            $pdo->beginTransaction();
            $scheduleIds = $pdo->prepare('SELECT id FROM schedules WHERE movie_id = :movie_id');
            $scheduleIds->execute(['movie_id' => $movieId]);
            $ids = array_column($scheduleIds->fetchAll(PDO::FETCH_ASSOC), 'id');

            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $pdo->prepare("DELETE FROM tickets WHERE schedule_id IN ($placeholders)")->execute($ids);
            }

            $pdo->prepare('DELETE FROM schedules WHERE movie_id = :movie_id')->execute(['movie_id' => $movieId]);
            $pdo->prepare('DELETE FROM reviews WHERE movie_id = :movie_id')->execute(['movie_id' => $movieId]);
            $pdo->prepare('DELETE FROM movie_ratings WHERE movie_id = :movie_id')->execute(['movie_id' => $movieId]);
            $pdo->prepare('DELETE FROM movies WHERE id = :movie_id')->execute(['movie_id' => $movieId]);
            $pdo->commit();

            redirectAdmin('Movie and related records deleted.');
        }

        if ($action === 'add_schedule') {
            $movieId = (int) ($_POST['movie_id'] ?? 0);
            $showTimeRaw = trim($_POST['show_time'] ?? '');
            $room = trim($_POST['room'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);
            $showTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $showTimeRaw);

            if ($movieId <= 0 || !$showTime || $room === '' || $price <= 0) {
                redirectAdmin('Fill in all required schedule fields.', 'error');
            }

            $stmt = $pdo->prepare('
                INSERT INTO schedules (movie_id, show_time, room, price)
                VALUES (:movie_id, :show_time, :room, :price)
            ');
            $stmt->execute([
                'movie_id' => $movieId,
                'show_time' => $showTime->format('Y-m-d H:i:s'),
                'room' => $room,
                'price' => $price,
            ]);

            redirectAdmin('Schedule entry added.');
        }

        if ($action === 'delete_schedule') {
            $scheduleId = (int) ($_POST['schedule_id'] ?? 0);
            if ($scheduleId <= 0) {
                redirectAdmin('Choose a schedule entry to delete.', 'error');
            }

            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM tickets WHERE schedule_id = :schedule_id')->execute(['schedule_id' => $scheduleId]);
            $pdo->prepare('DELETE FROM schedules WHERE id = :schedule_id')->execute(['schedule_id' => $scheduleId]);
            $pdo->commit();

            redirectAdmin('Schedule entry deleted.');
        }

        if ($action === 'add_menu_item') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = (float) ($_POST['price'] ?? 0);

            if ($categoryId <= 0 || $name === '' || $price <= 0) {
                redirectAdmin('Fill in all required menu item fields.', 'error');
            }

            $stmt = $pdo->prepare('
                INSERT INTO menu_items (category_id, name, description, price)
                VALUES (:category_id, :name, :description, :price)
            ');
            $stmt->execute([
                'category_id' => $categoryId,
                'name' => $name,
                'description' => $description,
                'price' => $price,
            ]);

            redirectAdmin('Menu item added.');
        }

        if ($action === 'delete_menu_item') {
            $itemId = (int) ($_POST['item_id'] ?? 0);
            if ($itemId <= 0) {
                redirectAdmin('Choose a menu item to delete.', 'error');
            }

            $pdo->prepare('DELETE FROM menu_items WHERE id = :item_id')->execute(['item_id' => $itemId]);
            redirectAdmin('Menu item deleted.');
        }

        redirectAdmin('Unknown admin action.', 'error');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        redirectAdmin('Admin action failed: ' . $e->getMessage(), 'error');
    }
}

$movies = $pdo->query('SELECT id, title, genre, duration, rating, is_current FROM movies ORDER BY title ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
$schedules = $pdo->query('
    SELECT s.id, s.show_time, s.room, s.price, m.title
    FROM schedules s
    JOIN movies m ON s.movie_id = m.id
    ORDER BY s.show_time DESC
    LIMIT 80
')->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query('SELECT id, name FROM menu_categories ORDER BY position ASC, name ASC')->fetchAll(PDO::FETCH_ASSOC);
$menuItems = $pdo->query('
    SELECT i.id, i.name, i.description, i.price, c.name AS category
    FROM menu_items i
    JOIN menu_categories c ON i.category_id = c.id
    ORDER BY c.position ASC, i.name ASC
')->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

include_once __DIR__ . '/includes/header.php';
?>

<?php if (!$isAdmin): ?>
    <section class="admin-shell">
        <div class="admin-panel admin-access">
            <h1 data-i18n="admin.denied.title">Access denied</h1>
            <p data-i18n="admin.denied.text">Your account is not allowed to use the admin panel.</p>
        </div>
    </section>
<?php else: ?>
    <section class="admin-shell">
        <div class="admin-heading">
            <div>
                <span class="eyebrow" data-i18n="admin.eyebrow">Admin</span>
                <h1 data-i18n="admin.title">Cinema Management</h1>
            </div>
            <p data-i18n="admin.subtitle">Add and remove movies, showtimes, and menu items.</p>
        </div>

        <?php if ($flash): ?>
            <div class="admin-flash <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="admin-grid">
            <section class="admin-panel">
                <h2 data-i18n="admin.movies.title">Movies</h2>
                <form class="admin-form" method="post">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                    <input type="hidden" name="action" value="add_movie">

                    <label>
                        <span data-i18n="admin.movie.title">Title</span>
                        <input type="text" name="title" required>
                    </label>
                    <label>
                        <span data-i18n="admin.movie.genre">Genre</span>
                        <input type="text" name="genre" required>
                    </label>
                    <div class="admin-form-row">
                        <label>
                            <span data-i18n="admin.movie.duration">Duration</span>
                            <input type="number" name="duration" min="1" required>
                        </label>
                        <label>
                            <span data-i18n="admin.movie.rating">Rating</span>
                            <input type="text" name="rating" required>
                        </label>
                    </div>
                    <label>
                        <span data-i18n="admin.movie.description">Description</span>
                        <textarea name="description" rows="4"></textarea>
                    </label>
                    <label class="admin-check">
                        <input type="checkbox" name="is_current" value="1" checked>
                        <span data-i18n="admin.movie.current">Currently showing</span>
                    </label>
                    <button type="submit" data-i18n="admin.addMovie">Add movie</button>
                </form>

                <div class="admin-list">
                    <?php foreach ($movies as $movie): ?>
                        <article class="admin-list-row">
                            <div>
                                <strong><?= htmlspecialchars($movie['title']) ?></strong>
                                <span><?= htmlspecialchars($movie['genre']) ?> / <?= (int) $movie['duration'] ?> min / <?= htmlspecialchars($movie['rating']) ?></span>
                            </div>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                                <input type="hidden" name="action" value="delete_movie">
                                <input type="hidden" name="movie_id" value="<?= (int) $movie['id'] ?>">
                                <button class="danger" type="submit" data-i18n="admin.delete">Delete</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-panel">
                <h2 data-i18n="admin.schedules.title">Schedule</h2>
                <form class="admin-form" method="post">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                    <input type="hidden" name="action" value="add_schedule">

                    <label>
                        <span data-i18n="admin.schedule.movie">Movie</span>
                        <select name="movie_id" required>
                            <?php foreach ($movies as $movie): ?>
                                <option value="<?= (int) $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span data-i18n="admin.schedule.time">Show time</span>
                        <input type="datetime-local" name="show_time" required>
                    </label>
                    <div class="admin-form-row">
                        <label>
                            <span data-i18n="admin.schedule.room">Room</span>
                            <input type="text" name="room" required>
                        </label>
                        <label>
                            <span data-i18n="admin.schedule.price">Price</span>
                            <input type="number" name="price" min="0.01" step="0.01" value="12.00" required>
                        </label>
                    </div>
                    <button type="submit" data-i18n="admin.addSchedule">Add showtime</button>
                </form>

                <div class="admin-list">
                    <?php foreach ($schedules as $schedule): ?>
                        <article class="admin-list-row">
                            <div>
                                <strong><?= htmlspecialchars($schedule['title']) ?></strong>
                                <span><?= htmlspecialchars(date('d/m/Y H:i', strtotime($schedule['show_time']))) ?> / <?= htmlspecialchars($schedule['room']) ?> / $<?= htmlspecialchars(number_format((float) $schedule['price'], 2)) ?></span>
                            </div>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                                <input type="hidden" name="action" value="delete_schedule">
                                <input type="hidden" name="schedule_id" value="<?= (int) $schedule['id'] ?>">
                                <button class="danger" type="submit" data-i18n="admin.delete">Delete</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="admin-panel admin-panel-wide">
                <h2 data-i18n="admin.menu.title">Menu Items</h2>
                <form class="admin-form admin-form-inline" method="post">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                    <input type="hidden" name="action" value="add_menu_item">

                    <label>
                        <span data-i18n="admin.menu.category">Category</span>
                        <select name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        <span data-i18n="admin.menu.name">Name</span>
                        <input type="text" name="name" required>
                    </label>
                    <label>
                        <span data-i18n="admin.menu.description">Description</span>
                        <input type="text" name="description">
                    </label>
                    <label>
                        <span data-i18n="admin.menu.price">Price</span>
                        <input type="number" name="price" min="0.01" step="0.01" required>
                    </label>
                    <button type="submit" data-i18n="admin.addMenuItem">Add item</button>
                </form>

                <div class="admin-list admin-list-compact">
                    <?php foreach ($menuItems as $item): ?>
                        <article class="admin-list-row">
                            <div>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <span><?= htmlspecialchars($item['category']) ?> / $<?= htmlspecialchars(number_format((float) $item['price'], 2)) ?></span>
                            </div>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['admin_csrf']) ?>">
                                <input type="hidden" name="action" value="delete_menu_item">
                                <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                <button class="danger" type="submit" data-i18n="admin.delete">Delete</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </section>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php';
