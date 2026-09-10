<?php require '../includes/functions.php';
require_admin();
if (isset($_GET['delete'])) {
    $stmt = db()->prepare('DELETE FROM products WHERE id=?');
    $stmt->execute([(int)$_GET['delete']]);
    flash('success', 'Product removed.');
    redirect('products.php');
}
if (isset($_GET['toggle'])) {
    $stmt = db()->prepare('UPDATE products SET is_active=1-is_active WHERE id=?');
    $stmt->execute([(int)$_GET['toggle']]);
    flash('success', 'Product visibility updated.');
    redirect('products.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    $imagePath = trim($_POST['existing_image'] ?? '');
    if (!empty($_FILES['image']['tmp_name'])) {
        $image = $_FILES['image'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > 5 * 1024 * 1024 || !isset($extensions[$mime]) || @getimagesize($image['tmp_name']) === false) {
            flash('error', 'Please upload a JPG, PNG, or WebP image under 5 MB.');
            redirect('products.php' . (!empty($_POST['product_id']) ? '?edit=' . (int)$_POST['product_id'] : ''));
        }
        $uploadDir = __DIR__ . '/../uploads/products';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename = 'product-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($image['tmp_name'], $uploadDir . '/' . $filename)) {
            flash('error', 'Product image could not be saved.');
            redirect('products.php');
        }
        $imagePath = 'uploads/products/' . $filename;
    }
    if (!$imagePath) {
        flash('error', 'Please upload a product image.');
        redirect('products.php');
    }
    $values = [(int)$_POST['category_id'], $name, $slug, trim($_POST['description']), $imagePath, max(0, (float)$_POST['price']), max(0, (float)$_POST['compare_price']), max(0, (int)$_POST['stock'])];
    if (!empty($_POST['product_id'])) {
        $stmt = db()->prepare('UPDATE products SET category_id=?,name=?,slug=?,description=?,image_url=?,price=?,compare_price=?,stock=? WHERE id=?');
        $stmt->execute([...$values, (int)$_POST['product_id']]);
        flash('success', 'Product updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO products(category_id,brand_id,name,slug,description,image_url,price,compare_price,stock) VALUES(?,?,?,?,?,?,?,?,?)');
        $stmt->execute([(int)$_POST['category_id'], null, $name, $slug, trim($_POST['description']), $imagePath, max(0, (float)$_POST['price']), max(0, (float)$_POST['compare_price']), max(0, (int)$_POST['stock'])]);
        flash('success', 'Product added.');
    }
    redirect('products.php');
}
$search = trim($_GET['q'] ?? '');
$stmt = db()->prepare('SELECT p.*,c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.name LIKE ? OR c.name LIKE ? ORDER BY p.created_at DESC');
$term = '%' . $search . '%';
$stmt->execute([$term, $term]);
$items = $stmt->fetchAll();
$cats = categories();
$editProduct = null;
if (!empty($_GET['edit'])) {
    $editStmt = db()->prepare('SELECT * FROM products WHERE id=?');
    $editStmt->execute([(int)$_GET['edit']]);
    $editProduct = $editStmt->fetch() ?: null;
}
$pageTitle = 'Manage products';
require '../includes/header.php'; ?><div class="admin-nav">
    <div class="container"><strong>ShopSphere / Admin</strong>
        <nav><a href="index.php">Overview</a><a href="products.php">Products</a><a href="categories.php">Categories</a><a href="orders.php">Orders</a><a href="returns.php">Returns</a><a href="users.php">Users</a><a href="logout.php">Sign out</a></nav>
    </div>
</div>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Catalog</span>
                <h1>Products.</h1>
            </div>
        </div>
        <form class="filters" method="get"><input name="q" placeholder="Search products or categories" value="<?= e($search) ?>"><button class="button button-outline">Search catalog</button><a class="button button-outline" href="products.php">Clear</a></form>
        <form method="post" enctype="multipart/form-data" class="form-panel" style="margin-bottom:28px"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $editProduct['id'] ?? '' ?>"><input type="hidden" name="existing_image" value="<?= e($editProduct['image_url'] ?? '') ?>">
            <h2><?= $editProduct ? 'Edit product' : 'Add product' ?></h2>
            <div class="form-grid">
                <div class="field"><label>Name</label><input name="name" required value="<?= e($editProduct['name'] ?? '') ?>"></div>
                <div class="field"><label>Category</label><select name="category_id"><?php foreach ($cats as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div>
                <div class="field full"><label>Description</label><textarea name="description" required><?= e($editProduct['description'] ?? '') ?></textarea></div>
                <div class="field full"><label>Product image</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= $editProduct ? '' : 'required' ?>><small class="product-meta">Upload JPG, PNG, or WebP up to 5 MB.<?= $editProduct ? ' Leave empty to keep the current image.' : '' ?></small></div>
                <div class="field"><label>Price</label><input type="number" step=".01" name="price" required value="<?= e((string)($editProduct['price'] ?? '')) ?>"></div>
                <div class="field"><label>Compare price</label><input type="number" step=".01" name="compare_price" value="<?= e((string)($editProduct['compare_price'] ?? '')) ?>"></div>
                <div class="field"><label>Stock</label><input type="number" name="stock" required value="<?= e((string)($editProduct['stock'] ?? '')) ?>"></div>
            </div><button class="button button-coral" style="margin-top:18px"><?= $editProduct ? 'Save product' : 'Add product' ?></button><?php if ($editProduct): ?> <a class="button button-outline" href="products.php">Cancel</a><?php endif; ?>
        </form>
        <div class="form-panel responsive-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody><?php foreach ($items as $item): ?><tr>
                            <td><?= e($item['name']) ?></td>
                            <td><?= e($item['category_name']) ?></td>
                            <td>$<?= number_format($item['price'], 2) ?></td>
                            <td><span class="stock-pill <?= $item['stock'] < 10 ? 'low' : '' ?>"><?= $item['stock'] ?></span></td>
                            <td><span class="product-meta"><?= $item['is_active'] ? 'Live' : 'Hidden' ?></span> <a class="button button-outline admin-action" href="products.php?edit=<?= $item['id'] ?>">Edit</a> <a class="button button-outline admin-action" href="products.php?toggle=<?= $item['id'] ?>"><?= $item['is_active'] ? 'Hide' : 'Show' ?></a> <a class="button button-coral admin-action" data-confirm="Delete this product?" href="products.php?delete=<?= $item['id'] ?>">Delete</a></td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</section><?php require '../includes/footer.php'; ?>