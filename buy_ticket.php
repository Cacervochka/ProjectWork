<?php
session_start();
$pageTitle = 'Buy Ticket';
$bodyClass = 'program-page';
$mainClass = 'program-main';

include_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user'])) {
    $scheduleId = filter_input(INPUT_GET, 'schedule_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
    $redirect = $scheduleId ? 'buy_ticket.php?schedule_id=' . urlencode((string) $scheduleId) : 'programs.php';
    header('Location: authorisation.php?action=1&redirect=' . urlencode($redirect));
    exit;
}

if (!isset($_SESSION['ticket_csrf'])) {
    $_SESSION['ticket_csrf'] = bin2hex(random_bytes(16));
}

$scheduleId = filter_input(INPUT_GET, 'schedule_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'schedule_id', FILTER_VALIDATE_INT);
$error = '';

if (!$scheduleId) {
    header('Location: programs.php');
    exit;
}

$scheduleStmt = $pdo->prepare('
    SELECT
        s.id AS schedule_id,
        s.show_time,
        s.room,
        s.price,
        m.id AS movie_id,
        m.title,
        m.genre,
        m.duration,
        m.rating
    FROM schedules s
    JOIN movies m ON s.movie_id = m.id
    WHERE s.id = :schedule_id
    LIMIT 1
');
$scheduleStmt->execute(['schedule_id' => $scheduleId]);
$show = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

if (!$show || strtotime($show['show_time']) < time()) {
    header('Location: programs.php');
    exit;
}

$takenStmt = $pdo->prepare('
    SELECT seat_number
    FROM tickets
    WHERE schedule_id = :schedule_id
      AND status IN ("reserved", "paid")
');
$takenStmt->execute(['schedule_id' => $scheduleId]);
$takenSeats = array_column($takenStmt->fetchAll(PDO::FETCH_ASSOC), 'seat_number');

$seatRows = ['A', 'B', 'C', 'D', 'E'];
$seatNumbers = range(1, 8);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['ticket_csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $error = 'Security check failed. Please try again.';
    } else {
        $seat = strtoupper(trim($_POST['seat_number'] ?? ''));
        $validSeats = [];
        foreach ($seatRows as $row) {
            foreach ($seatNumbers as $number) {
                $validSeats[] = $row . $number;
            }
        }

        if (!in_array($seat, $validSeats, true)) {
            $error = 'Choose a valid seat.';
        } elseif (in_array($seat, $takenSeats, true)) {
            $error = 'This seat is already taken.';
        } else {
            try {
                $insert = $pdo->prepare('
                    INSERT INTO tickets (user_id, schedule_id, seat_number, price, status)
                    VALUES (:user_id, :schedule_id, :seat_number, :price, "paid")
                ');
                $insert->execute([
                    'user_id' => $_SESSION['user']['id'],
                    'schedule_id' => $scheduleId,
                    'seat_number' => $seat,
                    'price' => $show['price'],
                ]);

                header('Location: ticket.php?id=' . $pdo->lastInsertId());
                exit;
            } catch (PDOException $e) {
                $error = $e->getCode() === '23000'
                    ? 'This seat was just taken. Choose another one.'
                    : 'Ticket purchase failed. Please try again.';
            }
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<section class="ticket-buy-shell">
    <div class="ticket-buy-heading">
        <span class="eyebrow" data-i18n="ticket.buy.eyebrow">Ticket</span>
        <h1 data-i18n="ticket.buy.title">Choose your seat</h1>
    </div>

    <?php if ($error): ?>
        <p class="ticket-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="ticket-buy-grid">
        <div class="ticket-summary-panel">
            <h2 data-i18n="movie.title.<?= (int) $show['movie_id'] ?>"><?= htmlspecialchars($show['title']) ?></h2>
            <p><?= htmlspecialchars($show['genre']) ?> / <?= (int) $show['duration'] ?> <span data-i18n="movie.minutes">min</span> / <?= htmlspecialchars($show['rating']) ?></p>
            <dl>
                <div>
                    <dt data-i18n="ticket.date">Date</dt>
                    <dd><?= htmlspecialchars(date('d/m/Y', strtotime($show['show_time']))) ?></dd>
                </div>
                <div>
                    <dt data-i18n="ticket.time">Time</dt>
                    <dd><?= htmlspecialchars(date('H:i', strtotime($show['show_time']))) ?></dd>
                </div>
                <div>
                    <dt data-i18n="ticket.room">Room</dt>
                    <dd><?= htmlspecialchars($show['room']) ?></dd>
                </div>
                <div>
                    <dt data-i18n="ticket.price">Price</dt>
                    <dd>$<?= htmlspecialchars(number_format((float) $show['price'], 2)) ?></dd>
                </div>
            </dl>
        </div>

        <form class="seat-panel" method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['ticket_csrf']) ?>">
            <input type="hidden" name="schedule_id" value="<?= (int) $show['schedule_id'] ?>">

            <div class="screen" data-i18n="ticket.screen">Screen</div>

            <div class="seat-grid" aria-label="Seat selection">
                <?php foreach ($seatRows as $row): ?>
                    <?php foreach ($seatNumbers as $number): ?>
                        <?php
                        $seat = $row . $number;
                        $taken = in_array($seat, $takenSeats, true);
                        ?>
                        <label class="seat-option <?= $taken ? 'taken' : '' ?>">
                            <input type="radio" name="seat_number" value="<?= htmlspecialchars($seat) ?>" <?= $taken ? 'disabled' : '' ?> required>
                            <span><?= htmlspecialchars($seat) ?></span>
                        </label>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <div class="seat-legend">
                <span><i class="free"></i><span data-i18n="ticket.seat.free">Available</span></span>
                <span><i class="taken"></i><span data-i18n="ticket.seat.taken">Taken</span></span>
                <span><i class="selected"></i><span data-i18n="ticket.seat.selected">Selected</span></span>
            </div>

            <button class="buy-button" type="submit" data-i18n="ticket.buy.confirm">Buy ticket</button>
        </form>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
