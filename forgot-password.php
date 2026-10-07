<?php
// Logic first (no HTML output yet), then the themed page.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) { redirect('profile.php'); }

$errors = [];
$sent = false;
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $token = bin2hex(random_bytes(32));
                // only a hash of the token is stored in the database
                $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?")
                    ->execute([hash('sha256', $token), $user['id']]);
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
                $link = $scheme . '://' . $_SERVER['HTTP_HOST'] . $dir . '/reset-password.php?token=' . $token;
                $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
                if ($isLocal) {
                    // XAMPP has no mail server: on localhost only, show the link on screen for demo/testing.
                    $resetLink = $link;
                } else {
                    @mail($email, 'Password reset', "Open this link to choose a new password (valid for 1 hour):\n\n" . $link . "\n\nIf you did not request this, ignore this email.", 'From: no-reply@' . preg_replace('/[^A-Za-z0-9.\-]/', '', $_SERVER['HTTP_HOST']));
                }
            }
            // same message whether or not the email exists (prevents account enumeration)
            $sent = true;
        }
    }
}

$pageTitle = 'Forgot Password';
require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2>Forgot Password</h2>
                <p class="text-muted">Enter your account email and we will send you a reset link.</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
            <?php endif; ?>
            <?php if ($sent): ?>
                <div class="alert alert-success">If an account exists for that email, a reset link has been sent. It is valid for 1 hour.</div>
                <?php if ($resetLink): ?>
                    <div class="alert alert-info small">Local demo mode (no mail server): <a href="<?= e($resetLink) ?>">open your reset link</a></div>
                <?php endif; ?>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-accent w-100 mb-3">Send reset link</button>
                <p class="text-center text-muted small mb-0"><a href="login.php" class="text-accent">Back to login</a></p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
