<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(is_admin() ? ADMIN_URL . '/index.php' : SITE_URL . '/index.php');
}

$page_title = 'Login';
$old_email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_email = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }
    if (!filter_var($old_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (empty($password)) $errors[] = 'Password required.';

    if (empty($errors)) {
        if (attempt_login($old_email, $password)) {
            flash('success', 'Welcome back, ' . current_user()['name'] . '!');
            $redirect = $_GET['redirect'] ?? (is_admin() ? ADMIN_URL . '/index.php' : SITE_URL . '/index.php');
            redirect($redirect);
        }
        $errors[] = 'Invalid email or password.';
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card p-4 bg-white border rounded shadow-sm">
                <h2 class="text-center mb-4">Login to NewsLanka</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($old_email) ?>" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">
                        <i class="fas fa-sign-in-alt"></i> Log In
                    </button>
                </form>

                <hr>
                <p class="text-center mb-0">
                    No account? <a href="register.php" class="text-accent fw-bold">Register here</a>
                </p>
                <div class="alert alert-light border mt-3 small mb-0">
                    <strong>Demo credentials:</strong><br>
                    Admin: admin@newslanka.lk / admin123<br>
                    User: saman@newslanka.lk / user123
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
