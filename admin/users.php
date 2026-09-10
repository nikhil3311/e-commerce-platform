<?php require '../includes/functions.php';
require_admin();
$users = db()->query('SELECT id,name,email,phone,created_at FROM users ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Manage users';
require '../includes/header.php'; ?><div class="admin-nav">
    <div class="container"><strong>ShopSphere / Admin</strong>
        <nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav>
    </div>
</div>
<section class="section">
    <div class="container"><span class="eyebrow">Customers</span>
        <h1>Users.</h1>
        <div class="form-panel responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($users as $user): ?><tr>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['phone']) ?></td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>