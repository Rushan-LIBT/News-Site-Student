<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$admin_page = $admin_page ?? 'dashboard';
$page_title = $page_title ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — NewsLanka Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrap">

    <aside class="admin-sidebar">
        <div class="brand">
            <a href="index.php">News<span style="color:var(--admin-accent)">Lanka</span></a>
            <small>Admin Panel</small>
        </div>
        <ul>
            <li><a href="index.php"      class="<?= $admin_page === 'dashboard'  ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> <span>Dashboard</span></a></li>
            <li><a href="articles.php"   class="<?= $admin_page === 'articles'   ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> <span>Articles</span></a></li>
            <li><a href="categories.php" class="<?= $admin_page === 'categories' ? 'active' : '' ?>"><i class="fas fa-folder-open"></i> <span>Categories</span></a></li>
            <li><a href="users.php"      class="<?= $admin_page === 'users'      ? 'active' : '' ?>"><i class="fas fa-users"></i> <span>Users</span></a></li>
            <li><a href="comments.php"   class="<?= $admin_page === 'comments'   ? 'active' : '' ?>"><i class="fas fa-comments"></i> <span>Comments</span></a></li>
            <li><a href="messages.php"   class="<?= $admin_page === 'messages'   ? 'active' : '' ?>"><i class="fas fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-external-link-alt"></i> <span>View Site</span></a></li>
            <li><a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 class="page-title"><?= e($page_title) ?></h1>
            <div>
                <span class="text-muted small">Welcome,</span>
                <strong><?= e(current_user()['name']) ?></strong>
                <i class="fas fa-user-circle text-accent ms-2"></i>
            </div>
        </div>

        <div class="admin-content">

            <?php if ($fs = flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= e($fs) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($fe = flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= e($fe) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
