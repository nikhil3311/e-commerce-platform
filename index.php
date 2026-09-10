<?php $pageTitle = 'Considered goods for everyday living';
require 'includes/header.php';
$featured = products(['limit' => 4]);
$trending = products(['sort' => 'selling', 'limit' => 4]);
$cats = categories(); ?>
<section class="hero">
    <div class="container hero-grid">
        <div><span class="eyebrow">The everyday edit · 2026</span>
            <h1>Good things, thoughtfully gathered.</h1>
            <p>Discover useful objects, quiet luxuries, and pieces that earn their place in your everyday.</p><a class="button button-dark" href="pages/products.php">Shop the collection →</a>
        </div>
        <div class="hero-art" aria-label="ShopSphere collection"></div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Browse by mood</span>
                <h2>Find your next favorite.</h2>
            </div><a class="text-link" href="pages/products.php">View all</a>
        </div>
        <div class="category-row"><?php $icons = ['⌂', '◈', '✦', '✿', '△'];
                                    foreach ($cats as $i => $category): ?><a class="category" href="pages/products.php?category=<?= $category['id'] ?>">
                    <div class="category-icon"><?= $icons[$i % 5] ?></div>
                    <h3><?= e($category['name']) ?></h3><small><?= e($category['description']) ?></small>
                </a><?php endforeach; ?></div>
    </div>
</section>
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Curated for you</span>
                <h2>Featured pieces</h2>
            </div><a class="text-link" href="pages/products.php">Shop all</a>
        </div>
        <div class="product-grid"><?php foreach ($featured as $product): ?><article class="product-card"><a href="pages/product.php?id=<?= $product['id'] ?>">
                        <div class="product-image"><img loading="lazy" src="<?= e(asset_url($product['image_url'])) ?>" alt="<?= e($product['name']) ?>"></div>
                        <div class="product-meta"><?= e($product['category_name']) ?> · <span class="rating">★ <?= $product['rating'] ?></span></div>
                        <h3><?= e($product['name']) ?></h3>
                        <div class="price">$<?= number_format((float)$product['price'], 2) ?> <span class="old-price">$<?= number_format((float)$product['compare_price'], 2) ?></span></div>
                    </a>
                    <?php if (is_logged_in()): ?><form method="post" action="pages/wishlist.php"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="product_id" value="<?= $product['id'] ?>"><input type="hidden" name="return_to" value="../index.php"><button class="quick-add wishlist-add" aria-label="<?= wishlist_has_product((int)$product['id']) ? 'Remove' : 'Add' ?> <?= e($product['name']) ?> <?= wishlist_has_product((int)$product['id']) ? 'from' : 'to' ?> wishlist"><?= wishlist_has_product((int)$product['id']) ? '♥' : '♡' ?></button></form><?php endif; ?>
                </article><?php endforeach; ?></div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="banner">
            <div><span class="eyebrow">The weekend edit</span>
                <h2>Make room for better habits.</h2>
                <p>Save 15% on wellness essentials with code RESET15.</p>
            </div><a class="button button-coral" href="pages/products.php?category=4">Explore wellness</a>
        </div>
    </div>
</section>
<section class="service-strip">
    <div class="container service-grid">
        <div><strong>01</strong><span><b>Free shipping</b><small>On orders over $75</small></span></div>
        <div><strong>02</strong><span><b>30-day returns</b><small>Simple, no-fuss returns</small></span></div>
        <div><strong>03</strong><span><b>Thoughtfully sourced</b><small>Better materials, fewer things</small></span></div>
        <div><strong>04</strong><span><b>Real human support</b><small>hello@shopsphere.test</small></span></div>
    </div>
</section>
<section class="section editorial">
    <div class="container editorial-grid">
        <div class="editorial-image"><span>THE QUIET<br>COLLECTION</span></div>
        <div class="editorial-copy"><span class="eyebrow">A slower way to shop</span>
            <h2>Less noise. More keepers.</h2>
            <p>We look for the pieces that make a daily ritual feel more considered: useful objects, honest materials, and design that does not ask for attention.</p>
            <p class="product-meta">Every item is reviewed for how it feels, functions, and holds up beyond the first impression.</p><a class="button button-outline" href="pages/products.php">Meet the collection →</a>
        </div>
    </div>
</section>
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Moving quickly</span>
                <h2>Community favorites</h2>
            </div><a class="text-link" href="pages/products.php?sort=selling">See best sellers</a>
        </div>
        <div class="product-grid"><?php foreach ($trending as $product): ?><article class="product-card"><a href="pages/product.php?id=<?= $product['id'] ?>">
                        <div class="product-image"><img loading="lazy" src="<?= e(asset_url($product['image_url'])) ?>" alt="<?= e($product['name']) ?>"></div>
                        <div class="product-meta"><span class="rating">★ <?= $product['rating'] ?></span> · <?= (int)$product['sold_count'] ?> sold</div>
                        <h3><?= e($product['name']) ?></h3>
                        <div class="price">$<?= number_format((float)$product['price'], 2) ?></div>
                    </a></article><?php endforeach; ?></div>
    </div>
</section>
<section class="section testimonials">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">Kind words</span>
                <h2>Good things, noticed.</h2>
            </div>
        </div>
        <div class="testimonial-grid">
            <blockquote>
                <div class="rating">★★★★★</div>
                <p>“The kind of store where every product feels like someone actually thought about it.”</p><cite>— Mira K., verified customer</cite>
            </blockquote>
            <blockquote>
                <div class="rating">★★★★★</div>
                <p>“Fast delivery, beautiful packaging, and the journal is now part of my morning.”</p><cite>— Anika R., verified customer</cite>
            </blockquote>
            <blockquote>
                <div class="rating">★★★★★</div>
                <p>“I came for one gift and left with a better desk setup. No regrets.”</p><cite>— Daniel S., verified customer</cite>
            </blockquote>
        </div>
    </div>
</section>
<?php require 'includes/footer.php'; ?>