<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

if (isLoggedIn()) {
    redirect('profile.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'license_number' => trim($_POST['license_number'] ?? '')
        ];
        if (!$data['first_name'] || !$data['last_name']) $errors[] = 'Name is required.';
        if (!$data['email'] || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (strlen($data['password']) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($data['password'] !== ($_POST['password_confirm'] ?? '')) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $result = registerUser($pdo, $data);
            if ($result['success']) {
                loginUser($pdo, $data['email'], $data['password']);
                setFlash('success', 'Account created successfully!');
                redirect('index.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="auth-card" style="max-width:520px;">
            <div class="text-center mb-4">
                <h2>Create Account</h2>
                <p class="text-muted">Join EliteDrive for faster bookings</p>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required value="<?= e($_POST['first_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required value="<?= e($_POST['last_name'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= e($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver License</label>
                        <input type="text" name="license_number" class="form-control" value="<?= e($_POST['license_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?= e($_POST['address'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?= e($_POST['city'] ?? '') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-accent w-100 mt-4 mb-3">Create Account</button>
                <p class="text-center text-muted small mb-0">Already have an account? <a href="login.php" class="text-accent">Login</a></p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
