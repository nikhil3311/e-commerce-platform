<?php require '../includes/functions.php';
require_login();
header('Content-Type: application/json');
$product = (int)($_POST['product_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $stmt = db()->prepare('SELECT 1 FROM wishlists WHERE user_id=? AND product_id=?');
    $stmt->execute([current_user()['id'], $product]);
    if ($stmt->fetchColumn()) {
        $stmt = db()->prepare('DELETE FROM wishlists WHERE user_id=? AND product_id=?');
        $stmt->execute([current_user()['id'], $product]);
        echo json_encode(['saved' => false, 'message' => 'Removed from your wishlist.']);
    } else {
        $stmt = db()->prepare('INSERT INTO wishlists(user_id,product_id) VALUES(?,?)');
        $stmt->execute([current_user()['id'], $product]);
        echo json_encode(['saved' => true, 'message' => 'Added to your wishlist.']);
    }
    exit;
}
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
