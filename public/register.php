<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) redirect(SITE_URL . '/index.php');

$page_title = 'Register';
$old = ['name' => '', 'email' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']  = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm      = $_POST['confirm_password'] ?? '';

    if (!csrf_verify($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request token.';
    if (strlen($old['name']) < 2) $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($errors) && email_exists($old['email'])) $errors[] = 'An account with this email already exists.';

    if (empty($errors)) {
        if (register_user($old['name'], $old['email'], $password)) {
            attempt_login($old['email'], $password);
            flash('success', 'Welcome to NewsLanka, ' . $old['name'] . '!');
            redirect(SITE_URL . '/index.php');
        }
        $errors[] = 'Registration failed. Please try again.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-card p-4 bg-white border rounded shadow-sm">
                <h2 class="text-center mb-4">Create Account</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required minlength="2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <hr>
                <p class="text-center mb-0">
                    Already have an account? <a href="login.php" class="text-accent fw-bold">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
