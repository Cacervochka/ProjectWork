<?php
$pageTitle = 'Current Movies';
$bodyClass = 'program-page current-page';
$mainClass = 'program-main';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$currentStmt = $pdo->prepare(
    'SELECT m.id, m.title, m.genre, m.duration, m.rating, m.description,
            MIN(s.show_time) AS next_show
     FROM movies m
     LEFT JOIN schedules s ON s.movie_id = m.id AND s.show_time >= NOW()
     WHERE m.is_current = 1
     GROUP BY m.id, m.title, m.genre, m.duration, m.rating, m.description
     ORDER BY m.title ASC'
);
$currentStmt->execute();
$currentMovies = $currentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="page-hero movies-hero">
    <div class="page-hero-panel">
        <span class="eyebrow" data-i18n="current.eyebrow">Now showing</span>
        <h1 data-i18n="current.title">Movies in cinema now</h1>
        <p data-i18n="current.text">Browse current movies with quick access to details and available showtimes.</p>
    </div>
    <div class="page-hero-image"></div>
</section>


<section class="program-shell movie-grid-shell">
    <?php if ($currentMovies): ?>
        <div class="movie-card-grid">
            <?php foreach ($currentMovies as $movie): ?>
                <article class="movie-card">
                    <a class="movie-poster" href="movie.php?id=<?= (int) $movie['id'] ?>" aria-label="<?= htmlspecialchars($movie['title']) ?>" data-i18n-aria="movie.title.<?= (int) $movie['id'] ?>">
                        <span><?= htmlspecialchars(substr($movie['title'], 0, 1)) ?></span>
                    </a>
                    <div class="movie-card-content">
                        <div class="movie-card-meta">
                            <span><?= htmlspecialchars($movie['genre']) ?></span>
                            <span><?= htmlspecialchars($movie['rating']) ?></span>
                            <span><?= htmlspecialchars($movie['duration']) ?> <span data-i18n="movie.minutes">min</span></span>
                        </div>
                        <h2><a href="movie.php?id=<?= (int) $movie['id'] ?>" data-i18n="movie.title.<?= (int) $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></a></h2>
                        <p><?= htmlspecialchars($movie['description']) ?></p>
                        <div class="movie-card-actions">
                            <?php if ($movie['next_show']): ?>
                                <time datetime="<?= htmlspecialchars(date('c', strtotime($movie['next_show']))) ?>">
                                    <span data-i18n="label.next">Next</span>: <?= htmlspecialchars(date('d. m. H:i', strtotime($movie['next_show']))) ?>
                                </time>
                            <?php else: ?>
                                <span data-i18n="empty.movieShowsShort">No upcoming show</span>
                            <?php endif; ?>
                            <a class="buy-button" href="movie.php?id=<?= (int) $movie['id'] ?>" data-i18n="button.detail">DETAIL</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="empty-program" data-i18n="empty.current">No movies are currently marked as playing. Check back again soon.</p>
    <?php endif; ?>
</section>


<?php include_once __DIR__ . '/includes/footer.php';
