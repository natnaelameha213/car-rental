<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$payments = $pdo->query("SELECT p.*, b.booking_number, b.customer_name FROM payments p LEFT JOIN bookings b ON p.booking_id = b.id ORDER BY p.paid_at DESC, p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments | EliteDrive Admin</title>
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
                <a class="nav-link active" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
                <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                <hr class="border-secondary mx-3">
                <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
        </div>
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <h2 class="mb-4">Payments</h2>
            <div class="card-dark p-3">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Payment #</th>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No payments yet.</td></tr>
                            <?php else: foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= e($p['payment_number']) ?></td>
                                    <td><?= e($p['booking_number'] ?? '-') ?></td>
                                    <td><?= e($p['customer_name'] ?? '-') ?></td>
                                    <td class="text-accent"><?= formatPrice($p['amount']) ?></td>
                                    <td><?= e($p['payment_method']) ?></td>
                                    <td><?= e($p['payment_type']) ?></td>
                                    <td><?= statusBadge($p['status']) ?></td>
                                    <td><?= $p['paid_at'] ? date('M d, Y H:i', strtotime($p['paid_at'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
