<?php $base = $base ?? ((str_contains($_SERVER['PHP_SELF'] ?? '', '/pages/') || str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/')) ? '../' : ''); ?></main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div><a class="brand footer-brand" href="<?= $base ?>index.php"><span class="brand-mark">S</span><span>ShopSphere</span></a>
            <p>Thoughtful products for a life well lived.</p>
            <p><a href="https://e-commerce-platform.developwithnikhil.com" target="_blank"> Click for Customer Portal</a></p>
            <p><a href="https://e-commerce-platform.developwithnikhil.com/admin" target="_blank"> Click for Admin Portal</a></p>
            <p class="demo-note">Demo website developed by Nikhil Patil · <a href="https://developwithnikhil.com" target="_blank">developwithnikhil.com</a></p>
        </div>
        <div>
            <h4>Explore</h4><a href="<?= $base ?>pages/products.php">All products</a><a href="<?= $base ?>pages/products.php?sort=selling">Best sellers</a><a href="<?= $base ?>pages/products.php?sort=price_asc">Under $50</a>
        </div>
        <div>
            <h4>Support</h4><a href="<?= $base ?>pages/orders.php">Order tracking</a><a href="<?= $base ?>pages/profile.php">My account</a><a href="mailto:hello@shopsphere.test">Contact us</a>
        </div>
        <div>
            <h4>Stay in the loop</h4>
            <p>New arrivals and considered offers, occasionally.</p>
            <form class="newsletter" action="<?= $base ?>api/newsletter.php" method="post"><input type="email" name="email" placeholder="Email address" required><button class="button button-dark">Join</button></form>
        </div>
    </div>
    <div class="container footer-bottom"><span>© <?= date('Y') ?> Nikhil Patil | developwithnikhil.com</span><span>Built with care · Secure checkout</span></div>
</footer>
<script src="<?= $base ?>assets/js/app.js?v=2"></script>
</body>

</html>