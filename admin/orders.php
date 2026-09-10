<?php require '../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $currentStmt = db()->prepare('SELECT status FROM orders WHERE id=?');
    $currentStmt->execute([(int)$_POST['id']]);
    $current = $currentStmt->fetchColumn();
    $nextStatus = ['Pending' => 'Confirmed', 'Confirmed' => 'Processing', 'Processing' => 'Shipped', 'Shipped' => 'Delivered'][$current] ?? null;
    if ($nextStatus) {
        $stmt = db()->prepare('UPDATE orders SET status=? WHERE id=? AND status=?');
        $stmt->execute([$nextStatus, (int)$_POST['id'], $current]);
    }
    flash('success', 'Order status updated.');
    redirect('orders.php');
}
$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$orderStmt = db()->prepare('SELECT o.*,u.name,u.email FROM orders o JOIN users u ON u.id=o.user_id WHERE o.status NOT IN ("Cancelled","Refunded") AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?) AND (? = "" OR o.status=?) ORDER BY o.created_at DESC');
$term = '%' . $search . '%';
$orderStmt->execute([$term, $term, $term, $statusFilter, $statusFilter]);
$orders = $orderStmt->fetchAll();
$statusGroups = [
    'Pending' => 'New orders',
    'Confirmed' => 'Confirmed',
    'Processing' => 'Processing',
    'Shipped' => 'Shipped',
    'Delivered' => 'Delivered',
];
$ordersByStatus = array_fill_keys(array_keys($statusGroups), []);
foreach ($orders as $order) $ordersByStatus[$order['status']][] = $order;
$pageTitle = 'Manage orders';
require '../includes/header.php'; ?><div class="admin-nav">
    <div class="container"><strong>ShopSphere / Admin</strong>
        <nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav>
    </div>
</div>
<section class="section">
    <div class="container"><span class="eyebrow">Operations</span>
        <h1>Orders.</h1><form class="filters" method="get"><input name="q" placeholder="Search order or customer" value="<?= e($search) ?>"><select name="status"><option value="">All active statuses</option><?php foreach (['Pending','Confirmed','Processing','Shipped','Delivered'] as $status): ?><option <?= $statusFilter === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select><button class="button button-outline">Filter</button></form>
        <?php foreach ($statusGroups as $status => $label): ?><section class="admin-order-group"><div class="section-head"><h2><?= e($label) ?></h2><span><?= count($ordersByStatus[$status]) ?> orders</span></div><div class="form-panel responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($ordersByStatus[$status] as $order): ?><tr>
                            <td><?= e($order['order_number']) ?></td>
                            <td><?= e($order['name']) ?><br><small><?= e($order['email']) ?></small></td>
                            <td>$<?= number_format($order['total'], 2) ?></td>
                            <td><?= e($order['status']) ?></td>
                            <td>
                                <div class="admin-order-actions"><?php $nextStatus = ['Pending' => 'Confirmed', 'Confirmed' => 'Processing', 'Processing' => 'Shipped', 'Shipped' => 'Delivered'][$order['status']] ?? null; ?><?php if ($nextStatus): ?><form method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= $order['id'] ?>"><button class="button button-dark" name="advance" value="1">Move to <?= e($nextStatus) ?></button></form><?php else: ?><span class="product-meta">Complete</span><?php endif; ?><?php if (in_array($order['status'], ['Confirmed', 'Processing', 'Shipped', 'Delivered'], true)): ?><a class="button button-outline admin-action" href="shipping-sticker.php?id=<?= $order['id'] ?>">Delivery details</a><?php endif; ?></div>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
            <?php if (!$ordersByStatus[$status]): ?><div class="empty">No <?= strtolower(e($label)) ?>.</div><?php endif; ?></div></section><?php endforeach; ?>
    </div>
</section><?php require '../includes/footer.php'; ?>