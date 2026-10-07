<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$cars = $pdo->query("SELECT c.*, l.name as location_name FROM cars c LEFT JOIN locations l ON c.location_id = l.id ORDER BY c.brand, c.model")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars | EliteDrive Admin</title>
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
                <a class="nav-link active" href="cars.php"><i class="bi bi-car-front me-2"></i>Cars</a>
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
        <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Cars Management</h2>
                <a href="add-car.php" class="btn btn-accent btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Car</a>
            </div>
            <div class="detail-card">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vehicle</th>
                                <th>Category</th>
                                <th>Price/Day</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= $car['id'] ?></td>
                                <td>
                                    <strong><?= e($car['brand'] . ' ' . $car['model']) ?></strong>
                                    <div class="small text-muted"><?= e($car['year']) ?></div>
                                </td>
                                <td><?= e($car['category']) ?></td>
                                <td><?= formatPrice($car['daily_price']) ?></td>
                                <td><?= statusBadge($car['status']) ?></td>
                                <td class="small"><?= e($car['location_name'] ?? '—') ?></td>
                                <td>
                                    <a href="edit-car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-dark-glass">Edit</a>
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
