<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

// Stats
$stats = [
    'total_cars' => $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn(),
    'available' => $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Available'")->fetchColumn(),
    'rented' => $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Rented'")->fetchColumn(),
    'maintenance' => $pdo->query("SELECT COUNT(*) FROM cars WHERE status = 'Maintenance'")->fetchColumn(),
    'total_bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'")->fetchColumn(),
    'customers' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'revenue' => $pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM bookings WHERE status IN ('Completed','Active','Confirmed')")->fetchColumn(),
    'deposits' => $pdo->query("SELECT COALESCE(SUM(deposit),0) FROM bookings WHERE status NOT IN ('Cancelled')")->fetchColumn(),
];

$recentBookings = $pdo->query("SELECT b.*, c.brand, c.model FROM bookings b JOIN cars c ON b.car_id = c.id ORDER BY b.created_at DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EliteDrive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="admin-sidebar" style="width:260px;flex-shrink:0;">
            <div class="px-3 mb-4">
                <a href="dashboard.php" class="text-decoration-none text-white">
                    <i class="fas fa-car-side text-accent me-2"></i>
                    <strong>EliteDrive Admin</strong>
                </a>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link" href="cars.php"><i class="bi bi-car-front me-2"></i>Cars</a>
                <a class="nav-link" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
                <a class="nav-link" href="customers.php"><i class="bi bi-people me-2"></i>Customers</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
                <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
                <hr class="border-secondary mx-3">
                <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
                <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </nav>
        </div>

        <!-- Main -->
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Dashboard</h2>
                <span class="text-muted">Welcome, <?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
            </div>

            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value text-accent"><?= $stats['total_cars'] ?></div>
                        <div class="stat-label">Total Cars</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value text-success"><?= $stats['available'] ?></div>
                        <div class="stat-label">Available</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value text-primary"><?= $stats['rented'] ?></div>
                        <div class="stat-label">Rented</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['total_bookings'] ?></div>
                        <div class="stat-label">Bookings</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value text-warning"><?= $stats['pending'] ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card">
                        <div class="stat-value text-info"><?= $stats['customers'] ?></div>
                        <div class="stat-label">Customers</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-value text-accent"><?= formatPrice($stats['revenue']) ?></div>
                        <div class="stat-label">Total Revenue (Paid)</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-value"><?= formatPrice($stats['deposits']) ?></div>
                        <div class="stat-label">Total Deposits Held</div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="detail-card">
                <h5 class="mb-3">Recent Bookings</h5>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td><a href="bookings.php?id=<?= $b['id'] ?>" class="text-accent"><?= e($b['booking_number']) ?></a></td>
                                <td><?= e($b['customer_name']) ?></td>
                                <td><?= e($b['brand'] . ' ' . $b['model']) ?></td>
                                <td class="small"><?= e($b['pickup_date']) ?> → <?= e($b['return_date']) ?></td>
                                <td><?= formatPrice($b['total_amount']) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="bookings.php" class="btn btn-sm btn-outline-accent">View All Bookings</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
