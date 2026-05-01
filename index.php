<?php
$pageTitle = 'Home';
$bodyClass = 'home-page';
$mainClass = 'home-main';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$upcomingStmt = $pdo->prepare(
    'SELECT s.id AS schedule_id, m.title, m.genre, m.duration, m.rating, s.show_time, s.room, s.price
     FROM schedules s
     JOIN movies m ON s.movie_id = m.id
     WHERE s.show_time >= NOW()
     ORDER BY s.show_time ASC
     LIMIT 6'
);
$upcomingStmt->execute();
$upcomingShows = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

$dateTabs = [];
$labels = ['DNES', 'ZAJTRA', 'NEDELA', 'PONDELOK', 'UTOROK', 'STREDA', 'STVRTOK'];
for ($i = 0; $i < 7; $i++) {
    $date = new DateTimeImmutable("+$i day");
    $dateTabs[] = [
        'label' => $labels[$i],
        'date' => $date->format('d. m.'),
    ];
}
?>

<section class="home-hero" aria-label="Featured cinema event">
    <button class="hero-arrow hero-arrow-left" type="button" aria-label="Previous slide">&lsaquo;</button>
    <div class="home-hero-panel">
        <h1>Kultura potrebuje priestor</h1>
        <p>Podporte verejnu zbierku, ktora pomoze zachranit fungovanie nezavislych kulturnych centier.</p>
        <a class="home-hero-button" href="programs.php">CHCEM PODPORIT -></a>
    </div>
    <div class="home-hero-image"></div>
    <button class="hero-arrow hero-arrow-right" type="button" aria-label="Next slide">&rsaquo;</button>
</section>

<section class="home-program">
    <div class="home-date-tabs" aria-label="Program days">
        <?php foreach ($dateTabs as $index => $tab): ?>
            <a class="<?= $index === 0 ? 'active' : '' ?>" href="programs.php">
                <?= htmlspecialchars($tab['label']) ?> <?= htmlspecialchars($tab['date']) ?>
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
                    <h2><?= htmlspecialchars($show['title']) ?></h2>
                    <div class="show-meta">
                        <span class="room-badge"><?= htmlspecialchars($show['room']) ?></span>
                        <span>2D</span>
                        <span><?= htmlspecialchars(strtoupper(substr($show['genre'], 0, 2))) ?></span>
                        <span><?= htmlspecialchars($show['rating']) ?></span>
                    </div>
                    <a class="buy-button" href="programs.php">KUPIT</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-program">No upcoming screenings available right now. Check back soon.</p>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
