<footer class="site-footer">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-4">
                <h5 class="brand" style="color:#fff;">News<span style="color:var(--accent)">Lanka</span></h5>
                <p class="small">
                    NewsLanka is Sri Lanka's trusted source for breaking news, in-depth analysis,
                    and stories from across the island and around the world. Stay informed,
                    stay connected.
                </p>
                <div class="mt-3">
                    <a href="#" class="me-2"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-2">
                <h5>Quick Links</h5>
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                    <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h5>Categories</h5>
                <ul>
                    <?php
                    $footer_cats = db()->query("SELECT name, slug FROM categories ORDER BY name LIMIT 6")->fetchAll();
                    foreach ($footer_cats as $fc): ?>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=<?= urlencode($fc['slug']) ?>"><?= e($fc['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-md-3">
                <h5>Newsletter</h5>
                <p class="small">Subscribe to get the latest news in your inbox.</p>
                <form id="newsletterForm">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" required>
                        <button type="submit" class="btn btn-accent">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            &copy; <?= date('Y') ?> NewsLanka — Sri Lanka's Trusted News Source. All rights reserved.
            &nbsp;|&nbsp; Built for SE102.3 Web Based Application Development.
        </div>
    </div>
</footer>

<a href="#" id="backToTop" class="btn btn-accent" style="position:fixed;bottom:30px;right:30px;display:none;border-radius:50%;width:45px;height:45px;padding:0;z-index:1000;">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
