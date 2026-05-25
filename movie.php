<?php
$movieId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$bodyClass = 'program-page';
$mainClass = 'program-main';
include_once __DIR__ . '/includes/db.php';

if (!$movieId) {
    http_response_code(404);
    $pageTitle = 'Movie not found';
    $movie = null;
} else {
    $movieStmt = $pdo->prepare(
        'SELECT id, title, genre, duration, rating, description, is_current
         FROM movies
         WHERE id = :id
         LIMIT 1'
    );
    $movieStmt->execute(['id' => $movieId]);
    $movie = $movieStmt->fetch(PDO::FETCH_ASSOC);
    $pageTitle = $movie ? $movie['title'] : 'Movie not found';
}

$shows = [];
if ($movie) {
    $showsStmt = $pdo->prepare(
        'SELECT id AS schedule_id, show_time, room, price
         FROM schedules
         WHERE movie_id = :movie_id AND show_time >= NOW()
         ORDER BY show_time ASC'
    );
    $showsStmt->execute(['movie_id' => $movie['id']]);
    $shows = $showsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include_once __DIR__ . '/includes/header.php'; ?>

<?php if (!$movie): ?>
    <section class="program-shell movie-detail-shell">
        <div class="movie-not-found">
            <span class="eyebrow">404</span>
            <h1 data-i18n="movie.notFound.title">Movie not found</h1>
            <p data-i18n="movie.notFound.text">The selected movie does not exist or was removed from the program.</p>
            <a class="buy-button" href="programs.php" data-i18n="movie.notFound.back">BACK TO PROGRAM</a>
        </div>
    </section>
<?php else: ?>
    <section class="movie-detail-hero">
        <div class="movie-detail-poster">
            <span><?= htmlspecialchars(substr($movie['title'], 0, 1)) ?></span>
        </div>
        <div class="movie-detail-content">
            <span class="eyebrow"><?= htmlspecialchars($movie['genre']) ?></span>
            <h1 data-i18n="movie.title.<?= (int) $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></h1>
            <p><?= htmlspecialchars($movie['description']) ?></p>
            <div class="movie-facts">
                <span><?= htmlspecialchars($movie['duration']) ?> <span data-i18n="movie.minutes">min</span></span>
                <span><?= htmlspecialchars($movie['rating']) ?></span>
                <span data-i18n="<?= $movie['is_current'] ? 'status.current' : 'status.upcoming' ?>"><?= $movie['is_current'] ? 'Current' : 'Upcoming' ?></span>
            </div>
        </div>
    </section>

    <section class="program-shell movie-detail-shell">
        <div class="section-heading-row">
            <div>
                <span class="eyebrow" data-i18n="movie.sessions.eyebrow">Sessions</span>
                <h2 data-i18n="movie.sessions.title">Available showtimes</h2>
            </div>
            <a class="outline-button" href="programs.php" data-i18n="movie.sessions.full">FULL PROGRAM</a>
        </div>

        <div class="home-show-list program-show-list">
            <?php if ($shows): ?>
                <?php foreach ($shows as $show): ?>
                    <article class="home-show-row movie-session-row">
                        <time class="show-date" datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($show['show_time']))) ?>">
                            <?= htmlspecialchars(date('d. m.', strtotime($show['show_time']))) ?>
                        </time>
                        <time class="show-time" datetime="<?= htmlspecialchars(date('H:i', strtotime($show['show_time']))) ?>">
                            <?= htmlspecialchars(date('H:i', strtotime($show['show_time']))) ?>
                        </time>
                        <h2 data-i18n="movie.title.<?= (int) $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></h2>
                        <div class="show-meta">
                            <span class="room-badge"><?= htmlspecialchars($show['room']) ?></span>
                            <span>2D</span>
                            <span>$<?= htmlspecialchars(number_format((float) $show['price'], 2)) ?></span>
                        </div>
                        <a class="buy-button" href="programs.php" data-i18n="button.buy">BUY</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-program" data-i18n="empty.movieShows">No upcoming sessions for this movie right now.</p>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php';
