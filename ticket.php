<?php
session_start();

$pageTitle = 'Ticket';

include_once __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: authorisation.php?action=1");
    exit;
}

// Check if ticket id exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$ticketId = (int)$_GET['id'];
$currentUserId = $_SESSION['user']['id'];

// Get ticket + schedule + movie info
$query = "
    SELECT 
        tickets.id AS ticket_id,
        tickets.user_id,
        tickets.seat_number,
        tickets.price AS ticket_price,
        tickets.purchased_at,

        schedules.show_time,
        schedules.room,
        schedules.price AS schedule_price,

        movies.title,
        movies.id AS movie_id,
        movies.genre,
        movies.duration,
        movies.description,
        movies.rating

    FROM tickets

    INNER JOIN schedules 
        ON tickets.schedule_id = schedules.id

    INNER JOIN movies 
        ON schedules.movie_id = movies.id

    WHERE tickets.id = :ticket_id
    LIMIT 1
";

$stmt = $pdo->prepare($query);

$stmt->execute([
    'ticket_id' => $ticketId
]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header("Location: index.php");
    exit;
}

// Check ownership
if ($ticket['user_id'] != $currentUserId) {
    header("Location: index.php");
    exit;
}

// Format date/time
$showDate = date("d/m/Y", strtotime($ticket['show_time']));
$showTime = date("H:i", strtotime($ticket['show_time']));

include_once __DIR__ . '/includes/header.php';
?>

<section class="section ticketSection">
    <div class="ticketSectionContent">
        <h2><span data-i18n="ticket.title">Ticket</span> #<?= htmlspecialchars($ticket['ticket_id']) ?></h2>

        <div class="columnContainer">

            <div class="l_column">
                <img src="./assets/extra/movie_poster_placeholder.jpg" alt="Movie Poster">

                <div>
                    <h3 data-i18n="movie.title.<?= (int) $ticket['movie_id'] ?>"><?= htmlspecialchars($ticket['title']) ?></h3>

                    <p><span data-i18n="ticket.rating">Rating:</span> <?= htmlspecialchars($ticket['rating']) ?>/10</p>

                    <p>
                        <?= htmlspecialchars($ticket['genre']) ?>
                        *
                        <?= htmlspecialchars($ticket['duration']) ?> <span data-i18n="movie.minutes">min</span>
                    </p>

                    <hr>

                    <h3 data-i18n="ticket.description">Description</h3>

                    <p>
                        <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                    </p>
                </div>
            </div>

            <div class="r_column">

                <div class="infoCard">
                    <h3 data-i18n="ticket.datetime">Date & Time</h3>

                    <p data-i18n="ticket.date">Date</p>
                    <p><?= $showDate ?></p>

                    <p data-i18n="ticket.time">Time</p>
                    <p><?= $showTime ?></p>
                </div>

                <div class="infoCard">
                    <h3 data-i18n="ticket.seating">Seating Info</h3>

                    <p data-i18n="ticket.room">Room</p>
                    <p><?= htmlspecialchars($ticket['room']) ?></p>

                    <p data-i18n="profile.seat">Seat</p>
                    <p><?= htmlspecialchars($ticket['seat_number']) ?></p>

                </div>

            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
