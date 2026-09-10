<?php require '../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $status = $_POST['status'] ?? 'Requested';
    $returnStmt = db()->prepare('SELECT status FROM returns WHERE id=?');
    $returnStmt->execute([(int)$_POST['id']]);
    $currentStatus = $returnStmt->fetchColumn();
    if ($status === 'Rejected' && $currentStatus === 'Requested') {
        $stmt = db()->prepare('UPDATE returns SET status=? WHERE id=? AND status=?');
        $stmt->execute(['Rejected', (int)$_POST['id'], 'Requested']);
        flash('success', 'Return request rejected.');
    } elseif ($status === 'Approved' && $currentStatus === 'Requested') {
        $stmt = db()->prepare('UPDATE returns SET status=? WHERE id=? AND status=?');
        $stmt->execute(['Approved', (int)$_POST['id'], 'Requested']);
        flash('success', 'Return request approved.');
    } elseif ($status === 'Refunded' && $currentStatus === 'Approved') {
        $stmt = db()->prepare('UPDATE returns SET status=? WHERE id=? AND status=?');
        $stmt->execute(['Refunded', (int)$_POST['id'], 'Approved']);
        $order = db()->prepare('UPDATE orders o JOIN returns r ON r.order_id=o.id SET o.status=? WHERE r.id=?');
        $order->execute(['Refunded', (int)$_POST['id']]);
        flash('success', 'Refund initiated.');
    }
    redirect('returns.php');
}
$returns = db()->query('SELECT r.*,o.order_number,u.name,u.email FROM returns r JOIN orders o ON o.id=r.order_id JOIN users u ON u.id=r.user_id ORDER BY r.created_at DESC')->fetchAll();
$cancelledOrders = db()->query('SELECT o.order_number,o.total,o.created_at,u.name,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.status="Cancelled" ORDER BY o.created_at DESC')->fetchAll();
$returnGroups = ['Requested', 'Rejected', 'Approved', 'Refunded'];
$returnsByStatus = array_fill_keys($returnGroups, []);
foreach ($returns as $return) $returnsByStatus[$return['status']][] = $return;
$pageTitle = 'Manage returns';
require '../includes/header.php'; ?><div class="admin-nav"><div class="container"><strong>ShopSphere / Admin</strong><nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav></div></div><section class="section"><div class="container"><span class="eyebrow">Customer care</span><h1>Returns.</h1><?php foreach ($returnGroups as $status): ?><section class="admin-order-group"><div class="section-head"><h2><?= e($status) ?></h2><span><?= count($returnsByStatus[$status]) ?> requests</span></div><div class="form-panel responsive-table"><table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Reason</th><th>Details</th><th>Action</th></tr></thead><tbody><?php foreach ($returnsByStatus[$status] as $return): ?><tr><td><?= e($return['order_number']) ?><br><small><?= date('M j, Y', strtotime($return['created_at'])) ?></small></td><td><?= e($return['name']) ?><br><small><?= e($return['email']) ?></small></td><td><?= e($return['reason']) ?></td><td><?= e($return['details']) ?></td><td><?php if ($status === 'Requested'): ?><form method="post" style="display:flex;gap:6px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $return['id'] ?>"><button class="button button-dark" name="status" value="Approved">Approve</button><button class="button button-coral" name="status" value="Rejected">Reject</button></form><?php elseif ($status === 'Approved'): ?><form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $return['id'] ?>"><button class="button button-dark" name="status" value="Refunded">Initiate refund</button></form><?php else: ?><span class="notice status-<?= strtolower($status) ?>" style="display:inline-block;padding:5px 8px"><?= e($status) ?></span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$returnsByStatus[$status]): ?><div class="empty">No <?= strtolower($status) ?> requests.</div><?php endif; ?></div></section><?php endforeach; ?><section class="admin-order-group"><div class="section-head"><h2>Cancelled orders</h2><span><?= count($cancelledOrders) ?> orders</span></div><div class="form-panel responsive-table"><table class="data-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Date</th></tr></thead><tbody><?php foreach ($cancelledOrders as $order): ?><tr><td><?= e($order['order_number']) ?></td><td><?= e($order['name']) ?><br><small><?= e($order['email']) ?></small></td><td>$<?= number_format($order['total'], 2) ?></td><td><?= date('M j, Y', strtotime($order['created_at'])) ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$cancelledOrders): ?><div class="empty">No cancelled orders.</div><?php endif; ?></div></section></div></section><?php require '../includes/footer.php'; ?>