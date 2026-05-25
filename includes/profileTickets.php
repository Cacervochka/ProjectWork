<?php

date_default_timezone_set('Europe/Kyiv');

// ======================
// FETCH TICKETS
// ======================
$sql = "
SELECT 
    t.id AS ticket_id,
    t.seat_number,
    t.price AS ticket_price,
    t.purchased_at,
    
    s.show_time,
    s.room,

    m.id AS movie_id,
    m.title

FROM tickets t
JOIN schedules s ON t.schedule_id = s.id
JOIN movies m ON s.movie_id = m.id

WHERE t.user_id = :user_id
ORDER BY s.show_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user["id"]]);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SPLIT UPCOMING / PAST
$now = new DateTime();

$upcoming = [];
$past = [];

foreach ($tickets as $ticket) {
    $showTime = new DateTime($ticket["show_time"]);

    if ($showTime > $now) {
        $upcoming[] = $ticket;
    } else {
        $past[] = $ticket;
    }
}
?>

<div class="profile-nav">
    <button class="active"><a href="profile.php?viewSection=1" data-i18n="profile.nav.tickets">Tickets</a></button>
    <button><a href="profile.php?viewSection=2" data-i18n="profile.nav.reviews">Reviews</a></button>
    <button><a href="profile.php?viewSection=3" data-i18n="profile.nav.settings">Settings</a></button>
</div>

<div class="profileSubSection">

    <!-- UPCOMING -->
    <h3 data-i18n="profile.tickets.upcoming">Upcoming Events:</h3>

    <?php if (empty($upcoming)): ?>
        <div>
            <p data-i18n="profile.tickets.empty">No reserved tickets found.</p>
            <a href="programs.php" data-i18n="profile.browse">Browse upcoming events</a>
        </div>
    <?php else: ?>

        <?php foreach ($upcoming as $ticket): ?>
            <div class="eventElement">
                <div>
                    <div>
                        <h3 data-i18n="movie.title.<?= (int) $ticket["movie_id"] ?>"><?= htmlspecialchars($ticket["title"]) ?></h3>
                        <p>
                            <?= date("d/m/Y - H:i", strtotime($ticket["show_time"])) ?>
                            - <?= htmlspecialchars($ticket["room"]) ?>
                            - <span data-i18n="profile.seat">Seat</span> <?= htmlspecialchars($ticket["seat_number"]) ?>
                        </p>
                    </div>
                    <div>
                        <button>
                            <a href="movie.php?id=<?= $ticket["movie_id"] ?>" data-i18n="profile.filmDetails">Film details</a>
                        </button>
                        <button>
                            <a href="ticket.php?id=<?= $ticket["ticket_id"] ?>" data-i18n="profile.ticket">Ticket</a>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>


    <!-- ====================== -->
    <!-- PAST -->
    <!-- ====================== -->
    <?php if (!empty($past)): ?>

        <hr>
        <h3 data-i18n="profile.tickets.past">Past Events:</h3>

        <?php foreach ($past as $ticket): ?>
            <div class="eventElement">
                <div>
                    <div>
                        <h3 data-i18n="movie.title.<?= (int) $ticket["movie_id"] ?>"><?= htmlspecialchars($ticket["title"]) ?></h3>
                        <p>
                            <?= date("d/m/Y - H:i", strtotime($ticket["show_time"])) ?>
                            - <?= htmlspecialchars($ticket["room"]) ?>
                            - <span data-i18n="profile.seat">Seat</span> <?= htmlspecialchars($ticket["seat_number"]) ?>
                        </p>
                    </div>
                    <div>
                        <button>
                            <a href="movie.php?id=<?= $ticket["movie_id"] ?>" data-i18n="profile.filmDetails">Film details</a>
                        </button>
                        <button>
                            <a href="ticket.php?id=<?= $ticket["ticket_id"] ?>" data-i18n="profile.ticket">Ticket</a>
                        </button>
                    </div>
                </div>

                <button>
                    <a href="profile.php?viewSection=2">
                        <span data-i18n="profile.review.add">Add review</span>
                    </a>
                </button>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>
