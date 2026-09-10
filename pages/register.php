<?php require '../includes/functions.php';
if (is_logged_in()) redirect('../index.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== ($_POST['confirm_password'] ?? '')) {
        flash('error', 'Use a valid email and matching password of at least 8 characters.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users(name,email,phone,password_hash) VALUES(?,?,?,?)');
            $stmt->execute([$name, $email, trim($_POST['phone'] ?? ''), password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Account created. Please sign in.');
            redirect('login.php');
        } catch (PDOException $e) {
            flash('error', $e->getCode() === '23000' ? 'That email is already registered.' : 'Could not create account.');
        }
    }
}
$pageTitle = 'Create account';
require '../includes/header.php'; ?><section class="section">
    <div class="container auth-wrap"><span class="eyebrow">Join the circle</span>
        <h1>Make it yours.</h1>
        <p>Create an account for faster checkout and an easier way to keep track of good things.</p>
        <form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="field"><label>Full name</label><input name="name" required autocomplete="name"></div>
            <div class="field" style="margin-top:15px"><label>Email</label><input type="email" name="email" required autocomplete="email"></div>
            <div class="field" style="margin-top:15px"><label>Phone</label><input name="phone" autocomplete="tel"></div>
            <div class="form-grid" style="margin-top:15px">
                <div class="field"><label>Password</label><input type="password" name="password" minlength="8" required></div>
                <div class="field"><label>Confirm password</label><input type="password" name="confirm_password" minlength="8" required></div>
            </div><button class="button button-coral" style="width:100%;margin-top:22px">Create account</button>
        </form>
    </div>
</section><?php require '../includes/footer.php'; ?>