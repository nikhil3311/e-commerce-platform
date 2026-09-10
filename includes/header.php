<?php require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'ShopSphere';
$isAdminPage = str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/');
$base = (str_contains($_SERVER['PHP_SELF'] ?? '', '/pages/') || $isAdminPage) ? '../' : ''; ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="ShopSphere - considered products for everyday living.">
    <title><?= e($pageTitle) ?> | ShopSphere</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= str_contains($_SERVER['PHP_SELF'], '/pages/') || str_contains($_SERVER['PHP_SELF'], '/admin/') ? '../assets/css/style.css' : 'assets/css/style.css' ?>">
</head>

<body>
    <?php if (!$isAdminPage): ?><header class="site-header">
        <div style="background-color: black;color: white;padding: 10px;text-align: center;">This is Demo E-Commerce Website Project. Developed by Nikhil Patil. For Contact Visit: <a href="https://developwithnikhil.com" target="_blank" style="color: #007bff;text-decoration: underline;">developwithnikhil.com</a></div>
        <div class="container nav-wrap"><a class="brand" href="<?= $base ?>index.php"><span class="brand-mark">S</span><span>ShopSphere</span></a><button class="menu-toggle" aria-label="Toggle navigation">☰</button>
            <nav class="main-nav"><a href="<?= $base ?>index.php">Home</a><a href="<?= $base ?>pages/products.php">Shop</a><a href="<?= $base ?>pages/products.php?sort=selling">Trending</a></nav>
            <form class="search" action="<?= $base ?>pages/products.php"><input name="q" placeholder="Search products..." value="<?= e($_GET['q'] ?? '') ?>"><button aria-label="Search">⌕</button></form>
            <div class="nav-actions"><a href="<?= $base ?>pages/<?= is_logged_in() ? 'profile.php' : 'login.php' ?>" class="user-link"><?php $headerProfileImage = is_logged_in() ? profile_image_src() : null; ?><?php if ($headerProfileImage): ?><img class="header-avatar" src="<?= e($headerProfileImage) ?>" alt="Profile image"><?php else: ?>◎<?php endif; ?> <span><?= is_logged_in() ? e(explode(' ', current_user()['name'])[0]) : 'Sign in' ?></span></a><?php if (is_logged_in()): ?><a href="<?= $base ?>pages/wishlist.php" class="wishlist-link">Wishlist</a><?php endif; ?><a href="<?= $base ?>pages/cart.php" class="cart-link">Bag <b><?= cart_count() ?></b></a></div>
        </div>
    </header><?php endif; ?>
    <main>
        <?php foreach (get_flashes() as $flash): ?><div class="toast <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endforeach; ?>