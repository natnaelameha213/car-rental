<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();
$customers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | EliteDrive Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <div class="admin-sidebar" style="width:260px;flex-shrink:0;">
        <div class="px-3 mb-4"><a href="dashboard.php" class="text-decoration-none text-white"><i class="fas fa-car-side text-accent me-2"></i><strong>EliteDrive Admin</strong></a></div>
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link" href="cars.php"><i class="bi bi-car-front me-2"></i>Cars</a>
            <a class="nav-link" href="bookings.php"><i class="bi bi-calendar-check me-2"></i>Bookings</a>
            <a class="nav-link active" href="customers.php"><i class="bi bi-people me-2"></i>Customers</a>
            <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payments</a>
                <a class="nav-link" href="invoices.php"><i class="bi bi-receipt me-2"></i>Invoices</a>
            <a class="nav-link" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a>
            <hr class="border-secondary mx-3">
            <a class="nav-link" href="../index.php" target="_blank"><i class="bi bi-globe me-2"></i>View Website</a>
            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </nav>
    </div>
    <div class="flex-grow-1 p-4" style="background:var(--bg-dark);min-height:100vh;">
        <h2 class="mb-4">Customers</h2>
        <div class="detail-card">
            <div class="table-responsive">
                <table class="table table-dark-custom table-hover">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Status</th><th>Joined</th></tr></thead>
                    <tbody>
                    <?php foreach ($customers as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['phone']) ?></td>
                            <td><?= e($u['city']) ?></td>
                            <td><?= statusBadge(ucfirst($u['status'])) ?></td>
                            <td class="small"><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
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
