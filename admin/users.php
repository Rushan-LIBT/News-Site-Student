<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'users';
$page_title = 'Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
        redirect(ADMIN_URL . '/users.php');
    }
    $act = $_POST['_action'] ?? '';
    $uid = (int)($_POST['id'] ?? 0);

    if ($uid === current_user()['id']) {
        flash('error', 'You cannot modify your own account here.');
        redirect(ADMIN_URL . '/users.php');
    }

    if ($act === 'delete') {
        db()->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $uid]);
        flash('success', 'User deleted.');
    } elseif ($act === 'promote') {
        db()->prepare("UPDATE users SET role='admin' WHERE id = :id")->execute([':id' => $uid]);
        flash('success', 'User promoted to admin.');
    } elseif ($act === 'demote') {
        db()->prepare("UPDATE users SET role='user' WHERE id = :id")->execute([':id' => $uid]);
        flash('success', 'User demoted to regular user.');
    } elseif ($act === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            flash('error', 'Invalid input. Name, valid email, and password (min 6) required.');
        } elseif (email_exists($email)) {
            flash('error', 'Email already exists.');
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            db()->prepare("INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, :r)")
               ->execute([':n' => $name, ':e' => $email, ':p' => $hash, ':r' => $role]);
            flash('success', 'User created.');
        }
    }
    redirect(ADMIN_URL . '/users.php');
}

$users = db()->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM articles WHERE author_id = u.id) AS article_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = u.id) AS comment_count
    FROM users u ORDER BY u.created_at DESC
")->fetchAll();

include __DIR__ . '/_layout_top.php';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-table">
            <table class="table mb-0">
                <thead><tr><th>User</th><th>Role</th><th>Articles</th><th>Comments</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= e($u['name']) ?></strong>
                            <div class="small text-muted"><?= e($u['email']) ?></div>
                        </td>
                        <td>
                            <span class="pill <?= $u['role'] === 'admin' ? 'pill-danger' : 'pill-info' ?>"><?= e($u['role']) ?></span>
                        </td>
                        <td><?= $u['article_count'] ?></td>
                        <td><?= $u['comment_count'] ?></td>
                        <td class="small"><?= format_date($u['created_at']) ?></td>
                        <td>
                            <?php if ($u['id'] === current_user()['id']): ?>
                                <em class="text-muted small">(you)</em>
                            <?php else: ?>
                                <?php if ($u['role'] === 'user'): ?>
                                    <form method="post" class="d-inline" data-confirm="Promote to admin?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_action" value="promote">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-success" title="Promote to admin"><i class="fas fa-arrow-up"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" class="d-inline" data-confirm="Demote to regular user?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_action" value="demote">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-warning" title="Demote"><i class="fas fa-arrow-down"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" class="d-inline" data-confirm="Delete this user? All their articles and comments will also be deleted.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card">
            <h5 class="mb-3">Create New User</h5>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_action" value="create">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button class="btn btn-accent w-100"><i class="fas fa-user-plus"></i> Create User</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
