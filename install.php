<?php
/**
 * NewsLanka Installation Script
 *
 * Run this once to set up the database with correctly hashed passwords.
 * Access via: http://localhost/News-Site-Student/install.php
 *
 * IMPORTANT: Delete this file after installation in production.
 */

require_once __DIR__ . '/includes/config.php';

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to MySQL server (no DB selected yet)
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Load and execute schema
        $sql = file_get_contents(__DIR__ . '/database/newslanka.sql');
        $pdo->exec($sql);
        $messages[] = "Database '" . DB_NAME . "' created with schema and seed data.";

        // Re-hash admin and user passwords with current PHP environment
        $pdo->exec("USE " . DB_NAME);

        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $userHash  = password_hash('user123',  PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("UPDATE users SET password = :pw WHERE email = :email");
        $stmt->execute([':pw' => $adminHash, ':email' => 'admin@newslanka.lk']);
        $stmt->execute([':pw' => $userHash,  ':email' => 'saman@newslanka.lk']);

        $messages[] = "Admin password set: admin@newslanka.lk / admin123";
        $messages[] = "User password set: saman@newslanka.lk / user123";
        $messages[] = "Installation complete! You can now <a href='public/index.php'>visit the site</a> or <a href='admin/index.php'>log in to admin</a>.";

    } catch (PDOException $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install NewsLanka</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h3 mb-3">NewsLanka Installer</h1>
                    <p class="text-muted">This will create the <code><?= DB_NAME ?></code> database, set up all tables and insert seed data.</p>

                    <?php foreach ($messages as $m): ?>
                        <div class="alert alert-success"><?= $m ?></div>
                    <?php endforeach; ?>
                    <?php foreach ($errors as $err): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>

                    <?php if (empty($messages)): ?>
                    <form method="post">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> Running this will <strong>drop and recreate</strong> the <code><?= DB_NAME ?></code> database. All existing data will be lost.
                        </div>
                        <button type="submit" class="btn btn-primary">Run Installation</button>
                    </form>
                    <?php endif; ?>

                    <hr>
                    <h6>Default credentials after install</h6>
                    <ul class="small text-muted mb-0">
                        <li>Admin: <code>admin@newslanka.lk</code> / <code>admin123</code></li>
                        <li>User: <code>saman@newslanka.lk</code> / <code>user123</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
