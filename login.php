<?php
$pageTitle = 'Login';
require_once 'includes/header.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $errors[] = 'Email and password are required.';
        } elseif (loginUser($pdo, $email, $password)) {
            setFlash('success', 'Welcome back!');
            redirect($_GET['redirect'] ?? 'index.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2>Welcome Back</h2>
                <p class="text-muted">Login to your EliteDrive account</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <a href="forgot-password.php" class="small text-accent">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-accent w-100 mb-3">Login</button>
                <p class="text-center text-muted small mb-0">Don't have an account? <a href="register.php" class="text-accent">Register</a></p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
