<?php require '../includes/functions.php';
require_admin();
$stats = ['users' => db()->query('SELECT COUNT(*) FROM users')->fetchColumn(), 'products' => db()->query('SELECT COUNT(*) FROM products')->fetchColumn(), 'orders' => db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(), 'revenue' => db()->query('SELECT COALESCE(SUM(total),0) FROM orders WHERE status<>"Cancelled"')->fetchColumn(), 'pending' => db()->query('SELECT COUNT(*) FROM orders WHERE status IN ("Pending","Confirmed","Processing")')->fetchColumn(), 'returns' => db()->query('SELECT COUNT(*) FROM returns WHERE status="Requested"')->fetchColumn()];
$recent = db()->query('SELECT o.*,u.name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8')->fetchAll();
$low = db()->query('SELECT name,stock FROM products WHERE stock<10 ORDER BY stock')->fetchAll();
$pageTitle = 'Admin dashboard';
require '../includes/header.php'; ?><div class="admin-nav">
    <div style="background-color: black;color: white;padding: 10px;text-align: center;">This is Demo E-Commerce Website Project. Developed by Nikhil Patil. For Contact Visit: <a href="https://developwithnikhil.com" target="_blank" style="color: #007bff;text-decoration: underline;">developwithnikhil.com</a></div>
    <div class="container"><strong>ShopSphere / Admin</strong>
        <nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav>
    </div>
</div>
<section class="section">
    <div class="container"><span class="eyebrow">Overview</span>
        <h1>WELCOME, <?= e(explode(' ', $_SESSION['admin']['name'])[0]) ?>.</h1>
        <div class="stats">
            <div class="stat"><span class="product-meta">Users</span><strong><?= $stats['users'] ?></strong></div>
            <div class="stat"><span class="product-meta">Products</span><strong><?= $stats['products'] ?></strong></div>
            <div class="stat"><span class="product-meta">Orders</span><strong><?= $stats['orders'] ?></strong></div>
            <div class="stat"><span class="product-meta">Revenue</span><strong>$<?= number_format((float)$stats['revenue'], 0) ?></strong></div>
            <div class="stat"><span class="product-meta">Open orders</span><strong><?= $stats['pending'] ?></strong></div>
            <div class="stat"><span class="product-meta">Return requests</span><strong><?= $stats['returns'] ?></strong></div>
        </div>
        <div class="checkout-layout" style="margin-top:30px">
            <div class="form-panel">
                <div class="section-head">
                    <h2>Recent orders</h2><a class="text-link" href="orders.php">Manage</a>
                </div>
                <div class="responsive-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($recent as $order): ?><tr>
                                    <td><?= e($order['order_number']) ?></td>
                                    <td><?= e($order['name']) ?></td>
                                    <td><?= e($order['status']) ?></td>
                                    <td>$<?= number_format($order['total'], 2) ?></td>
                                </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </div>
            <aside class="summary">
                <h3>Low stock</h3><?php foreach ($low as $item): ?><div class="summary-row"><span><?= e($item['name']) ?></span><strong><?= $item['stock'] ?></strong></div><?php endforeach; ?><?php if (!$low): ?><p class="product-meta">Everything is well stocked.</p><?php endif; ?><a class="button button-dark" style="width:100%;margin-top:15px" href="products.php">Manage catalog</a>
            </aside>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>