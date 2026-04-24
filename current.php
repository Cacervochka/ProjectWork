<?php
$pageTitle = 'Current Movies';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$currentStmt = $pdo->prepare(
    'SELECT m.id, m.title, m.genre, m.duration, m.rating, m.description
     FROM movies m
     WHERE m.is_current = 1
     ORDER BY m.title ASC'
);
$currentStmt->execute();
$currentMovies = $currentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">
    <h1>Currently Showing</h1>
    <p>See what’s playing now at CineView.</p>

    <?php if ($currentMovies): ?>
        <div class="card-grid">
            <?php foreach ($currentMovies as $movie): ?>
                <article class="card">
                    <h3><?= htmlspecialchars($movie['title']) ?></h3>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></p>
                    <p><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?> min</p>
                    <p><strong>Rating:</strong> <?= htmlspecialchars($movie['rating']) ?></p>
                    <p><?= htmlspecialchars($movie['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No movies are currently marked as playing. Check back again soon.</p>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
