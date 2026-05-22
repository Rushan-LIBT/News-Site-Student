<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'dashboard';
$page_title = 'Dashboard';

$stats = [
    'articles'   => (int)db()->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'published'  => (int)db()->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn(),
    'users'      => (int)db()->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'comments'   => (int)db()->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    'pending'    => (int)db()->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn(),
    'messages'   => (int)db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    'categories' => (int)db()->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'views'      => (int)db()->query("SELECT COALESCE(SUM(views),0) FROM articles")->fetchColumn(),
];

$recent_articles = db()->query("
    SELECT a.id, a.title, a.status, a.views, a.created_at, u.name AS author, c.name AS cat_name
    FROM articles a
    JOIN users u ON a.author_id = u.id
    JOIN categories c ON a.category_id = c.id
    ORDER BY a.created_at DESC LIMIT 5
")->fetchAll();

$recent_comments = db()->query("
    SELECT c.id, c.comment, c.status, c.created_at, u.name AS user_name, a.title AS article_title
    FROM comments c JOIN users u ON c.user_id = u.id JOIN articles a ON c.article_id = a.id
    ORDER BY c.created_at DESC LIMIT 5
")->fetchAll();

include __DIR__ . '/_layout_top.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3"><div class="stat-card blue">
        <div class="label">Total Articles</div>
        <div class="value"><?= $stats['articles'] ?></div>
        <div class="small text-muted mt-2"><?= $stats['published'] ?> published</div>
    </div></div>
    <div class="col-md-3"><div class="stat-card green">
        <div class="label">Registered Users</div>
        <div class="value"><?= $stats['users'] ?></div>
        <div class="small text-muted mt-2">Active accounts</div>
    </div></div>
    <div class="col-md-3"><div class="stat-card orange">
        <div class="label">Total Comments</div>
        <div class="value"><?= $stats['comments'] ?></div>
        <div class="small text-muted mt-2"><?= $stats['pending'] ?> pending</div>
    </div></div>
    <div class="col-md-3"><div class="stat-card purple">
        <div class="label">Total Views</div>
        <div class="value"><?= number_format($stats['views']) ?></div>
        <div class="small text-muted mt-2">Across all articles</div>
    </div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-table">
            <table class="table mb-0">
                <thead><tr><th colspan="5">Recent Articles</th></tr></thead>
                <tbody>
                <?php foreach ($recent_articles as $a): ?>
                    <tr>
                        <td>
                            <strong><?= e(truncate($a['title'], 50)) ?></strong>
                            <div class="small text-muted"><?= e($a['cat_name']) ?> · <?= e($a['author']) ?></div>
                        </td>
                        <td>
                            <span class="pill <?= $a['status'] === 'published' ? 'pill-success' : 'pill-warning' ?>"><?= e($a['status']) ?></span>
                        </td>
                        <td class="small text-muted"><i class="far fa-eye"></i> <?= $a['views'] ?></td>
                        <td class="small text-muted"><?= time_ago($a['created_at']) ?></td>
                        <td><a href="articles.php?action=edit&id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_articles)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No articles yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="admin-table">
            <table class="table mb-0">
                <thead><tr><th colspan="2">Recent Comments</th></tr></thead>
                <tbody>
                <?php foreach ($recent_comments as $c): ?>
                    <tr>
                        <td>
                            <div><strong><?= e($c['user_name']) ?></strong> <span class="pill <?= $c['status'] === 'approved' ? 'pill-success' : ($c['status'] === 'pending' ? 'pill-warning' : 'pill-danger') ?>"><?= e($c['status']) ?></span></div>
                            <div class="small text-muted"><?= e(truncate($c['comment'], 80)) ?></div>
                            <div class="small text-muted">on <em><?= e(truncate($c['article_title'], 40)) ?></em> · <?= time_ago($c['created_at']) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_comments)): ?>
                    <tr><td class="text-center text-muted py-3">No comments yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3"><a href="articles.php?action=new" class="btn btn-accent w-100"><i class="fas fa-plus"></i> New Article</a></div>
    <div class="col-md-3"><a href="categories.php" class="btn btn-outline-primary w-100"><i class="fas fa-folder"></i> Manage Categories</a></div>
    <div class="col-md-3"><a href="comments.php?status=pending" class="btn btn-outline-warning w-100"><i class="fas fa-clock"></i> Pending Comments (<?= $stats['pending'] ?>)</a></div>
    <div class="col-md-3"><a href="messages.php" class="btn btn-outline-secondary w-100"><i class="fas fa-envelope"></i> Unread Messages (<?= $stats['messages'] ?>)</a></div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
