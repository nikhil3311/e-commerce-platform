<?php require '../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    if ($name && $slug) {
        try {
            $stmt = db()->prepare('INSERT INTO categories(name,slug,description) VALUES(?,?,?)');
            $stmt->execute([$name, $slug, $description]);
            flash('success', 'Category created.');
        } catch (PDOException $exception) {
            flash('error', 'Category name or slug already exists.');
        }
    }
    redirect('categories.php');
}
$items = db()->query('SELECT c.*,COUNT(p.id) product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
$pageTitle = 'Manage categories';
require '../includes/header.php'; ?><div class="admin-nav">
    <div class="container"><strong>ShopSphere / Admin</strong>
        <nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav>
    </div>
</div>
<section class="section">
    <div class="container"><span class="eyebrow">Catalog structure</span>
        <h1>Categories.</h1>
        <form method="post" class="form-panel" style="margin-bottom:28px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <h2>Add category</h2>
            <div class="form-grid">
                <div class="field"><label>Name</label><input name="name" required></div>
                <div class="field"><label>Description</label><input name="description"></div>
            </div><button class="button button-coral" style="margin-top:18px">Create category</button>
        </form>
        <div class="form-panel responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($items as $item): ?><tr>
                            <td><strong><?= e($item['name']) ?></strong></td>
                            <td><?= e($item['slug']) ?></td>
                            <td><?= $item['product_count'] ?></td>
                            <td><?= e($item['description']) ?></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>