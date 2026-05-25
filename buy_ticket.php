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

$seatRows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
$seatNumbers = range(1, 22);

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
        <h3 data-i18n="movie.title.<?= (int) $show['movie_id'] ?>"><?= htmlspecialchars($show['title']) ?></h3>
        <h1 data-i18n="ticket.buy.title">Choose your seat</h1>
    </div>

    <?php if ($error): ?>
        <p class="ticket-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="ticket-buy">
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

            <!-- Buy button -->
            <button class="buy-button" type="button" id="openConfirmBtn" data-i18n="ticket.buy.confirm">
                Buy ticket
            </button>

            <!-- Confirm Purchase Popup -->
            <div class="purchase-popup hidden" id="purchasePopup">
                <div class="purchase-popup-card">

                    <div class="popup-header">
                        <h2 data-i18n="ticket.confirm.title">Confirm ticket purchase</h2>
                    </div>

                    <div class="popup-movie-info">
                        <h2 data-i18n="movie.title.<?= (int) $show['movie_id'] ?>"><?= htmlspecialchars($show['title']) ?></h2>
                        <p><?= htmlspecialchars($show['genre']) ?> - <?= (int) $show['duration'] ?><span data-i18n="movie.minutes">m</span></p>
                    </div>
                    <hr>

                    <div class="popup-details-grid">
                        <div>
                            <h3 data-i18n="ticket.datetime">Date & Time</h3>
                            <p><?= htmlspecialchars(date('d/m/Y', strtotime($show['show_time']))) ?></p>
                            <p><?= htmlspecialchars(date('H-i', strtotime($show['show_time']))) ?></p>
                        </div>
                        <div>
                            <h3 data-i18n="ticket.room">Room</h3>
                            <p><?= htmlspecialchars($show['room']) ?></p>
                        </div>
                        <div>
                            <h3 data-i18n="ticket.seat">Seat</h3>
                            <p id="selectedSeatText">-</p>
                        </div>
                    </div>
                    <hr>

                    <div class="popup-price">
                        <h3 data-i18n="ticket.final_price">Final Price</h3>
                        <p>$<?= htmlspecialchars(number_format((float) $show['price'], 2)) ?></p>
                    </div>

                    <div class="popup-actions">
                        <button type="button" class="discard-btn" id="closePopupBtn">
                            Discard
                        </button>

                        <button type="submit" class="confirm-btn">
                            Confirm
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</section>

<script>
    const popup = document.getElementById("purchasePopup");
    const openBtn = document.getElementById("openConfirmBtn");
    const closeBtn = document.getElementById("closePopupBtn");

    const seatText = document.getElementById("selectedSeatText");

    openBtn.addEventListener("click", () => {

        const selectedSeat = document.querySelector('input[name="seat_number"]:checked');

        if (!selectedSeat) {
            alert("Please select a seat first.");
            return;
        }

        seatText.textContent = selectedSeat.value;

        popup.classList.remove("hidden");
    });

    closeBtn.addEventListener("click", () => {
        popup.classList.add("hidden");
    });

    popup.addEventListener("click", (e) => {
        if (e.target === popup) {
            popup.classList.add("hidden");
        }
    });
</script>

<?php include_once __DIR__ . '/includes/footer.php';
