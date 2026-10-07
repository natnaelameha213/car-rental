<?php
$pageTitle = 'Car Details';
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, l.name as location_name, l.address as location_address FROM cars c LEFT JOIN locations l ON c.location_id = l.id WHERE c.id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    setFlash('danger', 'Car not found.');
    redirect('cars.php');
}

$imgStmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_primary DESC, sort_order ASC");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

$features = array_filter(array_map('trim', explode(',', $car['features'] ?? '')));
$pageTitle = $car['brand'] . ' ' . $car['model'];
?>

<section class="py-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-accent">Home</a></li>
                <li class="breadcrumb-item"><a href="cars.php" class="text-accent">Cars</a></li>
                <li class="breadcrumb-item active text-muted"><?= e($car['brand'] . ' ' . $car['model']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Gallery -->
            <div class="col-lg-7">
                <div class="gallery-main mb-3">
                    <img src="<?= carImageUrl($car['main_image']) ?>" 
                         alt="<?= e($car['brand'] . ' ' . $car['model']) ?>"
                         id="mainImage"
                         >
                </div>
                <?php if (count($images) > 1): ?>
                <div class="row g-2 gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                    <div class="col-3">
                        <img src="<?= carImageUrl($img['image_path']) ?>" 
                             class="<?= $i === 0 ? 'active' : '' ?>"
                             alt="Thumbnail"
                             >
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="col-lg-5">
                <div class="detail-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h2 class="mb-1"><?= e($car['brand'] . ' ' . $car['model']) ?></h2>
                            <p class="text-muted mb-0"><?= e($car['year']) ?> · <?= e($car['category']) ?> · <?= e($car['color']) ?></p>
                        </div>
                        <?= statusBadge($car['status']) ?>
                    </div>

                    <div class="price-box my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="price fs-3"><?= formatPrice($car['daily_price']) ?></div>
                                <small class="text-muted">per day</small>
                            </div>
                            <div class="text-end small text-muted">
                                <div>Weekly: <?= formatPrice($car['weekly_price']) ?></div>
                                <div>Monthly: <?= formatPrice($car['monthly_price']) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6"><i class="bi bi-gear text-accent me-2"></i> <?= e($car['transmission']) ?></div>
                        <div class="col-6"><i class="bi bi-fuel-pump text-accent me-2"></i> <?= e($car['fuel_type']) ?></div>
                        <div class="col-6"><i class="bi bi-people text-accent me-2"></i> <?= e($car['seats']) ?> Seats</div>
                        <div class="col-6"><i class="bi bi-door-open text-accent me-2"></i> <?= e($car['doors']) ?> Doors</div>
                        <div class="col-6"><i class="bi bi-speedometer2 text-accent me-2"></i> <?= e($car['engine']) ?></div>
                        <div class="col-6"><i class="bi bi-speedometer text-accent me-2"></i> <?= e($car['mileage']) ?></div>
                        <div class="col-12"><i class="bi bi-geo-alt text-accent me-2"></i> <?= e($car['location_name'] ?? 'N/A') ?></div>
                    </div>

                    <?php if ($car['status'] === 'Available'): ?>
                        <a href="booking.php?car_id=<?= $car['id'] ?>" class="btn btn-accent btn-lg w-100 mb-2">
                            <i class="bi bi-calendar-check me-2"></i>Book Now
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100 mb-2" disabled>Currently Unavailable</button>
                    <?php endif; ?>
                    <p class="text-muted small text-center mb-0">Deposit: <?= formatPrice($car['deposit_amount']) ?></p>
                </div>
            </div>
        </div>

        <!-- Description & Features -->
        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <div class="detail-card">
                    <h4 class="mb-3">Description</h4>
                    <p class="text-muted"><?= nl2br(e($car['description'])) ?></p>
                    
                    <?php if ($features): ?>
                    <h5 class="mt-4 mb-3">Features</h5>
                    <div class="row g-2">
                        <?php foreach ($features as $f): ?>
                        <div class="col-md-6">
                            <i class="bi bi-check-circle-fill text-accent me-2"></i><?= e($f) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="detail-card">
                    <h5 class="mb-3">Rental Conditions</h5>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>Minimum age: 21 years</li>
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>Valid driver's license required</li>
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>Security deposit required</li>
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>Full insurance included</li>
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>Unlimited mileage (unless stated)</li>
                        <li class="mb-2"><i class="bi bi-check2 text-accent me-2"></i>24/7 roadside assistance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
