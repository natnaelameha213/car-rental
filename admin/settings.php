<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$settings = $pdo->query("SELECT * FROM settings ORDER BY setting_key")->fetchAll(PDO::FETCH_KEY_PAIR);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $keys = ['company_name','company_email','company_phone','company_address','currency','currency_symbol','tax_rate','booking_deposit_percent','min_rental_days','max_rental_days','cancellation_hours'];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmt->execute([$key, trim($_POST[$key])]);
            }
        }
        $success = true;
        $settings = $pdo->query("SELECT * FROM settings ORDER BY setting_key")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | EliteDrive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <div class="admin-sidebar" style="width:260px;flex-shrink:0;">
            <div class="px-3 mb-4">
                <a href="dashboard.php" class="text-decoration-none text-white">
                    <i class="fas fa-car-side text-accent me-2"></i><strong>EliteDrive Admin</strong>
                </a>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link" href="cars.php"><i class="bi bi-car-front me-2"></i>Cars</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
                <a class="nav-link" href="customers.php"><i class="bi bi-people me-2"></i>Customers</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
                <a class="nav-link active" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                <hr class="border-secondary mx-3">
                <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
        </div>
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <h2 class="mb-4">Settings</h2>
            <?php if ($success): ?><div class="alert alert-success">Settings saved.</div><?php endif; ?>
            <?php if ($errors): ?><div class="alert alert-danger"><?= e(implode('<br>', $errors)) ?></div><?php endif; ?>
            <div class="card-dark p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? 'EliteDrive Car Rental') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="company_email" class="form-control" value="<?= e($settings['company_email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="company_phone" class="form-control" value="<?= e($settings['company_phone'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="company_address" class="form-control" value="<?= e($settings['company_address'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Currency</label><input type="text" name="currency" class="form-control" value="<?= e($settings['currency'] ?? 'USD') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol'] ?? '$') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= e($settings['tax_rate'] ?? '0') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Deposit %</label><input type="number" name="booking_deposit_percent" class="form-control" value="<?= e($settings['booking_deposit_percent'] ?? '30') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Min Rental Days</label><input type="number" name="min_rental_days" class="form-control" value="<?= e($settings['min_rental_days'] ?? '1') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Max Rental Days</label><input type="number" name="max_rental_days" class="form-control" value="<?= e($settings['max_rental_days'] ?? '90') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Cancellation Hours</label><input type="number" name="cancellation_hours" class="form-control" value="<?= e($settings['cancellation_hours'] ?? '24') ?>"></div>
                        <div class="col-12"><button type="submit" class="btn btn-accent">Save Settings</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
