<?php require '../includes/functions.php';
if (is_logged_in()) redirect('../index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE email=?');
    $stmt->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        redirect('../index.php');
    }
    flash('error', 'Email or password not recognised.');
}
$pageTitle = 'Sign in';
require '../includes/header.php'; ?><section class="section">
    <div class="container auth-wrap"><span class="eyebrow">Welcome back</span>
        <h1>Sign in to ShopSphere.</h1>
        <p>See your orders, save your favorites, and pick up where you left off.</p>
        <form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="field"><label>Email</label><input type="email" name="email" required autocomplete="email"></div>
            <div class="field" style="margin-top:15px"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button class="button button-dark" style="width:100%;margin-top:22px">Sign in</button>
            <p style="text-align:center">New here? <a class="text-link" href="register.php">Create an account</a></p>
            <label>Test credentials: <br>Email: demo@shopsphere.test<br>Password: Demo1234!</label>
        </form>
    </div>
</section><?php require '../includes/footer.php'; ?>