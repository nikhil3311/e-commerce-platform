<?php $pageTitle = 'Shop all products';
require '../includes/header.php';
$items = products($_GET);
$cats = categories(); ?><section class="section">
    <div class="container"><span class="eyebrow">The collection</span>
        <h1>Things worth keeping.</h1>
        <form class="filters"><input name="q" placeholder="Search by name, brand, or mood" value="<?= e($_GET['q'] ?? '') ?>"><select name="category">
                <option value="">All categories</option><?php foreach ($cats as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($_GET['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?>
            </select><select name="sort">
                <option value="">Newest first</option>
                <option value="selling">Best selling</option>
                <option value="rating">Highest rated</option>
                <option value="price_asc">Price: low to high</option>
                <option value="price_desc">Price: high to low</option>
            </select><button class="button button-dark">Filter</button></form>
        <div class="product-grid"><?php foreach ($items as $product): ?><article class="product-card"><a href="product.php?id=<?= $product['id'] ?>">
                        <div class="product-image"><img loading="lazy" src="<?= e(asset_url($product['image_url'], '../')) ?>" alt="<?= e($product['name']) ?>"></div>
                        <div class="product-meta"><?= e($product['brand_name'] ?? 'ShopSphere') ?> · <span class="rating">★ <?= $product['rating'] ?></span></div>
                        <h3><?= e($product['name']) ?></h3>
                        <div class="price">$<?= number_format((float)$product['price'], 2) ?><?php if ($product['compare_price']): ?><span class="old-price">$<?= number_format((float)$product['compare_price'], 2) ?></span><?php endif; ?></div>
                    </a>
                    <?php if (is_logged_in()): ?><form method="post" action="wishlist.php"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="return_to" value="products.php"><button class="quick-add wishlist-add" aria-label="Toggle <?= e($product['name']) ?> wishlist"><?= wishlist_has_product((int)$product['id']) ? '♥' : '♡' ?></button></form><?php endif; ?>
                </article><?php endforeach; ?></div><?php if (!$items): ?><div class="empty">No products matched that search.</div><?php endif; ?>
    </div>
</section><?php require '../includes/footer.php'; ?>