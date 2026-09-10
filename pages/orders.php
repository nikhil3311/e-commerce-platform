<?php require '../includes/functions.php';
require_login();
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();
$pageTitle = 'Your orders';
require '../includes/header.php'; ?><section class="section">
    <div class="container"><span class="eyebrow">Your account</span>
        <h1>Order history.</h1>
        <div class="form-panel responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($orders as $order): ?><tr>
                            <td><a class="order-link" href="order.php?id=<?= $order['id'] ?>"><strong><?= e($order['order_number']) ?></strong></a></td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            <td><span class="notice status-<?= strtolower(e($order['status'])) ?>" style="display:inline-block;padding:5px 8px"><?= e($order['status']) ?></span><div class="order-progress"><span style="width:<?= $order['status'] === 'Cancelled' ? 0 : (['Pending' => 20, 'Confirmed' => 40, 'Processing' => 60, 'Shipped' => 80, 'Delivered' => 100, 'Refunded' => 100][$order['status']] ?? 20) ?>%"></span></div></td>
                            <td>$<?= number_format($order['total'], 2) ?></td>
                        </tr><?php endforeach; ?></tbody>
            </table><?php if (!$orders): ?><div class="empty">Your order history is quiet for now.</div><?php endif; ?>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>