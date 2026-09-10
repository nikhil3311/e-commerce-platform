<?php require '../includes/functions.php';
$product = product_by_id((int)($_GET['id'] ?? 0));
if (!$product) {
    http_response_code(404);
    exit('Product not found');
}
$related = products(['category' => $product['category_id'], 'limit' => 4]);
$specifications = json_decode((string)($product['specifications'] ?? ''), true) ?: [
    'Materials' => 'Considered, durable materials',
    'Care' => 'Easy care and built for regular use',
    'Dimensions' => 'Designed for everyday comfort and use',
];
$reviewStmt = db()->prepare('SELECT r.*,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC');
$reviewStmt->execute([$product['id']]);
$reviews = $reviewStmt->fetchAll();
$inCart = cart_has_product((int)$product['id']);
$inWishlist = wishlist_has_product((int)$product['id']);
$pageTitle = $product['name'];
require '../includes/header.php'; ?><section class="product-detail">
    <div class="container detail-grid">
        <div>
            <div class="detail-image" data-zoom><img id="product-main-image" src="<?= e(asset_url($product['image_url'], '../')) ?>" alt="<?= e($product['name']) ?>"></div>
            <div class="gallery-note">Hover to zoom · Tap image to enlarge</div>
        </div>
        <div class="detail-copy"><span class="eyebrow"><?= e($product['category_name']) ?> · <?= e($product['brand_name'] ?? 'ShopSphere') ?></span>
            <h1><?= e($product['name']) ?></h1>
            <div class="rating">★ <?= $product['rating'] ?> · <?= count($reviews) ? count($reviews) . ' customer reviews' : 'Loved by the community' ?></div>
            <div class="price">$<?= number_format((float)$product['price'], 2) ?> <span class="old-price">$<?= number_format((float)$product['compare_price'], 2) ?></span></div>
            <p><?= e($product['description']) ?></p>
            <div class="stock-line"><span class="stock-dot"></span><strong><?= (int)$product['stock'] > 0 ? (int)$product['stock'] . ' in stock' : 'Out of stock' ?></strong><span><?= (int)$product['stock'] > 0 ? '· Ships in 1–2 business days' : '· Check back soon' ?></span></div>
            <form method="post" action="cart.php" class="purchase-form" data-native="true"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="purchase-row">
                    <?php if ((int)$product['stock'] > 0): ?><div class="quantity"><button type="button" onclick="this.nextElementSibling.stepDown()">−</button><input data-quantity type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"><button type="button" onclick="this.previousElementSibling.stepUp()">+</button></div><?php if ($inCart): ?><button class="button button-outline" name="action" value="remove">Remove from cart</button><?php else: ?><button class="button button-outline" name="action" value="add">Add to bag</button><button class="button button-coral" name="action" value="buy_now">Buy now</button><?php endif; ?><?php endif; ?>
                </div>
            </form>
            <?php if (is_logged_in()): ?><form method="post" action="wishlist.php" class="inline-form product-wishlist-form"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="return_to" value="product.php?id=<?= $product['id'] ?>"><button class="button button-outline" aria-label="Toggle wishlist"><?= $inWishlist ? '♥' : '♡' ?></button></form><?php endif; ?>
            <div class="detail-promises"><span>✓ Free shipping over $75</span><span>✓ Easy 30-day returns</span><span>✓ Secure checkout</span></div>
        </div>
    </div>
</section>
<section class="service-strip product-services">
    <div class="container service-grid">
        <div><strong>01</strong><span><b>Free shipping</b><small>On orders over $75</small></span></div>
        <div><strong>02</strong><span><b>30-day returns</b><small>Simple, no-fuss returns</small></span></div>
        <div><strong>03</strong><span><b>Secure checkout</b><small>Demo payments only</small></span></div>
        <div><strong>04</strong><span><b>Fast dispatch</b><small>Ships in 1–2 business days</small></span></div>
    </div>
</section>
<section class="section detail-sections">
    <div class="container detail-tabs">
        <div><span class="eyebrow">Product notes</span>
            <h2>Made for the everyday.</h2>
            <p><?= e($product['description']) ?> Designed for daily use, made to last, and backed by our simple returns promise.</p>
        </div>
        <div class="spec-list">
            <?php foreach ($specifications as $label => $value): ?><div><span><?= e((string)$label) ?></span><strong><?= e((string)$value) ?></strong></div><?php endforeach; ?>
            <div><span>Dispatch</span><strong>Ships within 1–2 business days</strong></div>
            <div><span>Returns</span><strong>30 days, no awkward questions</strong></div>
        </div>
    </div>
</section>
<section class="section reviews-section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Customer notes</span>
                <h2>Reviews from the community.</h2>
            </div>
            <div class="review-score"><strong><?= $product['rating'] ?></strong><span class="rating">★★★★★</span><small>Average rating</small></div>
        </div><?php if (is_logged_in()): ?><form method="post" action="review.php" class="review-form"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><h3>Rate this product</h3><div class="field"><label>Rating</label><select name="rating" required><option value="">Choose stars</option><?php for ($rating = 5; $rating >= 1; $rating--): ?><option value="<?= $rating ?>"><?= $rating ?> star<?= $rating === 1 ? '' : 's' ?></option><?php endfor; ?></select></div><div class="field"><label>Title</label><input name="title" maxlength="120" required></div><div class="field"><label>Your review</label><textarea name="body" rows="4" required></textarea></div><button class="button button-dark">Submit rating</button></form><?php endif; ?><?php if ($reviews): ?><div class="review-grid"><?php foreach ($reviews as $review): ?><article class="review">
                        <div class="rating"><?= str_repeat('★', (int)$review['rating']) ?></div>
                        <h3><?= e($review['title']) ?></h3>
                        <p><?= e($review['body']) ?></p><small><?= e($review['name']) ?> · Verified buyer</small>
                    </article><?php endforeach; ?></div><?php else: ?><div class="empty">Be the first to share your experience.</div><?php endif; ?>
    </div>
</section>
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">You may also like</span>
                <h2>More from <?= e($product['category_name']) ?>.</h2>
            </div>
        </div>
        <div class="product-grid"><?php foreach ($related as $item): if ($item['id'] === $product['id']) continue; ?><article class="product-card"><a href="product.php?id=<?= $item['id'] ?>">
                        <div class="product-image"><img loading="lazy" src="<?= e(asset_url($item['image_url'], '../')) ?>" alt="<?= e($item['name']) ?>"></div>
                        <h3><?= e($item['name']) ?></h3>
                        <div class="price">$<?= number_format((float)$item['price'], 2) ?></div>
                    </a></article><?php endforeach; ?></div>
    </div>
</section><?php require '../includes/footer.php'; ?>