<?php
date_default_timezone_set('Europe/Kyiv');

/* =========================
   SAVE REVIEW
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["save_review"])) {
    echo "<pre>";
print_r($_POST);
echo "</pre>";

    $movieId = (int)$_POST["movie_id"];
    $rating = (int)$_POST["rating"];
    $reviewText = trim($_POST["review_text"]);

    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }

    if ($reviewText !== "") {

        // save review text
        $insertReview = $pdo->prepare("
            INSERT INTO reviews (
                user_id,
                movie_id,
                review_text,
                created_at
            )
            VALUES (
                :user_id,
                :movie_id,
                :review_text,
                NOW()
            )
        ");

        $insertReview->execute([
            'user_id' => $user["id"],
            'movie_id' => $movieId,
            'review_text' => $reviewText
        ]);

        // save numeric rating
        $insertRating = $pdo->prepare("
            INSERT INTO movie_ratings (
                movie_id,
                user_id,
                rating
            )
            VALUES (
                :movie_id,
                :user_id,
                :rating
            )
        ");

        $insertRating->execute([
            'movie_id' => $movieId,
            'user_id' => $user["id"],
            'rating' => $rating
        ]);

        header("Location: profile.php?viewSection=2");
        exit;
    }
}

// get tickets for review section
$sql = "
SELECT 
    t.id AS ticket_id,
    t.seat_number,
    
    s.show_time,
    s.room,
    CASE WHEN DATE(s.show_time) <= CURDATE() THEN 1 ELSE 0 END AS can_review,

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
            <?php
            $review = $reviews[$t["movie_id"]] ?? null;
            $canReview = (bool) $t["can_review"];
            ?>

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

                    <?php if (!$review && $canReview): ?>
                        <!-- ===================== -->
                        <!-- NO REVIEW → FORM -->
                        <!-- ===================== -->

                        <form method="POST" action="profile.php?viewSection=2">
                            <div class="reviewHeader">
                                <button type="button" class="add-review-btn" data-i18n="profile.review.add">Add review</button>
                                <button type="submit" class="publish-btn" data-i18n="profile.review.publish">Publish</button>
                            </div>

                            <!-- rating (simple hidden or clickable later) -->
                            <input type="hidden" name="rating" class="ratingInput" value="5">

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
                            <input type="hidden" name="save_review" value="1">
                        </form>

                    <?php elseif ($review): ?>
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

                    <?php else: ?>
                        <p data-i18n="profile.review.afterShow">You can leave a review after the show.</p>

                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>
