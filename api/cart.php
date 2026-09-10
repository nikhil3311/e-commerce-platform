<?php require '../includes/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
add_to_cart((int)($_POST['product_id'] ?? 0), (int)($_POST['quantity'] ?? 1));
echo json_encode(['count' => cart_count(), 'total' => cart_total()]);
