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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        isset($_POST['refund_ticket']) &&
        isset($_POST['ticket_id']) &&
        (int)$_POST['ticket_id'] === $ticketId
    ) {

        $deleteStmt = $pdo->prepare("
            DELETE FROM tickets
            WHERE id = :ticket_id
              AND user_id = :user_id
            LIMIT 1
        ");

        $deleteStmt->execute([
            'ticket_id' => $ticketId,
            'user_id' => $currentUserId
        ]);

        header("Location: index.php");
        exit;
    }
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
                <div id="l_l_column">
                    <img src="./assets/extra/movie_poster_placeholder.jpg" alt="Movie Poster">
                    <button type="button" id="openRefundPopup" data-i18n="ticket.refund.title">Refund ticket</button>
                </div>


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

<div class="refundPopup hidden" id="refundPopup">

    <div class="refundPopupCard">

        <h2 data-i18n="ticket.refund.title">Refund ticket</h2>

        <div class="refundPopupBody">
            <p data-i18n="ticket.refund.q">Are you sure you want to refund this ticket?</p>
            
            <h3><?= htmlspecialchars($ticket['title']) ?></h3>
            <hr>
            <div class="refundInfo">
                <div>
                    <span data-i18n="ticket.date">Date</span>
                    <p><?= $showDate ?></p>
                </div>

                <div>
                    <span data-i18n="ticket.time">Time</span>
                    <p><?= $showTime ?></p>
                </div>

                <div>
                    <span data-i18n="ticket.seat">Seat</span>
                    <p><?= htmlspecialchars($ticket['seat_number']) ?></p>
                </div>

            </div>
        </div>

        <div class="refundPopupActions">
            <button type="button" class="cancelRefundBtn" id="closeRefundPopup">Cancel </button>

            <form method="post">
                <input type="hidden" name="ticket_id" value="<?= (int)$ticket['ticket_id'] ?>">
                <input type="hidden" name="refund_ticket" value="1">
                <button type="submit" class="confirmRefundBtn">Confirm</button>
            </form>

        </div>

    </div>

</div>

<script>
    const refundPopup = document.getElementById("refundPopup");

    const openRefundPopupBtn = document.getElementById("openRefundPopup");

    const closeRefundPopupBtn = document.getElementById("closeRefundPopup");

    openRefundPopupBtn.addEventListener("click", () => {
        refundPopup.classList.remove("hidden");
    });

    closeRefundPopupBtn.addEventListener("click", () => {
        refundPopup.classList.add("hidden");
    });

    refundPopup.addEventListener("click", (e) => {

        if (e.target === refundPopup) {
            refundPopup.classList.add("hidden");
        }

    });
</script>

<?php



include_once __DIR__ . '/includes/footer.php'; ?>