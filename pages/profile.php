<?php require '../includes/functions.php';
require_login();
$editing = isset($_GET['edit']);
$editingAddress = isset($_GET['edit_address']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf($_POST['csrf'] ?? '');
    if (isset($_POST['save_address'])) {
        $addressValues = [trim($_POST['full_name'] ?? ''), trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''), trim($_POST['state'] ?? ''), trim($_POST['postal_code'] ?? ''), trim($_POST['country'] ?? '')];
        if (!in_array('', $addressValues, true)) {
            $stmt = db()->prepare('UPDATE users SET full_name=?,address=?,city=?,state=?,postal_code=?,country=? WHERE id=?');
            $stmt->execute([...$addressValues, current_user()['id']]);
            flash('success', 'Address updated.');
        } else flash('error', 'Please complete every address field.');
        redirect('profile.php');
    }
    $profileImage = profile_image_src(null, '../');
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $image = $_FILES['profile_image'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($image['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > 3 * 1024 * 1024 || !isset($extensions[$mime]) || @getimagesize($image['tmp_name']) === false) {
            flash('error', 'Please upload a JPG, PNG, or WebP image under 3 MB.');
            redirect('profile.php?edit=1');
        }
        $profileImage = null;
        $stmt = db()->prepare('UPDATE users SET profile_image=NULL, profile_image_data=?, profile_image_mime=? WHERE id=?');
        $stmt->execute([file_get_contents($image['tmp_name']), $mime, current_user()['id']]);
        $_SESSION['user']['profile_image'] = null;
    }
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($name) {
        $stmt = db()->prepare('UPDATE users SET name=?,phone=? WHERE id=?');
        $stmt->execute([$name, $phone, current_user()['id']]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['phone'] = $phone;
        flash('success', 'Profile updated.');
    }
    redirect('profile.php');
}
$pageTitle = 'Your profile';
$profileImage = profile_image_src(null, '../');
$orderStmt = db()->prepare('SELECT COUNT(*) FROM orders WHERE user_id=?');
$orderStmt->execute([current_user()['id']]);
$orderCount = (int)$orderStmt->fetchColumn();
$addressStmt = db()->prepare('SELECT full_name,address,city,state,postal_code,country FROM users WHERE id=?');
$addressStmt->execute([current_user()['id']]);
$address = $addressStmt->fetch();
$hasAddress = $address && $address['address'];
require '../includes/header.php'; ?><section class="section">
    <div class="container"><span class="eyebrow">Your account</span>
        <h1>Welcome, <?= e(explode(' ', current_user()['name'])[0]) ?>.</h1>
        <div class="checkout-layout">
            <div class="form-panel"><h2>Profile</h2><?php if ($editing): ?><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="profile-image-picker"><?php if ($profileImage): ?><img src="<?= e($profileImage) ?>" alt="Profile image"><?php else: ?><span><?= e(strtoupper(substr(current_user()['name'], 0, 1))) ?></span><?php endif; ?><div class="field"><label>Profile image</label><input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp"></div></div><div class="field"><label>Full name</label><input name="name" value="<?= e(current_user()['name']) ?>" required></div><div class="field" style="margin-top:15px"><label>Email</label><input value="<?= e(current_user()['email']) ?>" disabled></div><div class="field" style="margin-top:15px"><label>Phone</label><input name="phone" value="<?= e(current_user()['phone']) ?>"></div><button class="button button-dark" style="margin-top:20px">Save changes</button> <a class="button button-outline" href="profile.php">Cancel</a></form><?php else: ?><div class="profile-fields"><div class="profile-avatar"><?php if ($profileImage): ?><img src="<?= e($profileImage) ?>" alt="Profile image"><?php else: ?><span><?= e(strtoupper(substr(current_user()['name'], 0, 1))) ?></span><?php endif; ?></div><div class="field"><label>Full name</label><input value="<?= e(current_user()['name']) ?>" disabled></div><div class="field" style="margin-top:15px"><label>Email</label><input value="<?= e(current_user()['email']) ?>" disabled></div><div class="field" style="margin-top:15px"><label>Phone</label><input value="<?= e(current_user()['phone'] ?: 'No phone added') ?>" disabled></div></div><a class="button button-outline" style="margin-top:20px" href="profile.php?edit=1">Edit profile</a><?php endif; ?></div>
            <aside class="summary">
                <h3>Account details</h3><div class="account-stat"><span>Member since</span><strong><?= date('M Y', strtotime(current_user()['created_at'] ?? 'now')) ?></strong></div><div class="account-stat"><span>Orders placed</span><strong><?= $orderCount ?></strong></div><div class="account-stat"><span>Saved address</span><strong><?= $hasAddress ? 'Yes' : 'No' ?></strong></div><a class="button button-outline" style="width:100%;margin:15px 0 5px" href="orders.php">Track orders</a><a class="button button-outline" style="width:100%;margin:5px 0" href="password.php">Change password</a><a class="button button-outline" style="width:100%;margin:5px 0" href="products.php">Continue shopping</a><a class="button button-coral" style="width:100%;margin:5px 0" href="logout.php">Sign out</a>
            </aside>
        </div>
        <div class="account-section"><span class="eyebrow">Saved for faster checkout</span><h2>Address.</h2><?php if ($editingAddress || !$hasAddress): ?><form class="form-panel address-edit-form" method="post"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><div class="form-grid"><div class="field"><label>Full name</label><input name="full_name" value="<?= e($address['full_name'] ?? current_user()['name']) ?>" required></div><div class="field"><label>Country</label><input name="country" value="<?= e($address['country'] ?? 'India') ?>" required></div><div class="field full"><label>Address</label><input name="address" value="<?= e($address['address'] ?? '') ?>" required></div><div class="field"><label>City</label><input name="city" value="<?= e($address['city'] ?? '') ?>" required></div><div class="field"><label>State</label><input name="state" value="<?= e($address['state'] ?? '') ?>" required></div><div class="field"><label>Postal code</label><input name="postal_code" value="<?= e($address['postal_code'] ?? '') ?>" required></div></div><button class="button button-dark" name="save_address" value="1" style="margin-top:20px">Save address</button> <a class="button button-outline" href="profile.php">Cancel</a></form><?php else: ?><div class="address-card"><strong><?= e($address['full_name']) ?></strong><span><?= e($address['address']) ?></span><span><?= e($address['city']) ?>, <?= e($address['state']) ?> <?= e($address['postal_code']) ?></span><span><?= e($address['country']) ?></span><a class="text-link" href="profile.php?edit_address=1">Edit address</a></div><?php endif; ?></div>
    </div>
</section><?php require '../includes/footer.php'; ?>