<?php
date_default_timezone_set('Europe/Kyiv');

// get past tickets (already watched movies)
$sql = "
SELECT 
    t.id AS ticket_id,
    t.seat_number,
    
    s.show_time,
    s.room,

    m.id AS movie_id,
    m.title

FROM tickets t
JOIN schedules s ON t.schedule_id = s.id
JOIN movies m ON s.movie_id = m.id

WHERE t.user_id = :user_id
AND s.show_time < NOW()

ORDER BY s.show_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user["id"]]);

$pastTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);


// get existing reviews by this user
$reviewStmt = $pdo->prepare("
    SELECT * FROM reviews WHERE user_id = :user_id
");
$reviewStmt->execute(['user_id' => $user["id"]]);

$reviews = [];
foreach ($reviewStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $reviews[$r["movie_id"]] = $r;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_id"])) {

    $movieId = $_POST["movie_id"];
    $text = trim($_POST["review_text"]);
    $rating = (int)($_POST["rating"] ?? 0);

    // basic validation
    if ($movieId && $text !== "") {

        // check if review exists
        $check = $pdo->prepare("
            SELECT id FROM reviews 
            WHERE user_id = :uid AND movie_id = :mid
        ");
        $check->execute([
            'uid' => $user["id"],
            'mid' => $movieId
        ]);

        if ($check->fetch()) {
            if ($check->fetch()) {

                // update review text
                $updReview = $pdo->prepare("
        UPDATE reviews 
        SET review_text = :text
        WHERE user_id = :uid AND movie_id = :mid
    ");
                $updReview->execute([
                    'text' => $text,
                    'uid' => $user["id"],
                    'mid' => $movieId
                ]);

                // update rating
                $updRating = $pdo->prepare("
        UPDATE movie_ratings 
        SET rating = :rating
        WHERE user_id = :uid AND movie_id = :mid
    ");
                $updRating->execute([
                    'rating' => $rating,
                    'uid' => $user["id"],
                    'mid' => $movieId
                ]);
            }
        } else {
            // insert review text
            $insReview = $pdo->prepare("
        INSERT INTO reviews (movie_id, user_id, review_text, created_at)
        VALUES (:mid, :uid, :text, NOW())
    ");
            $insReview->execute([
                'mid' => $movieId,
                'uid' => $user["id"],
                'text' => $text
            ]);

            // insert rating
            $insRating = $pdo->prepare("
        INSERT INTO movie_ratings (movie_id, user_id, rating)
        VALUES (:mid, :uid, :rating)
    ");
            $insRating->execute([
                'mid' => $movieId,
                'uid' => $user["id"],
                'rating' => $rating
            ]);
        }

        header("Location: profile.php?viewSection=2");
        exit;
    }
}

?>

<div class="profile-nav">
    <button><a href="profile.php?viewSection=1" data-i18n="profile.nav.tickets">Tickets</a></button>
    <button class="active"><a href="profile.php?viewSection=2" data-i18n="profile.nav.reviews">Reviews</a></button>
    <button><a href="profile.php?viewSection=3" data-i18n="profile.nav.settings">Settings</a></button>
</div>

<div class="profileSubSection">
    <h3 data-i18n="profile.reviews.title">Your reviews:</h3>

    <?php if (empty($pastTickets)): ?>
        <div>
            <p data-i18n="profile.reviews.empty">No events visited</p>
            <a href="current.php" data-i18n="profile.browse">Browse upcoming events</a>
        </div>
    <?php else: ?>

        <?php foreach ($pastTickets as $t): ?>
            <?php $review = $reviews[$t["movie_id"]] ?? null; ?>

            <div class="eventElement">
                <div>
                    <div>
                        <h3 data-i18n="movie.title.<?= (int) $t["movie_id"] ?>"><?= htmlspecialchars($t["title"]) ?></h3>
                        <p>
                            <?= date("d/m/Y - H:i", strtotime($t["show_time"])) ?>
                            - <?= htmlspecialchars($t["room"]) ?>
                            - <span data-i18n="profile.seat">Seat</span> <?= htmlspecialchars($t["seat_number"]) ?>
                        </p>
                    </div>

                    <div>
                        <button>
                            <a href="movie.php?id=<?= $t["movie_id"] ?>" data-i18n="profile.filmDetails">Film details</a>
                        </button>
                        <button>
                            <a href="ticket.php?id=<?= $t["ticket_id"] ?>" data-i18n="profile.ticket">Ticket</a>
                        </button>
                    </div>
                </div>

                <div class="reviewElement">

                    <?php if (!$review): ?>
                        <!-- ===================== -->
                        <!-- NO REVIEW → FORM -->
                        <!-- ===================== -->

                        <form method="POST">
                            <div class="reviewHeader">
                                <button type="button" class="add-review-btn" data-i18n="profile.review.add">Add review</button>
                                <button type="submit" class="publish-btn" data-i18n="profile.review.publish">Publish</button>
                            </div>

                            <!-- rating (simple hidden or clickable later) -->
                            <input type="hidden" name="rating" class="ratingInput" value="0">

                            <div class="stars starRating">
                                <span data-value="1">☆</span>
                                <span data-value="2">☆</span>
                                <span data-value="3">☆</span>
                                <span data-value="4">☆</span>
                                <span data-value="5">☆</span>
                            </div>

                            <textarea
                                name="review_text"
                                placeholder="Tell us about your experience"
                                data-i18n-placeholder="profile.review.placeholder"
                                maxlength="600"
                                required></textarea>

                            <input type="hidden" name="movie_id" value="<?= $t["movie_id"] ?>">
                        </form>

                    <?php else: ?>
                        <!-- ===================== -->
                        <!-- EXISTING REVIEW -->
                        <!-- ===================== -->

                        <div class="reviewHeader">
                            <button class="add-review-btn" data-i18n="profile.review.yours">Your review</button>
                        </div>

                        <div class="stars">
                            <span>★★★★★</span>
                        </div>

                        <p><?= htmlspecialchars($review["review_text"]) ?></p>

                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
