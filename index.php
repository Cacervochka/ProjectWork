<?php
$pageTitle = 'Home';
$bodyClass = 'home-page';
$mainClass = 'home-main';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/schedule_helpers.php';

$upcomingStmt = $pdo->prepare(
    'SELECT s.id AS schedule_id, m.id AS movie_id, m.title, m.genre, m.duration, m.rating, s.show_time, s.room, s.price
     FROM schedules s
     JOIN movies m ON s.movie_id = m.id
     WHERE s.show_time >= NOW()
     ORDER BY s.show_time ASC
     LIMIT 6'
);
$upcomingStmt->execute();
$upcomingShows = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$dateTabs = fetchScheduleDateTabs($pdo);
?>


<section class="home-hero" aria-label="Featured cinema event">
    <button class="hero-arrow hero-arrow-left" type="button" aria-label="Previous slide">&lsaquo;</button>
    <div class="home-hero-panel">
        <h1 data-i18n="home.hero.title">Culture needs space</h1>
        <p data-i18n="home.hero.text">Support the public collection that helps keep independent cultural cinema alive.</p>
        <a class="home-hero-button" href="programs.php" data-i18n="home.hero.button">SEE PROGRAM -></a>

    </div>
    <div class="home-hero-image"></div>
    <button class="hero-arrow hero-arrow-right" type="button" aria-label="Next slide">&rsaquo;</button>
</section>

<section class="home-program">
    <div class="home-date-tabs" aria-label="Program days">
        <?php foreach ($dateTabs as $index => $tab): ?>
            <a class="<?= $index === 0 ? 'active' : '' ?>" href="programs.php?date=<?= htmlspecialchars($tab['date']) ?>">
                <span data-i18n="<?= htmlspecialchars($tab['key']) ?>"><?= htmlspecialchars($tab['label']) ?></span> <?= htmlspecialchars($tab['display']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="home-show-list">
        <?php if ($upcomingShows): ?>
            <?php foreach ($upcomingShows as $show): ?>
                <article class="home-show-row">
                    <time class="show-date" datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($show['show_time']))) ?>">
                        <?= htmlspecialchars(date('d. m.', strtotime($show['show_time']))) ?>
                    </time>
                    <time class="show-time" datetime="<?= htmlspecialchars(date('H:i', strtotime($show['show_time']))) ?>">
                        <?= htmlspecialchars(date('H:i', strtotime($show['show_time']))) ?>
                    </time>
                    <h2><a href="movie.php?id=<?= (int) $show['movie_id'] ?>" data-i18n="movie.title.<?= (int) $show['movie_id'] ?>"><?= htmlspecialchars($show['title']) ?></a></h2>
                    <div class="show-meta">
                        <span class="room-badge"><?= htmlspecialchars($show['room']) ?></span>
                        <span>2D</span>
                        <span><?= htmlspecialchars(strtoupper(substr($show['genre'], 0, 2))) ?></span>
                        <span><?= htmlspecialchars($show['rating']) ?></span>
                    </div>
                    <a class="buy-button" href="buy_ticket.php?schedule_id=<?= (int) $show['schedule_id'] ?>" data-i18n="button.buy">BUY</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-program" data-i18n="empty.upcoming">No upcoming screenings available right now. Check back soon.</p>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
