<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'messages';
$page_title = 'Contact Messages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
        redirect(ADMIN_URL . '/messages.php');
    }
    $mid = (int)$_POST['id'];
    $act = $_POST['_action'] ?? '';

    if ($act === 'delete') {
        db()->prepare("DELETE FROM contact_messages WHERE id=:id")->execute([':id' => $mid]);
        flash('success', 'Message deleted.');
    } elseif ($act === 'mark_read') {
        db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=:id")->execute([':id' => $mid]);
        flash('success', 'Marked as read.');
    } elseif ($act === 'mark_unread') {
        db()->prepare("UPDATE contact_messages SET is_read=0 WHERE id=:id")->execute([':id' => $mid]);
        flash('success', 'Marked as unread.');
    }
    redirect(ADMIN_URL . '/messages.php');
}

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/_layout_top.php';
?>

<div class="admin-table">
    <table class="table mb-0">
        <thead><tr><th>From</th><th>Subject</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($messages as $m): ?>
            <tr style="<?= $m['is_read'] ? '' : 'background:#fffbef;' ?>">
                <td>
                    <strong><?= e($m['name']) ?></strong>
                    <div class="small text-muted"><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></div>
                </td>
                <td><strong><?= e($m['subject']) ?></strong></td>
                <td style="max-width:380px;"><?= e(truncate($m['message'], 150)) ?></td>
                <td>
                    <?php if ($m['is_read']): ?>
                        <span class="pill pill-muted">Read</span>
                    <?php else: ?>
                        <span class="pill pill-info">New</span>
                    <?php endif; ?>
                </td>
                <td class="small text-muted"><?= time_ago($m['created_at']) ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#msg<?= $m['id'] ?>"><i class="fas fa-eye"></i></button>
                    <form method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="<?= $m['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button class="btn btn-sm btn-outline-secondary" title="Toggle read"><i class="fas fa-<?= $m['is_read'] ? 'envelope' : 'envelope-open' ?>"></i></button>
                    </form>
                    <form method="post" class="d-inline" data-confirm="Delete this message?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>

            <!-- Modal -->
            <div class="modal fade" id="msg<?= $m['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= e($m['subject']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>From:</strong> <?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt;</p>
                            <p><strong>Date:</strong> <?= format_date($m['created_at'], 'F j, Y g:i a') ?></p>
                            <hr>
                            <p style="white-space:pre-wrap;"><?= e($m['message']) ?></p>
                        </div>
                        <div class="modal-footer">
                            <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn btn-accent"><i class="fas fa-reply"></i> Reply via Email</a>
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No messages yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
