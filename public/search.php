<?php
require_once __DIR__ . '/../includes/functions.php';

$q = trim($_GET['q'] ?? '');
$articles = [];

if (strlen($q) >= 2) {
    $stmt = db()->prepare("
        SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.author_id = u.id
        WHERE a.status = 'published'
        AND (a.title LIKE :q1 OR a.content LIKE :q2 OR a.excerpt LIKE :q3)
        ORDER BY a.created_at DESC
        LIMIT 30
    ");
    $like = '%' . $q . '%';
    $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
    $articles = $stmt->fetchAll();
}

$page_title = 'Search: ' . $q;
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <h1 class="section-title">Search Results</h1>
    <p class="text-muted">
        <?php if (strlen($q) < 2): ?>
            Enter at least 2 characters to search.
        <?php else: ?>
            Showing <?= count($articles) ?> result<?= count($articles) === 1 ? '' : 's' ?> for "<strong><?= e($q) ?></strong>"
        <?php endif; ?>
    </p>

    <form class="mb-4" method="get">
        <div class="input-group" style="max-width:600px;">
            <input type="search" name="q" class="form-control form-control-lg" value="<?= e($q) ?>" placeholder="Search articles..." required minlength="2">
            <button class="btn btn-accent"><i class="fas fa-search"></i> Search</button>
        </div>
    </form>

    <?php if (!empty($articles)): ?>
        <div class="row g-4">
            <?php foreach ($articles as $a): ?>
            <div class="col-md-6">
                <div class="article-card">
                    <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="card-img-wrap">
                        <img src="<?= e(article_image_url($a['image'])) ?>" alt="<?= e($a['title']) ?>">
                    </a>
                    <div class="card-body">
                        <a href="category.php?slug=<?= urlencode($a['cat_slug']) ?>" class="cat-badge"><?= e($a['cat_name']) ?></a>
                        <h5><a href="article.php?slug=<?= urlencode($a['slug']) ?>"><?= e($a['title']) ?></a></h5>
                        <p class="excerpt"><?= e(truncate($a['excerpt'] ?? $a['content'], 130)) ?></p>
                        <div class="meta"><i class="far fa-clock"></i> <?= time_ago($a['created_at']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php elseif (strlen($q) >= 2): ?>
        <div class="alert alert-warning">
            No articles found matching "<strong><?= e($q) ?></strong>". Try different keywords.
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
