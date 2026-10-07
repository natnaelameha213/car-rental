<?php
$pageTitle = 'Our Fleet';
require_once 'includes/header.php';

$filters = [
    'brand' => $_GET['brand'] ?? '',
    'category' => $_GET['category'] ?? '',
    'transmission' => $_GET['transmission'] ?? '',
    'fuel_type' => $_GET['fuel_type'] ?? '',
    'seats' => $_GET['seats'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'location_id' => $_GET['location_id'] ?? '',
    'search' => $_GET['search'] ?? '',
    'pickup_date' => $_GET['pickup_date'] ?? '',
    'return_date' => $_GET['return_date'] ?? '',
    'sort' => $_GET['sort'] ?? 'daily_price ASC'
];

$cars = searchAvailableCars($pdo, $filters);

// Filter options
$brands = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$locations = $pdo->query("SELECT * FROM locations WHERE is_active = 1")->fetchAll();
?>

<section class="py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <h1 class="section-title mb-1">Our Fleet</h1>
                <p class="text-muted"><?= count($cars) ?> vehicles available</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Filters -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filters</h5>
                    <form method="GET" action="cars.php">
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Brand or model..." value="<?= e($filters['search']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= e($b) ?>" <?= $filters['brand'] === $b ? 'selected' : '' ?>><?= e($b) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All</option>
                                <?php foreach (['Sedan','SUV','Luxury','Pickup','Van','Hatchback','Crossover'] as $c): ?>
                                    <option value="<?= $c ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transmission</label>
                            <select name="transmission" class="form-select">
                                <option value="">All</option>
                                <option value="Automatic" <?= $filters['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $filters['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fuel Type</label>
                            <select name="fuel_type" class="form-select">
                                <option value="">All</option>
                                <?php foreach (['Petrol','Diesel','Hybrid','Electric'] as $f): ?>
                                    <option value="<?= $f ?>" <?= $filters['fuel_type'] === $f ? 'selected' : '' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Min Seats</label>
                            <select name="seats" class="form-select">
                                <option value="">Any</option>
                                <?php for ($i = 2; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filters['seats'] == $i ? 'selected' : '' ?>><?= $i ?>+</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price Range ($/day)</label>
                            <div class="row g-2">
                                <div class="col"><input type="number" name="min_price" class="form-control" placeholder="Min" value="<?= e($filters['min_price']) ?>"></div>
                                <div class="col"><input type="number" name="max_price" class="form-control" placeholder="Max" value="<?= e($filters['max_price']) ?>"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-select">
                                <option value="">All</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['id'] ?>" <?= $filters['location_id'] == $loc['id'] ? 'selected' : '' ?>><?= e($loc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select">
                                <option value="daily_price ASC" <?= $filters['sort'] === 'daily_price ASC' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="daily_price DESC" <?= $filters['sort'] === 'daily_price DESC' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="year DESC" <?= $filters['sort'] === 'year DESC' ? 'selected' : '' ?>>Newest First</option>
                                <option value="brand ASC" <?= $filters['sort'] === 'brand ASC' ? 'selected' : '' ?>>Brand A-Z</option>
                            </select>
                        </div>
                        <?php if ($filters['pickup_date']): ?>
                            <input type="hidden" name="pickup_date" value="<?= e($filters['pickup_date']) ?>">
                            <input type="hidden" name="return_date" value="<?= e($filters['return_date']) ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-accent w-100 mb-2">Apply Filters</button>
                        <a href="cars.php" class="btn btn-dark-glass w-100">Clear All</a>
                    </form>
                </div>
            </div>

            <!-- Cars Grid -->
            <div class="col-lg-9">
                <?php if (empty($cars)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-car-front display-1 text-muted"></i>
                        <h4 class="mt-3">No cars found</h4>
                        <p class="text-muted">Try adjusting your filters or dates.</p>
                        <a href="cars.php" class="btn btn-accent">View All Cars</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($cars as $car): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="car-card">
                                <div class="position-relative overflow-hidden">
                                    <img src="<?= carImageUrl($car['main_image']) ?>" 
                                         class="card-img-top" 
                                         alt="<?= e($car['brand'] . ' ' . $car['model']) ?>"
                                         >
                                    <span class="badge bg-accent text-white position-absolute top-0 end-0 m-3"><?= e($car['category']) ?></span>
                                    <?= statusBadge($car['status']) ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-1"><?= e($car['brand'] . ' ' . $car['model']) ?></h5>
                                    <p class="text-muted small mb-2"><?= e($car['year']) ?> · <?= e($car['location_name'] ?? '') ?></p>
                                    <div class="car-specs mb-3">
                                        <span><i class="bi bi-gear"></i> <?= e($car['transmission']) ?></span>
                                        <span><i class="bi bi-fuel-pump"></i> <?= e($car['fuel_type']) ?></span>
                                        <span><i class="bi bi-people"></i> <?= e($car['seats']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="price"><?= formatPrice($car['daily_price']) ?> <small>/day</small></div>
                                        <div class="d-flex gap-1">
                                            <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-dark-glass">View</a>
                                            <a href="booking.php?car_id=<?= $car['id'] ?>" class="btn btn-sm btn-accent">Book</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
