<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'articles';

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// =========================================================
// DELETE
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
    } else {
        $del_id = (int)$_POST['id'];
        // Fetch image filename first so we can remove it after the row is gone
        $imgStmt = db()->prepare("SELECT image FROM articles WHERE id = :id");
        $imgStmt->execute([':id' => $del_id]);
        $old_image = $imgStmt->fetchColumn();

        $stmt = db()->prepare("DELETE FROM articles WHERE id = :id");
        $stmt->execute([':id' => $del_id]);

        if ($old_image) delete_uploaded_image($old_image);
        flash('success', 'Article deleted.');
    }
    redirect(ADMIN_URL . '/articles.php');
}

// =========================================================
// CREATE / UPDATE
// =========================================================
$errors = [];
$article = [
    'id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'content' => '',
    'image' => '', 'category_id' => 0, 'status' => 'draft', 'is_featured' => 0,
];

if ($action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('error', 'Article not found.');
        redirect(ADMIN_URL . '/articles.php');
    }
    $article = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['_action'] ?? ''), ['create', 'update'])) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid token.';
    }
    $article['title']       = trim($_POST['title'] ?? '');
    $article['slug']        = trim($_POST['slug'] ?? '');
    $article['excerpt']     = trim($_POST['excerpt'] ?? '');
    $article['content']     = trim($_POST['content'] ?? '');
    $article['category_id'] = (int)($_POST['category_id'] ?? 0);
    $article['status']      = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $article['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $article['id']          = (int)($_POST['id'] ?? 0);

    if (strlen($article['title']) < 3) $errors[] = 'Title required (min 3 chars).';
    if (strlen($article['content']) < 10) $errors[] = 'Content required (min 10 chars).';
    if (!$article['category_id']) $errors[] = 'Category required.';

    // Slug
    if (empty($article['slug'])) {
        $article['slug'] = unique_slug($article['title'], 'articles', $article['id'] ?: null);
    } else {
        $article['slug'] = slugify($article['slug']);
    }

    // Image upload
    $new_image = null;
    $upload_err = null;
    if (!empty($_FILES['image']['name'])) {
        $new_image = upload_image($_FILES['image'], 'article', $upload_err);
        if ($new_image === false) {
            $errors[] = 'Image upload failed: ' . $upload_err;
        }
    }

    if (empty($errors)) {
        // If a new image was uploaded successfully, delete the previous one
        if ($new_image && !empty($article['image']) && $new_image !== $article['image']) {
            delete_uploaded_image($article['image']);
        }
        $image_to_save = $new_image ?: $article['image'];

        if ($action === 'edit' && $article['id']) {
            $stmt = db()->prepare("
                UPDATE articles SET title=:t, slug=:s, excerpt=:e, content=:c,
                       image=:img, category_id=:cat, status=:st, is_featured=:f
                WHERE id=:id
            ");
            $stmt->execute([
                ':t' => $article['title'], ':s' => $article['slug'],
                ':e' => $article['excerpt'], ':c' => $article['content'],
                ':img' => $image_to_save, ':cat' => $article['category_id'],
                ':st' => $article['status'], ':f' => $article['is_featured'],
                ':id' => $article['id'],
            ]);
            flash('success', 'Article updated.');
        } else {
            $stmt = db()->prepare("
                INSERT INTO articles (title, slug, excerpt, content, image, category_id, author_id, status, is_featured)
                VALUES (:t, :s, :e, :c, :img, :cat, :auth, :st, :f)
            ");
            $stmt->execute([
                ':t' => $article['title'], ':s' => $article['slug'],
                ':e' => $article['excerpt'], ':c' => $article['content'],
                ':img' => $image_to_save, ':cat' => $article['category_id'],
                ':auth' => current_user()['id'],
                ':st' => $article['status'], ':f' => $article['is_featured'],
            ]);
            flash('success', 'Article created.');
        }
        redirect(ADMIN_URL . '/articles.php');
    }
}

$categories = db()->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// =========================================================
// LIST
// =========================================================
if ($action === 'list') {
    $page_title = 'Manage Articles';

    $filter_cat = (int)($_GET['cat'] ?? 0);
    $filter_status = $_GET['status'] ?? '';
    $search = trim($_GET['q'] ?? '');

    $where = ['1=1'];
    $params = [];
    if ($filter_cat) { $where[] = 'a.category_id = :cat'; $params[':cat'] = $filter_cat; }
    if (in_array($filter_status, ['draft','published'])) {
        $where[] = 'a.status = :st'; $params[':st'] = $filter_status;
    }
    if ($search !== '') {
        $where[] = 'a.title LIKE :q';
        $params[':q'] = '%' . $search . '%';
    }
    $where_sql = implode(' AND ', $where);

    $stmt = db()->prepare("
        SELECT a.*, c.name AS cat_name, u.name AS author_name
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.author_id = u.id
        WHERE $where_sql
        ORDER BY a.created_at DESC
    ");
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    include __DIR__ . '/_layout_top.php';
?>
    <div class="d-flex justify-content-between mb-3">
        <form method="get" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" class="form-control" placeholder="Search title..." value="<?= e($search) ?>" style="width:220px;">
            <select name="cat" class="form-select" style="width:180px;">
                <option value="0">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filter_cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select" style="width:150px;">
                <option value="">Any status</option>
                <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Draft</option>
            </select>
            <button class="btn btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <a href="?action=new" class="btn btn-accent"><i class="fas fa-plus"></i> New Article</a>
    </div>

    <div class="admin-table">
        <table class="table mb-0">
            <thead>
            <tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Views</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($articles as $a): ?>
                <tr>
                    <td>
                        <?php if ($a['is_featured']): ?><i class="fas fa-star text-warning" title="Featured"></i> <?php endif; ?>
                        <strong><?= e(truncate($a['title'], 60)) ?></strong>
                    </td>
                    <td><?= e($a['cat_name']) ?></td>
                    <td><?= e($a['author_name']) ?></td>
                    <td><span class="pill <?= $a['status'] === 'published' ? 'pill-success' : 'pill-warning' ?>"><?= e($a['status']) ?></span></td>
                    <td><?= number_format($a['views']) ?></td>
                    <td class="small"><?= format_date($a['created_at']) ?></td>
                    <td>
                        <a href="<?= SITE_URL ?>/article.php?slug=<?= urlencode($a['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                        <a href="?action=edit&id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form method="post" class="d-inline" data-confirm="Delete this article? This cannot be undone.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($articles)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No articles found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
    include __DIR__ . '/_layout_bottom.php';
    exit;
}

// =========================================================
// FORM (new / edit)
// =========================================================
$page_title = $action === 'edit' ? 'Edit Article' : 'New Article';
$upload_status = uploads_dir_status();
include __DIR__ . '/_layout_top.php';
?>

<?php if (!$upload_status['ok']): ?>
    <div class="alert alert-warning">
        <strong><i class="fas fa-exclamation-triangle"></i> Uploads folder problem:</strong>
        <?= e($upload_status['msg']) ?>
        <div class="small mt-1">Articles can still be saved without an image, but uploads will fail until this is fixed.</div>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-card">
    <?= csrf_field() ?>
    <input type="hidden" name="_action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= (int)$article['id'] ?>">

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Title *</label>
            <input id="titleInput" type="text" name="title" class="form-control form-control-lg" value="<?= e($article['title']) ?>" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Slug (URL)</label>
            <input id="slugInput" type="text" name="slug" class="form-control" value="<?= e($article['slug']) ?>" placeholder="Auto-generated from title">
        </div>
        <div class="col-md-4">
            <label class="form-label">Category *</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$article['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Excerpt (short summary)</label>
            <textarea name="excerpt" class="form-control" rows="2" maxlength="500"><?= e($article['excerpt']) ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Content * <span class="text-muted small">(HTML allowed — use &lt;p&gt;, &lt;strong&gt;, etc.)</span></label>
            <textarea name="content" class="form-control" rows="12" required><?= e($article['content']) ?></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Featured Image</label>
            <input id="imageInput" type="file" name="image" class="form-control" accept="image/*">
            <?php if ($article['image']): ?>
                <div class="small text-muted mt-1">Current: <?= e($article['image']) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <img id="imagePreview" src="<?= $article['image'] ? e(article_image_url($article['image'])) : '' ?>" style="max-height:120px;<?= $article['image'] ? '' : 'display:none;' ?>" class="img-thumbnail">
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="draft"     <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_featured" id="featured" <?= $article['is_featured'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="featured">Featured on homepage</label>
            </div>
        </div>
        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-accent btn-lg">
                <i class="fas fa-save"></i> <?= $action === 'edit' ? 'Update Article' : 'Create Article' ?>
            </button>
            <a href="articles.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>
    </div>
</form>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
