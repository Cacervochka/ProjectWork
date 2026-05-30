<?php
header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/search_helpers.php';

$query = trim($_GET['q'] ?? '');
$variants = buildSearchVariants($query);

if (!$variants) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$whereParts = [];
$params = [];

foreach ($variants as $index => $variant) {
    $whereParts[] = "title LIKE :title_start_$index OR title LIKE :title_any_$index OR genre LIKE :genre_any_$index";
    $params["title_start_$index"] = $variant . '%';
    $params["title_any_$index"] = '%' . $variant . '%';
    $params["genre_any_$index"] = '%' . $variant . '%';
}

$params['primary_start_order'] = $variants[0] . '%';
$params['primary_any_order'] = '%' . $variants[0] . '%';

$suggestStmt = $pdo->prepare(
    'SELECT id, title, genre
     FROM movies
     WHERE ' . implode(' OR ', $whereParts) . '
     ORDER BY
        CASE
            WHEN title LIKE :primary_start_order THEN 0
            WHEN title LIKE :primary_any_order THEN 1
            ELSE 2
        END,
        title ASC
     LIMIT 8'
);

$suggestStmt->execute($params);

echo json_encode($suggestStmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
