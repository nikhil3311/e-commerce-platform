<?php require '../includes/functions.php';
if (is_admin()) redirect('index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $stmt = db()->prepare('SELECT * FROM admins WHERE email=?');
    $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $admin;
        redirect('index.php');
    }
    flash('error', 'Admin credentials not recognised.');
}
$pageTitle = 'Admin login';
require '../includes/header.php'; ?><section class="section">
    <div style="background-color: black;color: white;padding: 10px;text-align: center;">This is Demo E-Commerce Website Project. Developed by Nikhil Patil. For Contact Visit: <a href="https://developwithnikhil.com" target="_blank" style="color: #007bff;text-decoration: underline;">developwithnikhil.com</a></div>
    <div class="container auth-wrap"><span class="eyebrow">Operations</span>
        <h1>ShopSphere admin.</h1>
        <form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="field"><label>Email</label><input type="email" name="email" required value="admin@shopsphere.test"></div>
            <div class="field" style="margin-top:15px"><label>Password</label><input type="password" name="password" required></div><button class="button button-dark" style="width:100%;margin-top:20px">Enter dashboard</button>
            <label>Password : admin123</label>
        </form>
    </div>
</section><?php require '../includes/footer.php'; ?>