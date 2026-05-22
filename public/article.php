<?php
require_once __DIR__ . '/../includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) redirect(SITE_URL . '/index.php');

$stmt = db()->prepare("
    SELECT a.*, c.name AS cat_name, c.slug AS cat_slug, u.name AS author_name, u.bio AS author_bio
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    WHERE a.slug = :slug AND a.status = 'published'
    LIMIT 1
");
$stmt->execute([':slug' => $slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $page_title = 'Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Article not found</h2><p><a href="index.php" class="btn btn-accent">Back to Home</a></p></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Increment view counter
db()->prepare("UPDATE articles SET views = views + 1 WHERE id = :id")
    ->execute([':id' => $article['id']]);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!is_logged_in()) {
        flash('error', 'You must be logged in to comment.');
        redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid request.');
    } else {
        $comment_text = trim($_POST['comment']);
        if (strlen($comment_text) < 2 || strlen($comment_text) > 1000) {
            flash('error', 'Comment must be 2–1000 characters.');
        } else {
            $stmt = db()->prepare("INSERT INTO comments (article_id, user_id, comment, status) VALUES (:aid, :uid, :c, 'pending')");
            $stmt->execute([
                ':aid' => $article['id'],
                ':uid' => current_user()['id'],
                ':c'   => $comment_text,
            ]);
            flash('success', 'Your comment has been submitted and is awaiting approval.');
        }
        redirect(SITE_URL . '/article.php?slug=' . urlencode($slug) . '#comments');
    }
}

// Fetch approved comments
$stmt = db()->prepare("
    SELECT c.*, u.name AS user_name FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.article_id = :aid AND c.status = 'approved'
    ORDER BY c.created_at DESC
");
$stmt->execute([':aid' => $article['id']]);
$comments = $stmt->fetchAll();

// Related articles (same category)
$stmt = db()->prepare("
    SELECT id, title, slug, image, created_at FROM articles
    WHERE category_id = :cid AND id != :id AND status = 'published'
    ORDER BY created_at DESC LIMIT 3
");
$stmt->execute([':cid' => $article['category_id'], ':id' => $article['id']]);
$related = $stmt->fetchAll();

$page_title = $article['title'];
$active_nav = 'cat-' . $article['cat_slug'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <article class="col-lg-8">
            <a href="category.php?slug=<?= urlencode($article['cat_slug']) ?>" class="cat-badge"><?= e($article['cat_name']) ?></a>
            <h1 class="article-title"><?= e($article['title']) ?></h1>

            <div class="article-meta d-flex justify-content-between flex-wrap">
                <div>
                    <i class="far fa-user"></i> By <strong><?= e($article['author_name']) ?></strong>
                    &nbsp;|&nbsp;
                    <i class="far fa-calendar"></i> <?= format_date($article['created_at'], 'F j, Y') ?>
                    &nbsp;|&nbsp;
                    <i class="far fa-eye"></i> <?= number_format($article['views']) ?> views
                </div>
                <div>
                    <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="me-2"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="me-2"><i class="fas fa-link"></i></a>
                </div>
            </div>

            <div class="article-hero">
                <img src="<?= e(article_image_url($article['image'])) ?>" alt="<?= e($article['title']) ?>">
            </div>

            <div class="article-content">
                <?= $article['content'] /* trusted: written by admin in admin panel */ ?>
            </div>

            <!-- Author bio -->
            <div class="sidebar-widget mt-4">
                <div class="d-flex">
                    <i class="fas fa-user-circle fa-4x text-accent me-3"></i>
                    <div>
                        <h5 class="mb-1"><?= e($article['author_name']) ?></h5>
                        <p class="text-muted mb-0"><?= e($article['author_bio'] ?? 'NewsLanka Contributor') ?></p>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <h3 class="section-title" id="comments">
                Comments (<?= count($comments) ?>)
            </h3>

            <?php if (is_logged_in()): ?>
            <form method="post" class="mb-4">
                <?= csrf_field() ?>
                <div class="mb-2">
                    <label class="form-label">Leave a comment as <strong><?= e(current_user()['name']) ?></strong></label>
                    <textarea name="comment" id="commentField" class="form-control" rows="4" placeholder="Share your thoughts..." required minlength="2" maxlength="1000"></textarea>
                    <div class="small text-muted text-end mt-1"><span id="commentCounter">0 / 1000</span></div>
                </div>
                <button type="submit" class="btn btn-accent">Post Comment</button>
            </form>
            <?php else: ?>
                <div class="alert alert-info">
                    <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Log in</a>
                    or <a href="<?= SITE_URL ?>/register.php">register</a> to leave a comment.
                </div>
            <?php endif; ?>

            <?php if (empty($comments)): ?>
                <p class="text-muted">No comments yet. Be the first to share your thoughts!</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-box">
                        <div>
                            <span class="author"><?= e($c['user_name']) ?></span>
                            <span class="date"><?= time_ago($c['created_at']) ?></span>
                        </div>
                        <p class="mb-0 mt-2"><?= nl2br(e($c['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </article>

        <!-- Sidebar -->
        <aside class="col-lg-4">
            <div class="sidebar-widget">
                <h5><i class="fas fa-newspaper text-accent"></i> Related Articles</h5>
                <?php foreach ($related as $r): ?>
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <a href="article.php?slug=<?= urlencode($r['slug']) ?>" style="flex-shrink:0;width:80px;margin-right:12px;">
                        <img src="<?= e(article_image_url($r['image'])) ?>" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:4px;">
                    </a>
                    <div>
                        <a href="article.php?slug=<?= urlencode($r['slug']) ?>" class="fw-bold" style="font-size:0.9rem;"><?= e($r['title']) ?></a>
                        <div class="small text-muted mt-1"><?= time_ago($r['created_at']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($related)): ?>
                    <p class="small text-muted mb-0">No related articles yet.</p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
