<?php
$pageTitle = 'Book a Car';
require_once 'includes/header.php';

$carId = (int)($_GET['car_id'] ?? $_POST['car_id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, l.name as location_name FROM cars c LEFT JOIN locations l ON c.location_id = l.id WHERE c.id = ? AND c.status = 'Available'");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    setFlash('danger', 'Car not available for booking.');
    redirect('cars.php');
}

$locations = $pdo->query("SELECT * FROM locations WHERE is_active = 1")->fetchAll();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $pickupLoc = (int)$_POST['pickup_location_id'];
        $returnLoc = (int)$_POST['return_location_id'];
        $pickupDate = $_POST['pickup_date'] ?? '';
        $returnDate = $_POST['return_date'] ?? '';
        $name = trim($_POST['customer_name'] ?? '');
        $email = trim($_POST['customer_email'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $address = trim($_POST['customer_address'] ?? '');
        $license = trim($_POST['driver_license'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$pickupDate || !$returnDate) $errors[] = 'Please select pick-up and return dates.';
        if ($pickupDate && $returnDate && $returnDate < $pickupDate) $errors[] = 'Return date must be after pick-up date.';
        if (!$name) $errors[] = 'Name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$phone) $errors[] = 'Phone is required.';

        if (empty($errors)) {
            // Check availability
            if (!isCarAvailable($pdo, $carId, $pickupDate, $returnDate)) {
                $errors[] = 'This car is not available for the selected dates.';
            } else {
                $days = calculateRentalDays($pickupDate, $returnDate);
                $subtotal = $days * $car['daily_price'];
                $deposit = $car['deposit_amount'];
                $total = $subtotal + $deposit;
                $bookingNumber = generateBookingNumber();
                $userId = isLoggedIn() ? $_SESSION['user_id'] : null;

                $stmt = $pdo->prepare("INSERT INTO bookings (booking_number, user_id, car_id, pickup_location_id, return_location_id, pickup_date, return_date, rental_days, daily_rate, subtotal, deposit, total_amount, status, customer_name, customer_email, customer_phone, customer_address, driver_license, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $bookingNumber, $userId, $carId, $pickupLoc, $returnLoc,
                    $pickupDate, $returnDate, $days, $car['daily_price'], $subtotal,
                    $deposit, $total, 'Pending', $name, $email, $phone, $address, $license, $notes
                ]);

                // Update car status to Reserved
                $pdo->prepare("UPDATE cars SET status = 'Reserved' WHERE id = ?")->execute([$carId]);

                setFlash('success', "Booking submitted successfully! Your booking number is {$bookingNumber}. We will contact you shortly.");
                redirect(isLoggedIn() ? 'my-bookings.php' : 'index.php');
            }
        }
    }
}

$user = isLoggedIn() ? currentUser($pdo) : null;
?>

<section class="py-4">
    <div class="container">
        <h1 class="section-title mb-4">Book Your Car</h1>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="detail-card">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                        <input type="hidden" id="daily_rate" value="<?= $car['daily_price'] ?>">

                        <h5 class="mb-3">Rental Period</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Pick-up Location *</label>
                                <select name="pickup_location_id" class="form-select" required>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>" <?= $loc['id'] == $car['location_id'] ? 'selected' : '' ?>><?= e($loc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Return Location *</label>
                                <select name="return_location_id" class="form-select" required>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>"><?= e($loc['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pick-up Date *</label>
                                <input type="date" name="pickup_date" id="pickup_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= e($_POST['pickup_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Return Date *</label>
                                <input type="date" name="return_date" id="return_date" class="form-control" required min="<?= date('Y-m-d') ?>" value="<?= e($_POST['return_date'] ?? '') ?>">
                            </div>
                        </div>

                        <h5 class="mb-3">Customer Information</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="customer_name" class="form-control" required value="<?= e($_POST['customer_name'] ?? ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="customer_email" class="form-control" required value="<?= e($_POST['customer_email'] ?? $user['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="tel" name="customer_phone" class="form-control" required value="<?= e($_POST['customer_phone'] ?? $user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Driver License Number</label>
                                <input type="text" name="driver_license" class="form-control" value="<?= e($_POST['driver_license'] ?? $user['license_number'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="customer_address" class="form-control" rows="2"><?= e($_POST['customer_address'] ?? $user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes / Special Requests</label>
                                <textarea name="notes" class="form-control" rows="2"><?= e($_POST['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-accent btn-lg px-5">Confirm Booking</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="booking-summary">
                    <h5 class="mb-3">Booking Summary</h5>
                    <div class="d-flex gap-3 mb-3">
                        <img src="<?= carImageUrl($car['main_image']) ?>" 
                             alt="" style="width:100px;height:70px;object-fit:cover;border-radius:8px;"
                             >
                        <div>
                            <strong><?= e($car['brand'] . ' ' . $car['model']) ?></strong>
                            <div class="small text-muted"><?= e($car['year']) ?> · <?= e($car['category']) ?></div>
                        </div>
                    </div>
                    <div class="summary-row">
                        <span>Daily Rate</span>
                        <span><?= formatPrice($car['daily_price']) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Rental Days</span>
                        <span id="rental_days">—</span>
                    </div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="total_price">—</span>
                    </div>
                    <div class="summary-row">
                        <span>Security Deposit</span>
                        <span><?= formatPrice($car['deposit_amount']) ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Estimated Total</span>
                        <span><?= formatPrice($car['daily_price'] + $car['deposit_amount']) ?>+</span>
                    </div>
                    <p class="small text-muted mt-3 mb-0">Final total calculated after selecting dates. Deposit is refundable subject to terms.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
