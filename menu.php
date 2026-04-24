<?php
$pageTitle = 'Bar & Cafeteria';
include_once __DIR__ . '/includes/header.php';
include_once __DIR__ . '/includes/db.php';

$menuStmt = $pdo->prepare(
    'SELECT c.name AS category, i.name, i.description, i.price
     FROM menu_items i
     JOIN menu_categories c ON i.category_id = c.id
     ORDER BY c.position, i.name'
);
$menuStmt->execute();
$menuItems = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

$menuByCategory = [];
foreach ($menuItems as $item) {
    $menuByCategory[$item['category']][] = $item;
}
?>

<section class="section">
    <h1>Bar & Cafeteria Menu</h1>
    <p>Enjoy snacks, drinks, and specials designed for your movie experience.</p>

    <?php if ($menuByCategory): ?>
        <?php foreach ($menuByCategory as $category => $items): ?>
            <div class="menu-category">
                <h2><?= htmlspecialchars($category) ?></h2>
                <ul class="menu-list">
                    <?php foreach ($items as $item): ?>
                        <li>
                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                            <span class="menu-price">$<?= number_format($item['price'], 2) ?></span>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>The menu is being updated. Please check again later.</p>
    <?php endif; ?>
</section>

<?php include_once __DIR__ . '/includes/footer.php';
