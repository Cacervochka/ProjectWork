<?php
$pageTitle = 'Home';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$upcomingStmt = $pdo->prepare(
    'SELECT s.id AS schedule_id, m.title, m.genre, m.duration, s.show_time, s.room
     FROM schedules s
     JOIN movies m ON s.movie_id = m.id
     WHERE s.show_time >= NOW()
     ORDER BY s.show_time ASC
     LIMIT 6'
);
$upcomingStmt->execute();
$upcomingShows = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="hero-content">
        <h1>Welcome to CineView</h1>
        <p>Experience the latest blockbusters, café favorites, and a seamless movie night.</p>
        <a class="button" href="programs.php">See Programs</a>
    </div>
</section>

<section class="section">
    <h2>Upcoming Screenings</h2>
    <?php if ($upcomingShows): ?>
        <div class="card-grid">
            <?php foreach ($upcomingShows as $show): ?>
                <article class="card">
                    <h3><?= htmlspecialchars($show['title']) ?></h3>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($show['genre']) ?></p>
                    <p><strong>Duration:</strong> <?= htmlspecialchars($show['duration']) ?> min</p>
                    <p><strong>Time:</strong> <?= date('M j, H:i', strtotime($show['show_time'])) ?></p>
                    <p><strong>Room:</strong> <?= htmlspecialchars($show['room']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No upcoming screenings available right now. Check back soon.</p>
    <?php endif; ?>
</section>

<section class="section feature-grid">
    <div class="feature-card">
        <h3>Current Movies</h3>
        <p>Browse all movies currently playing at your cinema.</p>
        <a href="current.php">Explore Now</a>
    </div>
    <div class="feature-card">
        <h3>Café & Menu</h3>
        <p>Discover our bar and cafeteria menu, including snacks and drinks.</p>
        <a href="menu.php">View Menu</a>
    </div>
    <div class="feature-card">
        <h3>Membership Profile</h3>
        <p>Log in to manage your profile, reservations, and preferences.</p>
        <a href="profile.php">Go to Profile</a>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
