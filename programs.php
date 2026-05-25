<?php
$pageTitle = 'Programs';
$bodyClass = 'program-page';
$mainClass = 'program-main';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/schedule_helpers.php';

$search = trim($_GET['q'] ?? '');
$selectedDate = trim($_GET['date'] ?? '');
$dateFilter = DateTimeImmutable::createFromFormat('Y-m-d', $selectedDate) ? $selectedDate : '';

$where = ['s.show_time >= NOW()'];
$params = [];

if ($search !== '') {
    $where[] = '(m.title LIKE :search OR m.genre LIKE :search OR m.description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($dateFilter !== '') {
    $where[] = 'DATE(s.show_time) = :date_filter';
    $params['date_filter'] = $dateFilter;
}

$programsStmt = $pdo->prepare(
    'SELECT s.id AS schedule_id, m.id AS movie_id, m.title, m.genre, m.duration, m.description,
            s.show_time, s.room, s.price, m.rating
     FROM schedules s
     JOIN movies m ON s.movie_id = m.id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY s.show_time ASC'
);
$programsStmt->execute($params);
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);

$dateTabs = fetchScheduleDateTabs($pdo);
?>

<section class="page-hero schedule-hero">
    <div class="page-hero-panel">
        <span class="eyebrow" data-i18n="program.eyebrow">Program</span>
        <h1 data-i18n="program.title">Show schedule</h1>
        <p data-i18n="program.text">Choose a day, movie, and showtime. All screenings are imported from the database.</p>
    </div>
    <div class="page-hero-image"></div>
</section>

<section class="program-shell">
    <form class="program-filter" action="programs.php" method="get">
        <input type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search movie or genre" data-i18n-placeholder="program.search">
        <?php if ($dateFilter !== ''): ?>
            <input type="hidden" name="date" value="<?= htmlspecialchars($dateFilter) ?>">
        <?php endif; ?>
        <button type="submit" data-i18n="button.search">SEARCH</button>
    </form>

    <div class="home-date-tabs program-date-tabs" aria-label="Program days">
        <a class="<?= $dateFilter === '' ? 'active' : '' ?>" href="programs.php<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>" data-i18n="date.all">ALL</a>
        <?php foreach ($dateTabs as $tab): ?>
            <?php
            $query = ['date' => $tab['date']];
            if ($search !== '') {
                $query['q'] = $search;
            }
            ?>
            <a class="<?= $dateFilter === $tab['date'] ? 'active' : '' ?>" href="programs.php?<?= htmlspecialchars(http_build_query($query)) ?>">
                <span data-i18n="<?= htmlspecialchars($tab['key']) ?>"><?= htmlspecialchars($tab['label']) ?></span> <?= htmlspecialchars($tab['display']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="home-show-list program-show-list">
        <?php if ($programs): ?>
            <?php foreach ($programs as $program): ?>
                <article class="home-show-row program-show-row">
                    <time class="show-date" datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($program['show_time']))) ?>">
                        <?= htmlspecialchars(date('d. m.', strtotime($program['show_time']))) ?>
                    </time>
                    <time class="show-time" datetime="<?= htmlspecialchars(date('H:i', strtotime($program['show_time']))) ?>">
                        <?= htmlspecialchars(date('H:i', strtotime($program['show_time']))) ?>
                    </time>
                    <div class="program-title-cell">
                        <a href="movie.php?id=<?= (int) $program['movie_id'] ?>" data-i18n="movie.title.<?= (int) $program['movie_id'] ?>"><?= htmlspecialchars($program['title']) ?></a>
                        <p><?= htmlspecialchars($program['genre']) ?> / <?= htmlspecialchars($program['duration']) ?> <span data-i18n="movie.minutes">min</span></p>
                    </div>
                    <div class="show-meta">
                        <span class="room-badge"><?= htmlspecialchars($program['room']) ?></span>
                        <span>2D</span>
                        <span><?= htmlspecialchars($program['rating']) ?></span>
                    </div>
                    <a class="buy-button" href="buy_ticket.php?schedule_id=<?= (int) $program['schedule_id'] ?>" data-i18n="button.buy">BUY</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-program" data-i18n="empty.selection">No screenings match your selection.</p>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
