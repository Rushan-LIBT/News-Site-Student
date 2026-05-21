<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = ARTICLES_PER_PAGE;
$offset = ($page - 1) * $per_page;

$stmt = db()->prepare("SELECT * FROM categories WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    $page_title = 'Category Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Category not found</h2><a href="index.php" class="btn btn-accent">Back to Home</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Count
$stmt = db()->prepare("SELECT COUNT(*) FROM articles WHERE category_id = :cid AND status='published'");
$stmt->execute([':cid' => $category['id']]);
$total = (int)$stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));

// Fetch articles
$stmt = db()->prepare("
    SELECT a.*, u.name AS author_name FROM articles a
    JOIN users u ON a.author_id = u.id
    WHERE a.category_id = :cid AND a.status = 'published'
    ORDER BY a.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([':cid' => $category['id']]);
$articles = $stmt->fetchAll();

$page_title = $category['name'];
$active_nav = 'cat-' . $category['slug'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
        </ol>
    </nav>

    <h1 class="section-title"><?= e($category['name']) ?> News</h1>
    <p class="text-muted"><?= e($category['description']) ?></p>

    <?php if (empty($articles)): ?>
        <div class="alert alert-info">No articles in this category yet.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="article-card">
                    <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="card-img-wrap">
                        <img src="<?= e(article_image_url($a['image'])) ?>" alt="<?= e($a['title']) ?>">
                    </a>
                    <div class="card-body">
                        <h5><a href="article.php?slug=<?= urlencode($a['slug']) ?>"><?= e($a['title']) ?></a></h5>
                        <p class="excerpt"><?= e(truncate($a['excerpt'] ?? $a['content'], 110)) ?></p>
                        <div class="meta d-flex justify-content-between">
                            <span><i class="far fa-user"></i> <?= e($a['author_name']) ?></span>
                            <span><i class="far fa-clock"></i> <?= time_ago($a['created_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?slug=<?= urlencode($slug) ?>&page=<?= $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
