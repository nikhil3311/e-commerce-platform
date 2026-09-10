<?php require '../includes/functions.php';
require_login();
$items = cart_items();
if (!$items) redirect('cart.php');
$subtotal = cart_total();
$shipping = $subtotal >= 75 ? 0 : 7.95;
$coupon = applied_coupon();
$discount = $coupon ? round($subtotal * ((int)$coupon['discount_percent'] / 100), 2) : 0;
$userStmt = db()->prepare('SELECT full_name,address,city,state,postal_code,country FROM users WHERE id=?');
$userStmt->execute([current_user()['id']]);
$savedAddress = $userStmt->fetch();
$savedAddresses = $savedAddress && $savedAddress['address'] ? [$savedAddress] : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    if (isset($_POST['apply_coupon'])) {
        if (apply_coupon($_POST['coupon_code'] ?? '')) flash('success', 'Coupon applied.');
        else flash('error', 'That coupon is invalid or has expired.');
        redirect('checkout.php');
    }
    if (isset($_POST['remove_coupon'])) {
        clear_coupon();
        flash('success', 'Coupon removed.');
        redirect('checkout.php');
    }
    if (!empty($_POST['address_id']) && $savedAddress) foreach (['full_name', 'address', 'city', 'state', 'postal_code', 'country'] as $field) $_POST[$field] = $savedAddress[$field];
    $required = ['full_name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country'];
    $valid = true;
    foreach ($required as $key) {
        if (trim($_POST[$key] ?? '') === '') $valid = false;
    }
    if (!$valid || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please complete every billing field.');
    } else {
        try {
            $pdo = db();
            $pdo->beginTransaction();
            foreach ($items as $item) {
                $check = $pdo->prepare('SELECT stock FROM products WHERE id=? FOR UPDATE');
                $check->execute([$item['id']]);
                if ((int)$check->fetchColumn() < (int)$item['quantity']) throw new RuntimeException('Stock changed for ' . $item['name']);
            }
            $number = 'SS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $billing = json_encode(array_map('trim', array_intersect_key($_POST, array_flip($required))), JSON_THROW_ON_ERROR);
            $total = max(0, $subtotal + $shipping - $discount);
            $stmt = $pdo->prepare('INSERT INTO orders(order_number,user_id,subtotal,shipping,discount,total,payment_method,billing_json) VALUES(?,?,?,?,?,?,?,?)');
            $stmt->execute([$number, current_user()['id'], $subtotal, $shipping, $discount, $total, $_POST['payment_method'] ?? 'Cash on Delivery', $billing]);
            $orderId = (int)$pdo->lastInsertId();
            $address = $pdo->prepare('UPDATE users SET full_name=?,address=?,city=?,state=?,postal_code=?,country=? WHERE id=?');
            $address->execute([$_POST['full_name'], $_POST['address'], $_POST['city'], $_POST['state'], $_POST['postal_code'], $_POST['country'], current_user()['id']]);
            $line = $pdo->prepare('INSERT INTO order_items(order_id,product_id,product_name,unit_price,quantity) VALUES(?,?,?,?,?)');
            $stock = $pdo->prepare('UPDATE products SET stock=stock-?,sold_count=sold_count+? WHERE id=?');
            foreach ($items as $item) {
                $line->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['quantity']]);
                $stock->execute([$item['quantity'], $item['quantity'], $item['id']]);
            }
            $clear = $pdo->prepare('DELETE ci FROM cart_items ci JOIN carts c ON c.id=ci.cart_id WHERE c.user_id=?');
            $clear->execute([current_user()['id']]);
            $pdo->commit();
            clear_coupon();
            flash('success', 'Order ' . $number . ' placed successfully.');
            redirect('orders.php');
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            flash('error', 'We could not place that order. Please try again.');
        }
    }
}
$pageTitle = 'Checkout';
require '../includes/header.php'; ?><section class="section">
    <div class="container"><span class="eyebrow">Almost yours</span>
        <h1>Checkout.</h1>
        <form method="post" class="checkout-layout"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-panel">
                <h2>Delivery details</h2>
                <?php if ($savedAddresses): ?><div class="field saved-address-select"><label>Use a saved address</label><select name="address_id" data-address-select><option value="">Enter a different address</option><?php foreach ($savedAddresses as $address): ?><option value="1" data-full_name="<?= e($address['full_name']) ?>" data-address="<?= e($address['address']) ?>" data-city="<?= e($address['city']) ?>" data-state="<?= e($address['state']) ?>" data-postal_code="<?= e($address['postal_code']) ?>" data-country="<?= e($address['country']) ?>"> <?= e($address['address'] . ', ' . $address['city']) ?></option><?php endforeach; ?></select><a class="text-link" href="profile.php?edit=1">Edit saved details</a></div><?php endif; ?>
                <div class="form-grid">
                    <div class="field"><label>Full name</label><input data-address-field="full_name" name="full_name" required value="<?= e($_POST['full_name'] ?? current_user()['name']) ?>"></div>
                    <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e(current_user()['email']) ?>"></div>
                    <div class="field"><label>Phone</label><input name="phone" required value="<?= e(current_user()['phone']) ?>"></div>
                    <div class="field"><label>Country</label><input data-address-field="country" name="country" required value="<?= e($_POST['country'] ?? 'India') ?>"></div>
                    <div class="field full"><label>Address</label><input data-address-field="address" name="address" required placeholder="Street and apartment" value="<?= e($_POST['address'] ?? '') ?>"></div>
                    <div class="field"><label>City</label><input data-address-field="city" name="city" required value="<?= e($_POST['city'] ?? '') ?>"></div>
                    <div class="field"><label>State</label><input data-address-field="state" name="state" required value="<?= e($_POST['state'] ?? '') ?>"></div>
                    <div class="field"><label>Postal code</label><input data-address-field="postal_code" name="postal_code" required value="<?= e($_POST['postal_code'] ?? '') ?>"></div>
                </div>
                <h2 style="margin-top:35px">Payment</h2>
                <div class="field"><select name="payment_method">
                        <option>Cash on Delivery</option>
                        <option>Demo Card Payment</option>
                        <option>Demo UPI</option>
                    </select></div>
                <p class="product-meta">Demo payment methods never charge a real card or bank account.</p><button class="button button-coral" style="margin-top:20px">Place order · $<?= number_format($subtotal + $shipping - $discount, 2) ?></button>
            </div>
                <aside class="summary">
                <h3>Order summary</h3><?php foreach ($items as $item): ?><div class="summary-row"><span><?= e($item['name']) ?> × <?= $item['quantity'] ?></span><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></div><?php endforeach; ?><div class="coupon-box"><label for="coupon_code">Coupon code</label><div><input id="coupon_code" name="coupon_code" placeholder="Enter code" value="<?= e($coupon['code'] ?? '') ?>"><button class="button button-outline" name="apply_coupon">Apply</button></div><?php if ($coupon): ?><small><?= e($coupon['code']) ?> saves <?= (int)$coupon['discount_percent'] ?>% <button class="text-link" name="remove_coupon">Remove</button></small><?php endif; ?></div><div class="summary-row"><span>Shipping</span><strong><?= $shipping ? '$' . number_format($shipping, 2) : 'Free' ?></strong></div><?php if ($discount): ?><div class="summary-row discount-row"><span>Discount</span><strong>-$<?= number_format($discount, 2) ?></strong></div><?php endif; ?>
                <div class="summary-row summary-total"><span>Total</span><strong>$<?= number_format($subtotal + $shipping - $discount, 2) ?></strong></div>
            </aside>
        </form>
    </div>
</section><?php require '../includes/footer.php'; ?>