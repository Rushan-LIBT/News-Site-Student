<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'categories';
$page_title = 'Categories';

$errors = [];
$edit = ['id' => 0, 'name' => '', 'slug' => '', 'description' => ''];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
        redirect(ADMIN_URL . '/categories.php');
    }

    $act = $_POST['_action'] ?? '';

    if ($act === 'delete') {
        $del_id = (int)$_POST['id'];
        $stmt = db()->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $del_id]);
        flash('success', 'Category deleted.');
        redirect(ADMIN_URL . '/categories.php');
    }

    $edit['id']          = (int)($_POST['id'] ?? 0);
    $edit['name']        = trim($_POST['name'] ?? '');
    $edit['slug']        = trim($_POST['slug'] ?? '');
    $edit['description'] = trim($_POST['description'] ?? '');

    if (strlen($edit['name']) < 2) $errors[] = 'Name required (min 2 chars).';
    if (empty($edit['slug'])) {
        $edit['slug'] = unique_slug($edit['name'], 'categories', $edit['id'] ?: null);
    } else {
        $edit['slug'] = slugify($edit['slug']);
    }

    if (empty($errors)) {
        if ($act === 'update' && $edit['id']) {
            $stmt = db()->prepare("UPDATE categories SET name=:n, slug=:s, description=:d WHERE id=:id");
            $stmt->execute([':n' => $edit['name'], ':s' => $edit['slug'], ':d' => $edit['description'], ':id' => $edit['id']]);
            flash('success', 'Category updated.');
        } else {
            $stmt = db()->prepare("INSERT INTO categories (name, slug, description) VALUES (:n, :s, :d)");
            $stmt->execute([':n' => $edit['name'], ':s' => $edit['slug'], ':d' => $edit['description']]);
            flash('success', 'Category created.');
        }
        redirect(ADMIN_URL . '/categories.php');
    }
}

// Load edit target from query string
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $found = $stmt->fetch();
    if ($found) $edit = $found;
}

$categories = db()->query("
    SELECT c.*, (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id) AS article_count
    FROM categories c ORDER BY c.name
")->fetchAll();

include __DIR__ . '/_layout_top.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="form-card">
            <h5 class="mb-3"><?= $edit['id'] ? 'Edit Category' : 'New Category' ?></h5>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?></div>
            <?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="_action" value="<?= $edit['id'] ? 'update' : 'create' ?>">
                <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($edit['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= e($edit['slug']) ?>" placeholder="Auto-generated">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"><?= e($edit['description']) ?></textarea>
                </div>
                <button class="btn btn-accent"><i class="fas fa-save"></i> <?= $edit['id'] ? 'Update' : 'Create' ?></button>
                <?php if ($edit['id']): ?>
                    <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="admin-table">
            <table class="table mb-0">
                <thead><tr><th>Name</th><th>Slug</th><th>Articles</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td>
                            <strong><?= e($c['name']) ?></strong>
                            <?php if ($c['description']): ?>
                                <div class="small text-muted"><?= e(truncate($c['description'], 80)) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><code><?= e($c['slug']) ?></code></td>
                        <td><span class="pill pill-info"><?= $c['article_count'] ?></span></td>
                        <td>
                            <a href="?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form method="post" class="d-inline" data-confirm="Delete this category? All articles in it will also be deleted.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_action" value="delete">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
