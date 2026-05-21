<?php
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Home';
$active_nav = 'home';

// Featured article
$featured = db()->query("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published' AND a.is_featured = 1
    ORDER BY a.created_at DESC LIMIT 1
")->fetch();

// Latest articles
$latest = db()->query("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published'
    ORDER BY a.created_at DESC LIMIT 6
")->fetchAll();

// Most viewed (trending)
$trending = db()->query("
    SELECT id, title, slug, views FROM articles
    WHERE status = 'published'
    ORDER BY views DESC, created_at DESC LIMIT 5
")->fetchAll();

// Articles grouped by category
$categories_with_articles = db()->query("
    SELECT id, name, slug FROM categories ORDER BY name LIMIT 4
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Breaking ticker -->
<div class="breaking-bar">
    <div class="container d-flex align-items-center">
        <span class="label"><i class="fas fa-bolt"></i> Breaking</span>
        <div class="ticker">
            Sri Lanka Cricket wins historic test series against Australia &nbsp;•&nbsp;
            New tech hub opens in Colombo &nbsp;•&nbsp;
            Parliament passes economic reform bill &nbsp;•&nbsp;
            Tourism sector records strong recovery
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Featured + Latest -->
        <div class="col-lg-8">
            <?php if ($featured): ?>
            <div class="hero-section" style="background-image:url('<?= e(article_image_url($featured['image'])) ?>');">
                <div class="hero-overlay">
                    <div>
                        <a href="category.php?slug=<?= urlencode($featured['cat_slug']) ?>" class="cat-badge"><?= e($featured['cat_name']) ?></a>
                    </div>
                    <h2><a href="article.php?slug=<?= urlencode($featured['slug']) ?>"><?= e($featured['title']) ?></a></h2>
                    <div class="hero-meta">
                        <i class="far fa-user"></i> <?= e($featured['author_name']) ?>
                        &nbsp;|&nbsp;
                        <i class="far fa-clock"></i> <?= time_ago($featured['created_at']) ?>
                        &nbsp;|&nbsp;
                        <i class="far fa-eye"></i> <?= number_format($featured['views']) ?> views
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <h3 class="section-title">Latest News</h3>
            <div class="row g-4">
                <?php foreach ($latest as $a): ?>
                <div class="col-md-6">
                    <div class="article-card">
                        <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="card-img-wrap">
                            <img src="<?= e(article_image_url($a['image'])) ?>" alt="<?= e($a['title']) ?>">
                        </a>
                        <div class="card-body">
                            <a href="category.php?slug=<?= urlencode($a['cat_slug']) ?>" class="cat-badge"><?= e($a['cat_name']) ?></a>
                            <h5><a href="article.php?slug=<?= urlencode($a['slug']) ?>"><?= e($a['title']) ?></a></h5>
                            <p class="excerpt"><?= e(truncate($a['excerpt'] ?? $a['content'], 120)) ?></p>
                            <div class="meta">
                                <i class="far fa-user"></i> <?= e($a['author_name']) ?>
                                &nbsp;•&nbsp;
                                <i class="far fa-clock"></i> <?= time_ago($a['created_at']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sidebar-widget">
                <h5><i class="fas fa-fire text-accent"></i> Trending Now</h5>
                <ul class="trending-list">
                    <?php foreach ($trending as $i => $t): ?>
                        <li>
                            <span class="num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                            <div>
                                <a href="article.php?slug=<?= urlencode($t['slug']) ?>"><?= e($t['title']) ?></a>
                                <div class="small text-muted mt-1"><i class="far fa-eye"></i> <?= number_format($t['views']) ?> views</div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-widget">
                <h5><i class="fas fa-th-list text-accent"></i> Categories</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $all_cats = db()->query("
                        SELECT c.name, c.slug, COUNT(a.id) cnt
                        FROM categories c
                        LEFT JOIN articles a ON a.category_id = c.id AND a.status='published'
                        GROUP BY c.id ORDER BY c.name
                    ")->fetchAll();
                    foreach ($all_cats as $cat): ?>
                        <a href="category.php?slug=<?= urlencode($cat['slug']) ?>" class="btn btn-outline-accent btn-sm">
                            <?= e($cat['name']) ?> <span class="badge bg-light text-dark ms-1"><?= $cat['cnt'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sidebar-widget text-center">
                <h5><i class="fas fa-envelope text-accent"></i> Stay Updated</h5>
                <p class="small text-muted">Get the latest news delivered to your inbox daily.</p>
                <form>
                    <input type="email" class="form-control mb-2" placeholder="Your email" required>
                    <button type="submit" class="btn btn-accent w-100">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Category sections -->
    <?php foreach ($categories_with_articles as $cat):
        $stmt = db()->prepare("
            SELECT a.*, u.name AS author_name FROM articles a
            JOIN users u ON a.author_id = u.id
            WHERE a.category_id = :cid AND a.status = 'published'
            ORDER BY a.created_at DESC LIMIT 3
        ");
        $stmt->execute([':cid' => $cat['id']]);
        $cat_articles = $stmt->fetchAll();
        if (empty($cat_articles)) continue;
    ?>
    <h3 class="section-title d-flex justify-content-between align-items-center">
        <span><?= e($cat['name']) ?></span>
        <a href="category.php?slug=<?= urlencode($cat['slug']) ?>" class="btn btn-outline-accent btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </h3>
    <div class="row g-4">
        <?php foreach ($cat_articles as $a): ?>
        <div class="col-md-4">
            <div class="article-card">
                <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="card-img-wrap">
                    <img src="<?= e(article_image_url($a['image'])) ?>" alt="<?= e($a['title']) ?>">
                </a>
                <div class="card-body">
                    <h5><a href="article.php?slug=<?= urlencode($a['slug']) ?>"><?= e($a['title']) ?></a></h5>
                    <p class="excerpt"><?= e(truncate($a['excerpt'] ?? $a['content'], 100)) ?></p>
                    <div class="meta">
                        <i class="far fa-clock"></i> <?= time_ago($a['created_at']) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
