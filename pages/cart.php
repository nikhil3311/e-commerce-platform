<?php require '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    if (isset($_POST['apply_coupon'])) {
        if (apply_coupon($_POST['coupon_code'] ?? '')) flash('success', 'Coupon applied.');
        else flash('error', 'That coupon is invalid or has expired.');
        redirect('cart.php');
    }
    if (isset($_POST['remove_coupon'])) {
        clear_coupon();
        flash('success', 'Coupon removed.');
        redirect('cart.php');
    }
    if (isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        if (($_POST['action'] ?? '') === 'remove') {
            remove_from_cart($productId);
            flash('success', 'Removed from your bag.');
            redirect('product.php?id=' . $productId);
        }
        add_to_cart($productId, (int)($_POST['quantity'] ?? 1));
        flash('success', 'Added to your bag.');
        if (($_POST['action'] ?? '') === 'buy_now') redirect('cart.php');
        if (($_POST['action'] ?? '') === 'add') redirect('product.php?id=' . $productId);
    } elseif (isset($_POST['update'])) {
        update_cart($_POST['quantities'] ?? []);
        flash('success', 'Bag updated.');
    }
    redirect('cart.php');
}
if (isset($_GET['remove'])) {
    remove_from_cart((int)$_GET['remove']);
    redirect('cart.php');
}
$items = cart_items();
$subtotal = cart_total();
$shipping = $subtotal >= 75 || $subtotal === 0 ? 0 : 7.95;
$coupon = applied_coupon();
$discount = $coupon ? round($subtotal * ((int)$coupon['discount_percent'] / 100), 2) : 0;
$pageTitle = 'Your bag';
require '../includes/header.php'; ?><section class="section">
    <div class="container"><span class="eyebrow">Shopping bag</span>
        <h1>Your considered cart.</h1><?php if (!$items): ?><div class="empty">
                <p>Your bag is waiting for something good.</p><a class="button button-dark" href="products.php">Continue shopping</a>
            </div><?php else: ?><form method="post" class="cart-layout"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <div class="form-panel">
                    <div class="section-head">
                        <h2>Items</h2><span><?= cart_count() ?> items</span>
                    </div><?php foreach ($items as $item): ?><div class="cart-item"><img src="<?= e(asset_url($item['image_url'], '../')) ?>" alt="<?= e($item['name']) ?>">
                            <div>
                                <h3><?= e($item['name']) ?></h3>
                                <div class="product-meta">$<?= number_format((float)$item['price'], 2) ?> · <a class="remove" href="cart.php?remove=<?= $item['id'] ?>">Remove</a></div>
                            </div><input data-quantity type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>" style="width:65px;padding:9px;border:1px solid var(--line)">
                        </div><?php endforeach; ?><button name="update" class="button button-outline" style="margin-top:20px">Update bag</button>
                </div>
                <aside class="summary">
                    <h3>Order summary</h3>
                    <div class="summary-row"><span>Subtotal</span><strong>$<?= number_format($subtotal, 2) ?></strong></div>
                    <div class="coupon-box"><label for="coupon_code">Coupon code</label><div><input id="coupon_code" name="coupon_code" placeholder="Enter code" value="<?= e($coupon['code'] ?? '') ?>"><button class="button button-outline" name="apply_coupon">Apply</button></div><?php if ($coupon): ?><small><?= e($coupon['code']) ?> saves <?= (int)$coupon['discount_percent'] ?>% <button class="text-link" name="remove_coupon">Remove</button></small><?php endif; ?></div>
                    <div class="summary-row"><span>Shipping</span><strong><?= $shipping ? '$' . number_format($shipping, 2) : 'Free' ?></strong></div>
                    <?php if ($discount): ?><div class="summary-row discount-row"><span>Discount</span><strong>-$<?= number_format($discount, 2) ?></strong></div><?php endif; ?><div class="summary-row summary-total"><span>Total</span><strong>$<?= number_format($subtotal + $shipping - $discount, 2) ?></strong></div><a class="button button-coral" style="width:100%;margin-top:20px" href="checkout.php">Checkout securely</a>
                </aside>
            </form><?php endif; ?>
    </div>
</section><?php require '../includes/footer.php'; ?>