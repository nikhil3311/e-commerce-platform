<?php require '../includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');
verify_csrf($_POST['csrf'] ?? '');
$productId = (int)($_POST['product_id'] ?? 0);
$rating = max(1, min(5, (int)($_POST['rating'] ?? 0)));
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');
if (!$productId || !$title || !$body) {
    flash('error', 'Please complete your rating.');
    redirect('product.php?id=' . $productId);
}
$stmt = db()->prepare('SELECT id FROM reviews WHERE product_id=? AND user_id=?');
$stmt->execute([$productId, current_user()['id']]);
if ($stmt->fetchColumn()) {
    $stmt = db()->prepare('UPDATE reviews SET rating=?,title=?,body=? WHERE product_id=? AND user_id=?');
    $stmt->execute([$rating, $title, $body, $productId, current_user()['id']]);
} else {
    $stmt = db()->prepare('INSERT INTO reviews(product_id,user_id,rating,title,body) VALUES(?,?,?,?,?)');
    $stmt->execute([$productId, current_user()['id'], $rating, $title, $body]);
}
$average = db()->prepare('SELECT AVG(rating) FROM reviews WHERE product_id=?');
$average->execute([$productId]);
$update = db()->prepare('UPDATE products SET rating=? WHERE id=?');
$update->execute([round((float)$average->fetchColumn(), 1), $productId]);
flash('success', 'Thanks for sharing your rating.');
redirect('product.php?id=' . $productId);