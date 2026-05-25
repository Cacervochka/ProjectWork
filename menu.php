<?php
$pageTitle = 'Bar & Cafeteria';
include_once __DIR__ . '/includes/header.php';
?>

<<<<<<< HEAD
<section class="section">
    <h1 data-i18n="menu.page.title">Bar & Cafeteria Menu</h1>
    <p data-i18n="menu.page.text">Enjoy snacks, drinks, and specials designed for your movie experience.</p>

    <?php if ($menuByCategory): ?>
        <?php foreach ($menuByCategory as $category => $items): ?>
            <?php $categorySlug = strtolower(str_replace(' ', '-', $category)); ?>
            <div class="menu-category" id="<?= htmlspecialchars($categorySlug) ?>">
                <h2 data-i18n="menu.category.<?= htmlspecialchars($categorySlug) ?>"><?= htmlspecialchars($category) ?></h2>
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
        <p data-i18n="menu.page.empty">The menu is being updated. Please check again later.</p>
    <?php endif; ?>
=======
<section class="section menuSection">
    <div class="menuSectionContent">
        <h2>Popular</h2>
        <div class="popularDeals">
            <div>
                <img src="./assets/menu popular/1.jpg" alt="">
                <h3>Date Night Combo</h3>
                <p>$19.99</p>
            </div>
            <div>
                <img src="./assets/menu popular/2.jpg" alt="">
                <h3>Popcorn & Drink Combo</h3>
                <p>$11.99</p>
            </div>
            <div>
                <img src="./assets/menu popular/3.jpg" alt="">
                <h3>Ice Cream Sundae</h3>
                <p>$5.99</p>
            </div>
            <div>
                <img src="./assets/menu popular/4.jpg" alt="">
                <h3>Butter Popcorn (Large)</h3>
                <p>$8.49</p>
            </div>
        </div>
        <div class="menuOffers">
            <div class="menuColumn">
                <h3>Snacks</h3>
                <p><span>Butter Popcorn (Small)</span> <span>$5.99</span></p>
                <p><span>Butter Popcorn (Large)</span> <span>$8.49</span></p>
                <p><span>Nachos with Cheese</span> <span>$6.79</span></p>
                <p><span>Pretzel Bites</span> <span>$5.49</span></p>
                <p><span>Hot Dog</span> <span>$4.99</span></p>
                <p><span>Chicken Tenders</span> <span>$7.99</span></p>
                <p><span>Mozzarella Sticks</span> <span>$6.49</span></p>
                <p><span>Candy Mix Box</span> <span>$4.29</span></p>

                <h3>Combos</h3>
                <p><span>Popcorn & Drink Combo</span> <span>$11.99</span></p>
                <p><span>Date Night Combo</span> <span>$19.99</span></p>
                <p><span>Family Movie Pack</span> <span>$29.99</span></p>
            </div>

            <div class="menuColumn">
                <h3>Drinks</h3>
                <p><span>Soft Drink (Small)</span> <span>$3.49</span></p>
                <p><span>Soft Drink (Large)</span> <span>$4.99</span></p>
                <p><span>Bottled Water</span> <span>$2.99</span></p>
                <p><span>Iced Tea</span> <span>$3.79</span></p>
                <p><span>Frozen Slushie</span> <span>$5.29</span></p>
                <p><span>Fresh Coffee</span> <span>$3.59</span></p>

                <h3>Desserts</h3>
                <p><span>Chocolate Brownie</span> <span>$4.49</span></p>
                <p><span>Ice Cream Sundae</span> <span>$5.99</span></p>
                <p><span>Chocolate Chip Cookies</span> <span>$3.99</span></p>
                <p><span>Mini Cheesecake</span> <span>$5.49</span></p>
                <p><span>Churro Bites</span> <span>$4.79</span></p>
            </div>
        </div>
    </div>
>>>>>>> origin/main
</section>

<?php include_once __DIR__ . '/includes/footer.php';
