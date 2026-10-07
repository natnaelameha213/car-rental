<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();
if (!$car) {
    setFlash('danger', 'Car not found.');
    redirect('cars.php');
}

$locations = $pdo->query("SELECT * FROM locations WHERE is_active = 1 ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = (int)($_POST['year'] ?? 0);
        $category = $_POST['category'] ?? 'Sedan';
        $daily_price = (float)($_POST['daily_price'] ?? 0);
        $weekly_price = (float)($_POST['weekly_price'] ?? 0);
        $monthly_price = (float)($_POST['monthly_price'] ?? 0);
        $transmission = $_POST['transmission'] ?? 'Automatic';
        $fuel_type = $_POST['fuel_type'] ?? 'Petrol';
        $seats = (int)($_POST['seats'] ?? 5);
        $doors = (int)($_POST['doors'] ?? 4);
        $engine = trim($_POST['engine'] ?? '');
        $mileage = trim($_POST['mileage'] ?? '');
        $color = trim($_POST['color'] ?? '');
        $location_id = (int)($_POST['location_id'] ?? 0);
        $status = $_POST['status'] ?? 'Available';
        $deposit_amount = (float)($_POST['deposit_amount'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $main_image = trim($_POST['main_image'] ?? '');

        if (!$brand || !$model || $year < 1990 || $daily_price <= 0) {
            $errors[] = 'Brand, model, year and daily price are required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE cars SET brand=?, model=?, year=?, category=?, daily_price=?, weekly_price=?, monthly_price=?, transmission=?, fuel_type=?, seats=?, doors=?, engine=?, mileage=?, color=?, location_id=?, status=?, deposit_amount=?, description=?, features=?, main_image=? WHERE id=?");
            $stmt->execute([$brand, $model, $year, $category, $daily_price, $weekly_price, $monthly_price, $transmission, $fuel_type, $seats, $doors, $engine, $mileage, $color, $location_id ?: null, $status, $deposit_amount, $description, $features, $main_image, $id]);
            setFlash('success', 'Car updated successfully.');
            redirect('cars.php');
        }
    }
} else {
    $_POST = $car;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car | EliteDrive Admin</title>
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
                <h2 class="mb-0">Edit Car #<?= $id ?></h2>
                <a href="cars.php" class="btn btn-outline-accent btn-sm">Back to Cars</a>
            </div>
            <?php if ($errors): ?>
                <div class="alert alert-danger"><?= e(implode('<br>', $errors)) ?></div>
            <?php endif; ?>
            <div class="card-dark p-4">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Brand *</label><input type="text" name="brand" class="form-control" required value="<?= e($_POST['brand'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Model *</label><input type="text" name="model" class="form-control" required value="<?= e($_POST['model'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Year *</label><input type="number" name="year" class="form-control" required min="1990" max="2030" value="<?= e($_POST['year'] ?? '') ?>"></div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <?php foreach (['Sedan','SUV','Pickup','Van','Luxury','Hatchback','Coupe'] as $c): ?>
                                    <option value="<?= $c ?>" <?= ($_POST['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Daily Price ($)</label><input type="number" step="0.01" name="daily_price" class="form-control" required value="<?= e($_POST['daily_price'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Weekly Price ($)</label><input type="number" step="0.01" name="weekly_price" class="form-control" value="<?= e($_POST['weekly_price'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Monthly Price ($)</label><input type="number" step="0.01" name="monthly_price" class="form-control" value="<?= e($_POST['monthly_price'] ?? '') ?>"></div>
                        <div class="col-md-3">
                            <label class="form-label">Transmission</label>
                            <select name="transmission" class="form-select">
                                <option <?= ($_POST['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option <?= ($_POST['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fuel Type</label>
                            <select name="fuel_type" class="form-select">
                                <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                                    <option <?= ($_POST['fuel_type'] ?? '') === $f ? 'selected' : '' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><label class="form-label">Seats</label><input type="number" name="seats" class="form-control" value="<?= e($_POST['seats'] ?? 5) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Doors</label><input type="number" name="doors" class="form-control" value="<?= e($_POST['doors'] ?? 4) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Deposit ($)</label><input type="number" step="0.01" name="deposit_amount" class="form-control" value="<?= e($_POST['deposit_amount'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Engine</label><input type="text" name="engine" class="form-control" value="<?= e($_POST['engine'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Mileage</label><input type="text" name="mileage" class="form-control" value="<?= e($_POST['mileage'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Color</label><input type="text" name="color" class="form-control" value="<?= e($_POST['color'] ?? '') ?>"></div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-select">
                                <option value="">Select</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['id'] ?>" <?= ($_POST['location_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>><?= e($loc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (['Available','Reserved','Rented','Maintenance','Inactive'] as $s): ?>
                                    <option <?= ($_POST['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Image URL</label><input type="text" name="main_image" class="form-control" value="<?= e($_POST['main_image'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($_POST['description'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label">Features</label><input type="text" name="features" class="form-control" value="<?= e($_POST['features'] ?? '') ?>"></div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-accent">Update Car</button>
                            <a href="cars.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
