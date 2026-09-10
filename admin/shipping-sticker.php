<?php require '../includes/functions.php';
require_admin();
$stmt = db()->prepare('SELECT o.*,u.name,u.email,u.phone FROM orders o JOIN users u ON u.id=o.user_id WHERE o.id=?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}
$billing = json_decode($order['billing_json'], true) ?: [];
$itemStmt = db()->prepare('SELECT product_name,quantity FROM order_items WHERE order_id=? ORDER BY id');
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Order details <?= e($order['order_number']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="order-details-page">
        <div class="order-details-actions no-print"><a class="button button-outline" href="orders.php">Back to orders</a> <button class="button button-dark" onclick="window.print()">Print delivery label</button></div>
        <section class="order-details-card">
            <div class="print-logo"><span class="brand-mark">S</span><strong>ShopSphere</strong></div>
            <div class="order-details-heading"><div><span class="eyebrow">ShopSphere order details</span><h1><?= e($order['order_number']) ?></h1></div><span class="notice status-<?= strtolower(e($order['status'])) ?> print-hide-status"><?= e($order['status']) ?></span></div>
            <div class="order-details-meta"><div><span>Order date</span><strong><?= date('M j, Y · g:i A', strtotime($order['created_at'])) ?></strong></div><div><span>Payment method</span><strong><?= e($order['payment_method']) ?></strong></div></div>
            <div class="order-details-grid">
                <div class="order-details-block"><h2>Deliver from</h2><strong>ShopSphere</strong><span>Demo Commerce Center</span><span>101 Market Street</span><span>Pune, Maharashtra 411001</span><span>India</span></div>
                <div class="order-details-block"><h2>Deliver to</h2><strong><?= e($billing['full_name'] ?? $order['name']) ?></strong><span><?= e($billing['address'] ?? '') ?></span><span><?= e(($billing['city'] ?? '') . ', ' . ($billing['state'] ?? '') . ' ' . ($billing['postal_code'] ?? '')) ?></span><span><?= e($billing['country'] ?? '') ?></span></div>
                <div class="order-details-block"><h2>Customer contact</h2><span><?= e($billing['email'] ?? $order['email']) ?></span><span><?= e($billing['phone'] ?? $order['phone']) ?></span></div>
            </div>
            <div class="order-details-items"><h2>Products in delivery box</h2><?php foreach ($items as $item): ?><div class="summary-row"><span><?= e($item['product_name']) ?></span><strong>Qty <?= (int)$item['quantity'] ?></strong></div><?php endforeach; ?></div>
        </section>
    </main>
</body>
</html>