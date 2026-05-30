<?php
$pageTitle = 'Search';
$bodyClass = 'program-page search-page';
$mainClass = 'program-main';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/search_helpers.php';

$search = trim($_GET['q'] ?? '');
$searchVariants = buildSearchVariants($search);
$movies = [];

$whereParts = [];
$params = [];

foreach ($searchVariants as $index => $variant) {
    $whereParts[] = "(m.title LIKE :search_title_$index
            OR m.genre LIKE :search_genre_$index
            OR m.description LIKE :search_description_$index
            OR m.rating LIKE :search_rating_$index)";
    $params["search_title_$index"] = '%' . $variant . '%';
    $params["search_genre_$index"] = '%' . $variant . '%';
    $params["search_description_$index"] = '%' . $variant . '%';
    $params["search_rating_$index"] = '%' . $variant . '%';
}

if ($searchVariants) {
    $params['starts_with'] = $searchVariants[0] . '%';

    $searchStmt = $pdo->prepare(
        'SELECT m.id, m.title, m.genre, m.duration, m.rating, m.description,
                MIN(s.show_time) AS next_show
         FROM movies m
         LEFT JOIN schedules s ON s.movie_id = m.id AND s.show_time >= NOW()
         WHERE ' . implode(' OR ', $whereParts) . '
         GROUP BY m.id, m.title, m.genre, m.duration, m.rating, m.description
         ORDER BY
            CASE WHEN m.title LIKE :starts_with THEN 0 ELSE 1 END,
            m.title ASC'
    );
    $searchStmt->execute($params);
    $movies = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<section class="page-hero movies-hero">
    <div class="page-hero-panel">
        <span class="eyebrow">Search</span>
        <h1>Movie search</h1>
        <p>Find movies by title, genre, rating, or description.</p>
    </div>
    <div class="page-hero-image"></div>
</section>

<section class="program-shell movie-grid-shell">
    <form class="program-filter" action="search.php" method="get">
        <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search movies..." aria-label="Search movies" list="movieSearchSuggestions" autocomplete="off">
        <button type="submit" data-i18n="button.search">SEARCH</button>
    </form>

    <?php if ($search === ''): ?>
        <p class="empty-program">Enter a movie title, genre, rating, or keyword to search.</p>
    <?php elseif ($movies): ?>
        <div class="section-heading-row search-heading-row">
            <h2>Results for "<?= htmlspecialchars($search) ?>"</h2>
            <a class="outline-button" href="programs.php?q=<?= urlencode($search) ?>">Show schedule</a>
        </div>

        <div class="movie-card-grid">
            <?php foreach ($movies as $movie): ?>
                <article class="movie-card">
                    <a class="movie-poster" href="movie.php?id=<?= (int) $movie['id'] ?>" aria-label="<?= htmlspecialchars($movie['title']) ?>">
                        <span><?= htmlspecialchars(substr($movie['title'], 0, 1)) ?></span>
                    </a>
                    <div class="movie-card-content">
                        <div class="movie-card-meta">
                            <span><?= htmlspecialchars($movie['genre']) ?></span>
                            <span><?= htmlspecialchars($movie['rating']) ?></span>
                            <span><?= htmlspecialchars($movie['duration']) ?> <span data-i18n="movie.minutes">min</span></span>
                        </div>
                        <h2><a href="movie.php?id=<?= (int) $movie['id'] ?>"><?= htmlspecialchars($movie['title']) ?></a></h2>
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
        <p class="empty-program">No movies found for "<?= htmlspecialchars($search) ?>".</p>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
