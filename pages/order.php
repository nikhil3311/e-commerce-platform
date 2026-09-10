<?php require '../includes/functions.php';
require_login();
$orderId = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$stmt->execute([$orderId, current_user()['id']]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    verify_csrf($_POST['csrf'] ?? '');
    if (in_array($order['status'], ['Pending', 'Confirmed', 'Processing'], true)) {
        $cancel = db()->prepare('UPDATE orders SET status=? WHERE id=? AND user_id=? AND status IN (?,?,?)');
        $cancel->execute(['Cancelled', $orderId, current_user()['id'], 'Pending', 'Confirmed', 'Processing']);
        flash('success', 'Your order has been cancelled.');
    } else {
        flash('error', 'This order can no longer be cancelled.');
    }
    redirect('order.php?id=' . $orderId);
}
$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id=? ORDER BY id');
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();
$returnStmt = db()->prepare('SELECT status FROM returns WHERE order_id=? AND user_id=? ORDER BY created_at DESC LIMIT 1');
$returnStmt->execute([$orderId, current_user()['id']]);
$returnStatus = $returnStmt->fetchColumn() ?: null;
$billing = json_decode($order['billing_json'], true) ?: [];
$steps = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered'];
$currentStep = array_search($order['status'], $steps, true);
$pageTitle = 'Track ' . $order['order_number'];
require '../includes/header.php'; ?><section class="section">
    <div class="container"><a class="text-link" href="orders.php">← Back to orders</a><span class="eyebrow order-eyebrow">Order tracking</span>
        <div class="order-heading"><div><h1><?= e($order['order_number']) ?></h1><p class="product-meta">Placed <?= date('M j, Y · g:i A', strtotime($order['created_at'])) ?></p></div><span class="notice status-<?= strtolower(e($order['status'])) ?>"><?= e($order['status']) ?></span></div>
        <?php if ($order['status'] === 'Cancelled'): ?><div class="notice error">This order was cancelled.</div><?php elseif ($returnStatus === 'Refunded' || $order['status'] === 'Refunded'): ?><div class="notice success">This order was Returned and Refunded.</div><?php else: ?><div class="tracking"><div class="tracking-line"></div><?php foreach ($steps as $index => $step): ?><div class="tracking-step <?= $currentStep !== false && $index <= $currentStep ? 'complete' : '' ?>"><span><?= $index + 1 ?></span><strong><?= e($step) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
        <div class="order-layout"><div class="form-panel"><div class="section-head"><h2>Items</h2><span><?= count($items) ?> products</span></div><?php foreach ($items as $item): ?><div class="order-item"><div><strong><?= e($item['product_name']) ?></strong><span><?= $item['quantity'] ?> × $<?= number_format((float)$item['unit_price'], 2) ?></span></div><strong>$<?= number_format((float)$item['unit_price'] * (int)$item['quantity'], 2) ?></strong></div><?php endforeach; ?></div>
            <aside class="summary"><h3>Delivery details</h3><div class="delivery-details"><strong><?= e($billing['full_name'] ?? '') ?></strong><span><?= e($billing['address'] ?? '') ?></span><span><?= e($billing['city'] ?? '') ?>, <?= e($billing['state'] ?? '') ?> <?= e($billing['postal_code'] ?? '') ?></span><span><?= e($billing['country'] ?? '') ?></span></div><div class="summary-row"><span>Subtotal</span><strong>$<?= number_format((float)$order['subtotal'], 2) ?></strong></div><div class="summary-row"><span>Shipping</span><strong><?= (float)$order['shipping'] ? '$' . number_format((float)$order['shipping'], 2) : 'Free' ?></strong></div><div class="summary-row summary-total"><span>Total</span><strong>$<?= number_format((float)$order['total'], 2) ?></strong></div><?php if (in_array($order['status'], ['Pending', 'Confirmed', 'Processing'], true)): ?><form method="post" data-native="true" style="margin-top:18px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><button class="button button-outline" name="cancel_order" value="1" data-confirm="Cancel this order?" style="width:100%">Cancel order</button></form><?php elseif ($order['status'] === 'Delivered' && !$returnStatus): ?><a class="button button-outline" style="width:100%;margin-top:18px" href="return.php?order_id=<?= $order['id'] ?>">Return or refund</a><?php elseif ($returnStatus === 'Rejected'): ?><div class="notice error" style="margin-top:18px">Refund or Return Request Rejected.</div><?php elseif ($returnStatus === 'Requested' || $returnStatus === 'Approved'): ?><div class="notice" style="margin-top:18px">Return or refund request: <?= e($returnStatus) ?></div><?php endif; ?></aside>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>