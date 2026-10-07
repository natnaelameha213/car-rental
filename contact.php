<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = false;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        if (!$name || !$email || !$message) $errors[] = 'Name, email and message are required.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = true;
        }
    }
}
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="section-title">Contact Us</h1>
            <p class="section-subtitle">We'd love to hear from you</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="detail-card h-100">
                    <h5 class="mb-4">Get in Touch</h5>
                    <p class="text-muted mb-4">Have questions about our fleet or need help with a booking? Reach out anytime.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="bi bi-geo-alt text-accent me-2"></i> Bole Airport Road, Addis Ababa</li>
                        <li class="mb-3"><i class="bi bi-telephone text-accent me-2"></i> +251 11 665 0220</li>
                        <li class="mb-3"><i class="bi bi-envelope text-accent me-2"></i> info@elitedrive.com</li>
                        <li class="mb-3"><i class="bi bi-clock text-accent me-2"></i> Mon – Sun: 7:00 AM – 9:00 PM</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="detail-card">
                    <?php if ($success): ?>
                        <div class="alert alert-success">Thank you! Your message has been sent. We will respond shortly.</div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" value="<?= e($_POST['subject'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-accent px-4">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
