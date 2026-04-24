<?php
$pageTitle = 'Programs';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$programsStmt = $pdo->prepare(
    'SELECT s.id AS schedule_id, m.title, m.genre, m.duration, s.show_time, s.room, m.rating
     FROM schedules s
     JOIN movies m ON s.movie_id = m.id
     WHERE s.show_time >= NOW()
     ORDER BY s.show_time ASC'
);
$programsStmt->execute();
$programs = $programsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="section">
    <h1>Programs</h1>
    <p>Discover our full schedule of upcoming movie screenings.</p>

    <?php if ($programs): ?>
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Genre</th>
                    <th>Duration</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programs as $program): ?>
                    <tr>
                        <td><?= htmlspecialchars($program['title']) ?></td>
                        <td><?= htmlspecialchars($program['genre']) ?></td>
                        <td><?= htmlspecialchars($program['duration']) ?> min</td>
                        <td><?= date('D, M j, H:i', strtotime($program['show_time'])) ?></td>
                        <td><?= htmlspecialchars($program['room']) ?></td>
                        <td><?= htmlspecialchars($program['rating']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No programs are available at the moment.</p>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
