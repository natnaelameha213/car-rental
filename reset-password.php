<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$token = (string)($_GET['token'] ?? $_POST['token'] ?? '');
$user = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() AND status = 'active'");
    $stmt->execute([hash('sha256', $token)]);
    $user = $stmt->fetch();
}

$errors = [];
if ($user && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } elseif (strlen($pw) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($pw !== ($_POST['password_confirm'] ?? '')) {
        $errors[] = 'Passwords do not match.';
    } else {
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $user['id']]);
        setFlash('success', 'Password updated. You can now log in.');
        redirect('login.php');
    }
}

$pageTitle = 'Reset Password';
require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4"><h2>Reset Password</h2></div>
            <?php if (!$user): ?>
                <div class="alert alert-danger">This reset link is invalid or has expired.</div>
                <p class="text-center"><a href="forgot-password.php" class="text-accent">Request a new link</a></p>
            <?php else: ?>
                <?php if ($errors): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="mb-3"><label class="form-label">New password</label>
                        <input type="password" name="password" class="form-control" required minlength="6"></div>
                    <div class="mb-3"><label class="form-label">Confirm new password</label>
                        <input type="password" name="password_confirm" class="form-control" required minlength="6"></div>
                    <button type="submit" class="btn btn-accent w-100">Update password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
