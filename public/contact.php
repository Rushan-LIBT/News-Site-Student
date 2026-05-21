<?php
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Contact Us';
$active_nav = 'contact';

$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token. Please try again.';
    }
    if (strlen($old['name']) < 2)    $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($old['subject']) < 3) $errors[] = 'Subject is required.';
    if (strlen($old['message']) < 10) $errors[] = 'Message must be at least 10 characters.';

    if (empty($errors)) {
        $stmt = db()->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:n, :e, :s, :m)");
        $stmt->execute([
            ':n' => $old['name'],
            ':e' => $old['email'],
            ':s' => $old['subject'],
            ':m' => $old['message'],
        ]);
        flash('success', 'Thank you! Your message has been sent. We\'ll get back to you soon.');
        redirect(SITE_URL . '/contact.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="section-title">Contact Us</h1>
            <p class="text-muted">Have a tip, feedback, or general inquiry? Drop us a message — we read every one.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-7">
                    <div class="form-card p-4 bg-white border rounded">
                        <form method="post" novalidate>
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Subject *</label>
                                    <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea name="message" class="form-control" rows="6" required><?= e($old['message']) ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-accent btn-lg">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="sidebar-widget">
                        <h5><i class="fas fa-map-marker-alt text-accent"></i> Our Office</h5>
                        <p class="small mb-0">123 Galle Road<br>Colombo 03, Sri Lanka</p>
                    </div>
                    <div class="sidebar-widget">
                        <h5><i class="fas fa-envelope text-accent"></i> Email</h5>
                        <p class="small mb-0">
                            General: info@newslanka.lk<br>
                            News tips: tips@newslanka.lk<br>
                            Advertising: ads@newslanka.lk
                        </p>
                    </div>
                    <div class="sidebar-widget">
                        <h5><i class="fas fa-phone text-accent"></i> Phone</h5>
                        <p class="small mb-0">+94 11 234 5678<br>Mon–Fri, 9am–5pm</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
