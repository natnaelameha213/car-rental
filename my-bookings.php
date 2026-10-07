<?php
$pageTitle = 'My Bookings';
require_once 'includes/header.php';
requireLogin();

$stmt = $pdo->prepare("SELECT b.*, c.brand, c.model, c.main_image FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<section class="py-4">
    <div class="container">
        <h1 class="section-title mb-4">My Bookings</h1>
        <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted"></i>
                <h4 class="mt-3">No bookings yet</h4>
                <p class="text-muted">Start exploring our fleet and book your first car.</p>
                <a href="cars.php" class="btn btn-accent">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($bookings as $b): ?>
                <div class="col-md-6">
                    <div class="detail-card">
                        <div class="d-flex gap-3">
                            <img src="<?= carImageUrl($b['main_image']) ?>" 
                                 style="width:120px;height:80px;object-fit:cover;border-radius:8px;"
                                 
                                 alt="">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <h5 class="mb-1"><?= e($b['brand'] . ' ' . $b['model']) ?></h5>
                                    <?= statusBadge($b['status']) ?>
                                </div>
                                <p class="small text-muted mb-1">#<?= e($b['booking_number']) ?></p>
                                <p class="small mb-1">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= e($b['pickup_date']) ?> → <?= e($b['return_date']) ?>
                                    (<?= $b['rental_days'] ?> days)
                                </p>
                                <p class="mb-0"><strong><?= formatPrice($b['total_amount']) ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
