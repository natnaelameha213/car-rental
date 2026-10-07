<?php
$pageTitle = 'My Profile';
require_once 'includes/header.php';
requireLogin();
$user = currentUser($pdo);

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $license = trim($_POST['license_number'] ?? '');
        if (!$first || !$last) $errors[] = 'Name is required.';
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, address=?, city=?, license_number=? WHERE id=?");
            $stmt->execute([$first, $last, $phone, $address, $city, $license, $user['id']]);
            $_SESSION['user_name'] = $first . ' ' . $last;
            $success = true;
            $user = currentUser($pdo);
        }
    }
}
?>
<section class="py-4">
    <div class="container">
        <h1 class="section-title mb-4">My Profile</h1>
        <?php if ($success): ?><div class="alert alert-success">Profile updated successfully.</div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="detail-card">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="<?= e($user['address']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?= e($user['city']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Driver License</label>
                                <input type="text" name="license_number" class="form-control" value="<?= e($user['license_number']) ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-accent mt-4">Save Changes</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="detail-card text-center">
                    <div class="rounded-circle bg-accent text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:2rem;font-weight:700;">
                        <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                    </div>
                    <h5><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                    <p class="text-muted small"><?= e($user['email']) ?></p>
                    <a href="my-bookings.php" class="btn btn-outline-accent btn-sm">My Bookings</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
