<?php require '../includes/functions.php';
require_login();
$orderId = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$orderStmt = db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$orderStmt->execute([$orderId, current_user()['id']]);
$order = $orderStmt->fetch();
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}
if ($order['status'] !== 'Delivered') {
    flash('error', 'Returns and refunds are available after delivery.');
    redirect('order.php?id=' . $orderId);
}
$returnStmt = db()->prepare('SELECT * FROM returns WHERE order_id=? AND user_id=? ORDER BY created_at DESC LIMIT 1');
$returnStmt->execute([$orderId, current_user()['id']]);
$existing = $returnStmt->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existing) {
    verify_csrf($_POST['csrf'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $allowed = ['Damaged item', 'Wrong item', 'Item not as expected', 'Changed my mind'];
    if (!in_array($reason, $allowed, true)) {
        flash('error', 'Please choose a valid return reason.');
    } else {
        $stmt = db()->prepare('INSERT INTO returns(order_id,user_id,reason,details) VALUES(?,?,?,?)');
        $stmt->execute([$orderId, current_user()['id'], $reason, $details]);
        flash('success', 'Your return request has been submitted.');
        redirect('order.php?id=' . $orderId);
    }
}
$pageTitle = 'Return order ' . $order['order_number'];
require '../includes/header.php'; ?><section class="section"><div class="container auth-wrap"><a class="text-link" href="order.php?id=<?= $orderId ?>">← Back to order</a><span class="eyebrow order-eyebrow">Returns and refunds</span><h1>Request a return.</h1><p class="product-meta">Order <?= e($order['order_number']) ?> · We review requests and update you from your order history.</p><?php if ($existing): ?><div class="form-panel"><h2>Request received</h2><div class="account-stat"><span>Status</span><strong><?= e($existing['status']) ?></strong></div><div class="account-stat"><span>Reason</span><strong><?= e($existing['reason']) ?></strong></div><p><?= e($existing['details']) ?></p></div><?php else: ?><form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="order_id" value="<?= $orderId ?>"><div class="field"><label>Reason</label><select name="reason" required><option value="">Choose a reason</option><option>Damaged item</option><option>Wrong item</option><option>Item not as expected</option><option>Changed my mind</option></select></div><div class="field" style="margin-top:15px"><label>Additional details</label><textarea name="details" rows="5" placeholder="Tell us what happened"></textarea></div><button class="button button-coral" style="margin-top:20px">Submit return request</button></form><?php endif; ?></div></section><?php require '../includes/footer.php'; ?>