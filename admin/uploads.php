<?php
require_once __DIR__ . '/../includes/functions.php';
$admin_page = 'media';
$page_title = 'Media / Uploads';

// Handle deletion of a file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid token.');
        redirect(ADMIN_URL . '/uploads.php');
    }
    $fname = $_POST['filename'] ?? '';
    if (delete_uploaded_image($fname)) {
        flash('success', "File deleted: $fname");
    } else {
        flash('error', "Could not delete file: $fname");
    }
    redirect(ADMIN_URL . '/uploads.php');
}

$status = uploads_dir_status();

// List files in uploads folder
$files = [];
if (is_dir(UPLOAD_DIR)) {
    foreach (scandir(UPLOAD_DIR) as $f) {
        if ($f === '.' || $f === '..' || $f === '.htaccess' || $f === '.gitkeep') continue;
        $path = UPLOAD_DIR . $f;
        if (!is_file($path)) continue;
        $files[] = [
            'name' => $f,
            'size' => filesize($path),
            'time' => filemtime($path),
            'url'  => UPLOAD_URL . $f,
        ];
    }
    // Sort newest first
    usort($files, fn($a, $b) => $b['time'] <=> $a['time']);
}

// Find which files are referenced by articles
$used_images = [];
foreach (db()->query("SELECT image FROM articles WHERE image IS NOT NULL AND image != ''") as $row) {
    $used_images[$row['image']] = true;
}

include __DIR__ . '/_layout_top.php';
?>

<!-- Status banner -->
<div class="alert <?= $status['ok'] ? 'alert-success' : 'alert-danger' ?>">
    <i class="fas fa-<?= $status['ok'] ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <strong>Uploads folder status:</strong> <?= e($status['msg']) ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stat-card blue">
        <div class="label">Total Files</div>
        <div class="value"><?= count($files) ?></div>
    </div></div>
    <div class="col-md-3"><div class="stat-card green">
        <div class="label">In Use</div>
        <div class="value"><?= count(array_filter($files, fn($f) => isset($used_images[$f['name']]))) ?></div>
        <div class="small text-muted mt-2">Linked from articles</div>
    </div></div>
    <div class="col-md-3"><div class="stat-card orange">
        <div class="label">Orphaned</div>
        <div class="value"><?= count(array_filter($files, fn($f) => !isset($used_images[$f['name']]))) ?></div>
        <div class="small text-muted mt-2">Safe to delete</div>
    </div></div>
    <div class="col-md-3"><div class="stat-card purple">
        <div class="label">Total Size</div>
        <div class="value" style="font-size:1.6rem;"><?= number_format(array_sum(array_column($files, 'size')) / 1024 / 1024, 2) ?> MB</div>
    </div></div>
</div>

<?php if (empty($files)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No files uploaded yet. Create or edit an article with an image to upload your first file.
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($files as $f):
            $in_use = isset($used_images[$f['name']]);
        ?>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <div style="aspect-ratio:4/3;overflow:hidden;background:#f4f6f9;">
                        <img src="<?= e($f['url']) ?>" alt="<?= e($f['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <div class="card-body p-2">
                        <div class="small text-truncate" title="<?= e($f['name']) ?>"><strong><?= e($f['name']) ?></strong></div>
                        <div class="small text-muted">
                            <?= number_format($f['size'] / 1024, 1) ?> KB · <?= date('M d, H:i', $f['time']) ?>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <?php if ($in_use): ?>
                                <span class="pill pill-success">In use</span>
                            <?php else: ?>
                                <span class="pill pill-muted">Unused</span>
                            <?php endif; ?>
                            <form method="post" class="d-inline" data-confirm="Delete this file? <?= $in_use ? 'It is still referenced by an article!' : '' ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="filename" value="<?= e($f['name']) ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mt-4 small text-muted">
    <strong>Folder path:</strong> <code><?= e(UPLOAD_DIR) ?></code>
</div>

<?php include __DIR__ . '/_layout_bottom.php'; ?>
