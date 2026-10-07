<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $errors[] = 'Username and password required.';
    } elseif (loginAdmin($pdo, $username, $password)) {
        redirect('dashboard.php');
    } else {
        $errors[] = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | EliteDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2><i class="fas fa-car-side text-accent me-2"></i>Admin Login</h2>
                <p class="text-muted">EliteDrive Management Panel</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-accent w-100">Login</button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0">
                Default: admin / password<br>
                <a href="../index.php" class="text-accent">← Back to Website</a>
            </p>
        </div>
    </div>
</body>
</html>
