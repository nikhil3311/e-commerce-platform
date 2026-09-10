<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
function asset_url(string $path, string $base = ''): string
{
    return preg_match('#^https?://#i', $path) ? $path : $base . ltrim($path, '/');
}
function profile_image_src(?array $user = null, string $base = ''): ?string
{
    $user ??= current_user();
    if (!$user || empty($user['id'])) return null;
    $stmt = db()->prepare('SELECT profile_image_data, profile_image_mime, profile_image FROM users WHERE id=?');
    $stmt->execute([(int)$user['id']]);
    $image = $stmt->fetch();
    if (!$image) return null;
    if (!empty($image['profile_image_data'])) return 'data:' . $image['profile_image_mime'] . ';base64,' . base64_encode($image['profile_image_data']);
    return !empty($image['profile_image']) ? asset_url($image['profile_image'], $base) : null;
}
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = compact('type', 'message');
}
function get_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function verify_csrf(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid request token.');
    }
}
function is_logged_in(): bool
{
    if (empty($_SESSION['user']['id'])) return false;
    $stmt = db()->prepare('SELECT 1 FROM users WHERE id=?');
    $stmt->execute([(int)$_SESSION['user']['id']]);
    if (!$stmt->fetchColumn()) {
        unset($_SESSION['user']);
        return false;
    }
    return true;
}
function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please sign in to continue.');
        redirect('login.php');
    }
}
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}
function require_admin(): void
{
    if (!is_admin()) redirect('login.php');
}

function categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}
function product_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id WHERE p.id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function products(array $filters = []): array
{
    $where = ['p.is_active=1'];
    $params = [];
    if (!empty($filters['category'])) {
        $where[] = 'p.category_id=?';
        $params[] = (int)$filters['category'];
    }
    if (!empty($filters['q'])) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?)';
        $term = '%' . $filters['q'] . '%';
        array_push($params, $term, $term, $term);
    }
    if (isset($filters['max']) && $filters['max'] !== '') {
        $where[] = 'p.price <= ?';
        $params[] = (float)$filters['max'];
    }
    $sort = match ($filters['sort'] ?? '') {
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'rating' => 'p.rating DESC',
        'selling' => 'p.sold_count DESC',
        default => 'p.created_at DESC'
    };
    $limit = max(1, min(48, (int)($filters['limit'] ?? 24)));
    $sql = 'SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $sort . ' LIMIT ' . $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function cart_items(): array
{
    if (is_logged_in()) {
        $stmt = db()->prepare('SELECT ci.quantity, p.* FROM cart_items ci JOIN carts c ON c.id=ci.cart_id JOIN products p ON p.id=ci.product_id WHERE c.user_id=?');
        $stmt->execute([current_user()['id']]);
        return $stmt->fetchAll();
    }
    $items = [];
    foreach ($_SESSION['cart'] ?? [] as $id => $quantity) {
        if ($product = product_by_id((int)$id)) {
            $product['quantity'] = $quantity;
            $items[] = $product;
        }
    }
    return $items;
}
function cart_count(): int
{
    return array_sum(array_column(cart_items(), 'quantity'));
}
function cart_has_product(int $productId): bool
{
    foreach (cart_items() as $item) {
        if ((int)$item['id'] === $productId) return true;
    }
    return false;
}
function wishlist_has_product(int $productId): bool
{
    if (!is_logged_in()) return false;
    $stmt = db()->prepare('SELECT 1 FROM wishlists WHERE user_id=? AND product_id=?');
    $stmt->execute([current_user()['id'], $productId]);
    return (bool)$stmt->fetchColumn();
}
function wishlist_count(): int
{
    if (!is_logged_in()) return 0;
    $stmt = db()->prepare('SELECT COUNT(*) FROM wishlists WHERE user_id=?');
    $stmt->execute([current_user()['id']]);
    return (int)$stmt->fetchColumn();
}
function cart_total(): float
{
    return array_sum(array_map(fn($item) => (float)$item['price'] * (int)$item['quantity'], cart_items()));
}
function applied_coupon(): ?array
{
    $code = $_SESSION['coupon_code'] ?? '';
    if (!$code) return null;
    $stmt = db()->prepare('SELECT code,discount_percent FROM coupons WHERE code=? AND is_active=1 AND expires_at>=CURDATE()');
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon) {
        unset($_SESSION['coupon_code']);
        return null;
    }
    return $coupon;
}
function apply_coupon(string $code): ?array
{
    $code = strtoupper(trim($code));
    $stmt = db()->prepare('SELECT code,discount_percent FROM coupons WHERE code=? AND is_active=1 AND expires_at>=CURDATE()');
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon && $code === 'RESET15') {
        $seed = db()->prepare('INSERT IGNORE INTO coupons(code,discount_percent,expires_at) VALUES(?,?,?)');
        $seed->execute(['RESET15', 15, '2027-12-31']);
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();
    }
    if ($coupon) $_SESSION['coupon_code'] = $coupon['code'];
    return $coupon ?: null;
}
function clear_coupon(): void
{
    unset($_SESSION['coupon_code']);
}
function add_to_cart(int $productId, int $quantity = 1): void
{
    $product = product_by_id($productId);
    if (!$product || (int)$product['stock'] < 1) return;
    $quantity = max(1, min((int)$product['stock'], $quantity));
    if (is_logged_in()) {
        $cart = db()->prepare('INSERT INTO carts(user_id) VALUES(?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)');
        $cart->execute([current_user()['id']]);
        $cartId = (int)db()->lastInsertId();
        $stmt = db()->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=LEAST(quantity+VALUES(quantity),?)');
        $stmt->execute([$cartId, $productId, $quantity, $product['stock']]);
    } else {
        $_SESSION['cart'][$productId] = min((int)$product['stock'], ($_SESSION['cart'][$productId] ?? 0) + $quantity);
    }
}
function remove_from_cart(int $productId): void
{
    if (is_logged_in()) {
        $stmt = db()->prepare('DELETE ci FROM cart_items ci JOIN carts c ON c.id=ci.cart_id WHERE c.user_id=? AND ci.product_id=?');
        $stmt->execute([current_user()['id'], $productId]);
    } else {
        unset($_SESSION['cart'][$productId]);
    }
}
function update_cart(array $quantities): void
{
    foreach ($quantities as $id => $quantity) {
        $quantity = max(0, (int)$quantity);
        if ($quantity === 0) remove_from_cart((int)$id);
        else {
            if (is_logged_in()) {
                $stmt = db()->prepare('UPDATE cart_items ci JOIN carts c ON c.id=ci.cart_id SET ci.quantity=LEAST(?,(SELECT stock FROM products WHERE id=ci.product_id)) WHERE c.user_id=? AND ci.product_id=?');
                $stmt->execute([$quantity, current_user()['id'], (int)$id]);
            } else $_SESSION['cart'][(int)$id] = $quantity;
        }
    }
}
