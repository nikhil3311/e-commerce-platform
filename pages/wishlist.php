<?php require '../includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);
    $stmt = db()->prepare('SELECT 1 FROM wishlists WHERE user_id=? AND product_id=?');
    $stmt->execute([current_user()['id'], $productId]);
    if ($stmt->fetchColumn()) {
        $stmt = db()->prepare('DELETE FROM wishlists WHERE user_id=? AND product_id=?');
        $stmt->execute([current_user()['id'], $productId]);
        flash('success', 'Removed from your wishlist.');
    } else {
        $stmt = db()->prepare('INSERT INTO wishlists(user_id,product_id) VALUES(?,?)');
        $stmt->execute([current_user()['id'], $productId]);
        flash('success', 'Added to your wishlist.');
    }
    $returnTo = $_POST['return_to'] ?? 'wishlist.php';
    redirect(str_starts_with($returnTo, '/') ? 'wishlist.php' : $returnTo);
}
if (isset($_GET['remove'])) {
    $stmt = db()->prepare('DELETE FROM wishlists WHERE user_id=? AND product_id=?');
    $stmt->execute([current_user()['id'], (int)$_GET['remove']]);
    redirect('wishlist.php');
}
$stmt = db()->prepare('SELECT p.* FROM wishlists w JOIN products p ON p.id=w.product_id WHERE w.user_id=? ORDER BY w.created_at DESC');
$stmt->execute([current_user()['id']]);
$items = $stmt->fetchAll();
$pageTitle = 'Wishlist';
require '../includes/header.php'; ?><section class="section">
    <div class="container"><span class="eyebrow">Saved for later</span>
        <h1>Your wishlist.</h1>
        <div class="product-grid"><?php foreach ($items as $product): ?><article class="product-card"><a href="product.php?id=<?= $product['id'] ?>">
                        <div class="product-image"><img src="<?= e(asset_url((string)$product['image_url'], '../')) ?>" alt="<?= e($product['name']) ?>"></div>
                        <h3><?= e($product['name']) ?></h3>
                        <div class="price">$<?= number_format($product['price'], 2) ?></div>
                    </a><a class="text-link" href="wishlist.php?remove=<?= $product['id'] ?>">Remove</a></article><?php endforeach; ?></div><?php if (!$items): ?><div class="empty">Save the pieces you love while you browse.</div><?php endif; ?>
    </div>
</section><?php require '../includes/footer.php'; ?>