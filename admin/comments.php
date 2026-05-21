<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'comments';
$page_title = 'Comments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
        redirect(ADMIN_URL . '/comments.php');
    }
    $cid = (int)$_POST['id'];
    $act = $_POST['_action'] ?? '';

    if ($act === 'approve') {
        db()->prepare("UPDATE comments SET status='approved' WHERE id=:id")->execute([':id' => $cid]);
        flash('success', 'Comment approved.');
    } elseif ($act === 'reject') {
        db()->prepare("UPDATE comments SET status='rejected' WHERE id=:id")->execute([':id' => $cid]);
        flash('success', 'Comment rejected.');
    } elseif ($act === 'delete') {
        db()->prepare("DELETE FROM comments WHERE id=:id")->execute([':id' => $cid]);
        flash('success', 'Comment deleted.');
    }
    redirect(ADMIN_URL . '/comments.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$filter = $_GET['status'] ?? '';
$where = '';
$params = [];
if (in_array($filter, ['pending','approved','rejected'])) {
    $where = "WHERE c.status = :st";
    $params[':st'] = $filter;
}

$stmt = db()->prepare("
    SELECT c.*, u.name AS user_name, u.email AS user_email, a.title AS article_title, a.slug AS article_slug
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN articles a ON c.article_id = a.id
    $where
    ORDER BY c.created_at DESC
");
$stmt->execute($params);
$comments = $stmt->fetchAll();

include __DIR__ . '/_layout_top.php';
?>

<div class="mb-3 d-flex gap-2">
    <a href="comments.php" class="btn btn-sm <?= $filter === '' ? 'btn-accent' : 'btn-outline-secondary' ?>">All</a>
    <a href="?status=pending"  class="btn btn-sm <?= $filter === 'pending'  ? 'btn-warning text-white' : 'btn-outline-warning' ?>">Pending</a>
    <a href="?status=approved" class="btn btn-sm <?= $filter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
    <a href="?status=rejected" class="btn btn-sm <?= $filter === 'rejected' ? 'btn-danger'  : 'btn-outline-danger' ?>">Rejected</a>
</div>

<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>Comment</th><th>Author</th><th>Article</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($comments as $c): ?>
            <tr>
                <td style="max-width:340px;"><?= e($c['comment']) ?></td>
                <td>
                    <strong><?= e($c['user_name']) ?></strong>
                    <div class="small text-muted"><?= e($c['user_email']) ?></div>
                </td>
                <td>
                    <a href="<?= SITE_URL ?>/article.php?slug=<?= urlencode($c['article_slug']) ?>" target="_blank">
                        <?= e(truncate($c['article_title'], 40)) ?>
                    </a>
                </td>
                <td>
                    <span class="pill <?= $c['status'] === 'approved' ? 'pill-success' : ($c['status'] === 'pending' ? 'pill-warning' : 'pill-danger') ?>"><?= e($c['status']) ?></span>
                </td>
                <td class="small text-muted"><?= time_ago($c['created_at']) ?></td>
                <td>
                    <?php if ($c['status'] !== 'approved'): ?>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_action" value="approve">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-sm btn-outline-success" title="Approve"><i class="fas fa-check"></i></button>
                        </form>
                    <?php endif; ?>
                    <?php if ($c['status'] !== 'rejected'): ?>
                        <form method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_action" value="reject">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-sm btn-outline-warning" title="Reject"><i class="fas fa-times"></i></button>
                        </form>
                    <?php endif; ?>
                    <form method="post" class="d-inline" data-confirm="Delete this comment?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($comments)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No comments found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
