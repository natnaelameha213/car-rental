<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    $allowed = ['Pending','Confirmed','Active','Completed','Cancelled'];
    if (in_array($status, $allowed)) {
        $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $id]);
        // Update car status accordingly
        $booking = $pdo->prepare("SELECT car_id FROM bookings WHERE id = ?");
        $booking->execute([$id]);
        $carId = $booking->fetchColumn();
        if ($status === 'Active' || $status === 'Confirmed') {
            $pdo->prepare("UPDATE cars SET status = 'Rented' WHERE id = ?")->execute([$carId]);
        } elseif ($status === 'Completed' || $status === 'Cancelled') {
            $pdo->prepare("UPDATE cars SET status = 'Available' WHERE id = ?")->execute([$carId]);
        }
        setFlash('success', 'Booking status updated.');
    }
    header('Location: bookings.php');
    exit;
}

$bookings = $pdo->query("SELECT b.*, c.brand, c.model FROM bookings b JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings | EliteDrive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                <a class="nav-link active" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
                <a class="nav-link" href="customers.php"><i class="bi bi-people me-2"></i>Customers</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
                <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                <hr class="border-secondary mx-3">
                <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
        </div>
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <h2 class="mb-4">Bookings Management</h2>
            <div class="detail-card">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-hover">
                        <thead>
                            <tr>
                                <th>Booking #</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Dates</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><?= e($b['booking_number']) ?></td>
                                <td>
                                    <?= e($b['customer_name']) ?>
                                    <div class="small text-muted"><?= e($b['customer_phone']) ?></div>
                                </td>
                                <td><?= e($b['brand'] . ' ' . $b['model']) ?></td>
                                <td class="small"><?= e($b['pickup_date']) ?> → <?= e($b['return_date']) ?></td>
                                <td><?= formatPrice($b['total_amount']) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <?php foreach (['Pending','Confirmed','Active','Completed','Cancelled'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $b['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
