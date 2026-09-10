<?php require '../includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if (!password_verify($current, current_user()['password_hash']) || strlen($new) < 8 || $new !== ($_POST['confirm_password'] ?? '')) flash('error', 'Use your current password and a matching password of at least 8 characters.');
    else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
        $stmt->execute([$newHash, current_user()['id']]);
        $_SESSION['user']['password_hash'] = $newHash;
        flash('success', 'Password changed successfully.');
        redirect('profile.php');
    }
}
$pageTitle = 'Change password';
require '../includes/header.php'; ?><section class="section"><div class="container auth-wrap"><span class="eyebrow">Account security</span><h1>Change password.</h1><form class="form-panel" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="field"><label>Current password</label><input type="password" name="current_password" required></div><div class="field" style="margin-top:15px"><label>New password</label><input type="password" name="new_password" minlength="8" required></div><div class="field" style="margin-top:15px"><label>Confirm new password</label><input type="password" name="confirm_password" minlength="8" required></div><button class="button button-coral" style="margin-top:20px">Update password</button></form></div></section><?php require '../includes/footer.php'; ?>