<?php
require_once __DIR__ . '/auth.php';
$page_title = $page_title ?? SITE_NAME;
$active_nav = $active_nav ?? '';

// Fetch categories for nav (cached per request)
$nav_categories = db()->query("SELECT id, name, slug FROM categories ORDER BY name LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(SITE_TAGLINE) ?>">
    <title><?= e($page_title) ?> — <?= e(SITE_NAME) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Top bar -->
<div class="top-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="far fa-calendar"></i> <?= date('l, F j, Y') ?>
            &nbsp;|&nbsp;
            <i class="fas fa-map-marker-alt"></i> Colombo, Sri Lanka
        </div>
        <div>
            <?php if (is_logged_in()): ?>
                <i class="fas fa-user-circle"></i> <?= e(current_user()['name']) ?>
                <?php if (is_admin()): ?>
                    &nbsp;|&nbsp;<a href="<?= ADMIN_URL ?>/index.php"><i class="fas fa-cog"></i> Admin</a>
                <?php endif; ?>
                &nbsp;|&nbsp;<a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                &nbsp;|&nbsp;
                <a href="<?= SITE_URL ?>/register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Site header / brand -->
<header class="site-header py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="<?= SITE_URL ?>/index.php" class="text-decoration-none">
            <div class="brand">News<span class="accent">Lanka</span>
                <small><?= e(SITE_TAGLINE) ?></small>
            </div>
        </a>
        <form action="<?= SITE_URL ?>/search.php" method="get" class="d-none d-md-flex" style="min-width:300px;">
            <input type="search" name="q" id="liveSearch" class="form-control" placeholder="Search news..." value="<?= e($_GET['q'] ?? '') ?>" required>
            <button type="submit" class="btn btn-accent ms-2"><i class="fas fa-search"></i></button>
        </form>
    </div>
</header>

<!-- Main navigation -->
<nav class="navbar navbar-expand-lg navbar-news">
    <div class="container">
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'home' ? 'active' : '' ?>" href="<?= SITE_URL ?>/index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <?php foreach ($nav_categories as $cat): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'cat-' . $cat['slug'] ? 'active' : '' ?>" href="<?= SITE_URL ?>/category.php?slug=<?= urlencode($cat['slug']) ?>">
                        <?= e($cat['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'about' ? 'active' : '' ?>" href="<?= SITE_URL ?>/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_nav === 'contact' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flash_success = flash('success')): ?>
<div class="container mt-3"><div class="alert alert-success alert-dismissible fade show"><?= e($flash_success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
<?php endif; ?>
<?php if ($flash_error = flash('error')): ?>
<div class="container mt-3"><div class="alert alert-danger alert-dismissible fade show"><?= e($flash_error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
<?php endif; ?>
