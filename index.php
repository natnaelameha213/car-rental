<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Featured cars
$stmt = $pdo->query("SELECT c.*, l.name as location_name FROM cars c LEFT JOIN locations l ON c.location_id = l.id WHERE c.status = 'Available' ORDER BY c.daily_price DESC LIMIT 6");
$featuredCars = $stmt->fetchAll();

// Categories count
$catStmt = $pdo->query("SELECT category, COUNT(*) as cnt FROM cars WHERE status = 'Available' GROUP BY category ORDER BY cnt DESC");
$categories = $catStmt->fetchAll();

// Reviews
$revStmt = $pdo->query("SELECT r.*, u.first_name, u.last_name, c.brand, c.model FROM reviews r LEFT JOIN users u ON r.user_id = u.id LEFT JOIN cars c ON r.car_id = c.id WHERE r.status = 'Approved' ORDER BY r.created_at DESC LIMIT 3");
$reviews = $revStmt->fetchAll();

// Locations for search
$locStmt = $pdo->query("SELECT * FROM locations WHERE is_active = 1");
$locations = $locStmt->fetchAll();
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <span class="badge bg-accent text-white mb-3 px-3 py-2">Premium Car Rental Service</span>
                <h1>Drive Your Dream.<br><span class="text-accent">Anytime. Anywhere.</span></h1>
                <p class="lead mb-4">Experience luxury and reliability with our premier car rental service. From economy sedans to executive SUVs — we have the perfect vehicle for every journey.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="cars.php" class="btn btn-accent btn-lg px-4">Browse Fleet</a>
                    <a href="#search" class="btn btn-outline-accent btn-lg px-4">Search Cars</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <i class="fas fa-car-side" style="font-size: 12rem; color: rgba(232,185,35,0.15);"></i>
            </div>
        </div>
    </div>
</section>

<!-- Search Box -->
<section class="container" id="search">
    <div class="search-box">
        <form action="cars.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Pick-up Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>"><?= e($loc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pick-up Date</label>
                <input type="date" name="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <option value="Sedan">Sedan</option>
                    <option value="SUV">SUV</option>
                    <option value="Luxury">Luxury</option>
                    <option value="Pickup">Pickup</option>
                    <option value="Van">Van</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-accent w-100 py-2">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Featured Cars -->
<section class="py-5 mt-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title mb-1">Featured Vehicles</h2>
                <p class="section-subtitle mb-0">Hand-picked premium cars for your next journey</p>
            </div>
            <a href="cars.php" class="btn btn-outline-accent d-none d-md-inline-flex">View All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredCars as $car): ?>
            <div class="col-md-6 col-lg-4">
                <div class="car-card">
                    <div class="position-relative overflow-hidden">
                        <img src="<?= carImageUrl($car['main_image']) ?>" 
                             class="card-img-top" 
                             alt="<?= e($car['brand'] . ' ' . $car['model']) ?>"
                             >
                        <span class="badge bg-accent text-white position-absolute top-0 end-0 m-3"><?= e($car['category']) ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= e($car['brand'] . ' ' . $car['model']) ?></h5>
                        <p class="text-muted small mb-2"><?= e($car['year']) ?> · <?= e($car['location_name'] ?? 'Addis Ababa') ?></p>
                        <div class="car-specs mb-3">
                            <span><i class="bi bi-gear"></i> <?= e($car['transmission']) ?></span>
                            <span><i class="bi bi-fuel-pump"></i> <?= e($car['fuel_type']) ?></span>
                            <span><i class="bi bi-people"></i> <?= e($car['seats']) ?> Seats</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="price"><?= formatPrice($car['daily_price']) ?> <small>/ day</small></div>
                            <div class="d-flex gap-2">
                                <a href="car-details.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-dark-glass">Details</a>
                                <a href="booking.php?car_id=<?= $car['id'] ?>" class="btn btn-sm btn-accent">Book</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 d-md-none">
            <a href="cars.php" class="btn btn-outline-accent">View All Cars</a>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="py-5" style="background: var(--bg-elevated);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Popular Categories</h2>
            <p class="section-subtitle">Find the perfect vehicle type for your needs</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php 
            $catIcons = ['Sedan' => 'bi-car-front', 'SUV' => 'bi-truck', 'Luxury' => 'bi-star', 'Pickup' => 'bi-truck-flatbed', 'Van' => 'bi-bus-front', 'Hatchback' => 'bi-car-front-fill', 'Crossover' => 'bi-car-front'];
            foreach ($categories as $cat): 
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="cars.php?category=<?= urlencode($cat['category']) ?>" class="category-card">
                    <i class="bi <?= $catIcons[$cat['category']] ?? 'bi-car-front' ?>"></i>
                    <h6 class="mb-1"><?= e($cat['category']) ?></h6>
                    <small class="text-muted"><?= $cat['cnt'] ?> cars</small>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Choose EliteDrive</h2>
            <p class="section-subtitle">We deliver more than just a car — we deliver peace of mind</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-wrap"><i class="bi bi-shield-check"></i></div>
                    <h5>Fully Insured</h5>
                    <p class="text-muted small mb-0">Comprehensive insurance coverage on every rental for your protection.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-wrap"><i class="bi bi-geo-alt"></i></div>
                    <h5>Multiple Locations</h5>
                    <p class="text-muted small mb-0">Convenient pick-up and drop-off points across major cities.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-wrap"><i class="bi bi-headset"></i></div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted small mb-0">Round-the-clock customer service ready to assist you anytime.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-box">
                    <div class="icon-wrap"><i class="bi bi-currency-exchange"></i></div>
                    <h5>Best Rates</h5>
                    <p class="text-muted small mb-0">Competitive pricing with transparent fees and no hidden charges.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews -->
<?php if ($reviews): ?>
<section class="py-5" style="background: var(--bg-elevated);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Customer Reviews</h2>
            <p class="section-subtitle">What our clients say about us</p>
        </div>
        <div class="row g-4">
            <?php foreach ($reviews as $rev): ?>
            <div class="col-md-4">
                <div class="review-card">
                    <div class="stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= $rev['rating'] ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <h6 class="mb-2"><?= e($rev['title']) ?></h6>
                    <p class="text-muted small mb-3">"<?= e($rev['comment']) ?>"</p>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-accent text-white d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-weight:700;">
                            <?= strtoupper(substr($rev['first_name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div>
                            <strong class="d-block small"><?= e(($rev['first_name'] ?? '') . ' ' . ($rev['last_name'] ?? '')) ?></strong>
                            <small class="text-muted"><?= e(($rev['brand'] ?? '') . ' ' . ($rev['model'] ?? '')) ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="py-5">
    <div class="container">
        <div class="glass rounded-lg p-5 text-center">
            <h2 class="mb-3">Ready to Hit the Road?</h2>
            <p class="text-muted mb-4 col-lg-6 mx-auto">Book your perfect car today and enjoy a seamless rental experience with EliteDrive. Competitive rates, premium vehicles, exceptional service.</p>
            <a href="cars.php" class="btn btn-accent btn-lg px-5">Explore Our Fleet</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
