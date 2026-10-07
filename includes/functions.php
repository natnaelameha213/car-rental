<?php
/**
 * Helper Functions
 * Car Rental Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize output to prevent XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency in $
 */
function formatPrice($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Get car image URL (supports external Unsplash/Pexels URLs or local files)
 */
function carImageUrl($path) {
    if (empty($path)) {
        return 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=800&h=500&fit=crop&q=80';
    }
    // Already a full URL
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    // Local file
    return 'assets/images/cars/' . ltrim($path, '/');
}

/**
 * Generate unique booking number
 */
function generateBookingNumber() {
    return 'BK' . date('Ym') . strtoupper(substr(uniqid(), -5));
}

/**
 * Generate unique payment number
 */
function generatePaymentNumber() {
    return 'PAY' . date('Ym') . strtoupper(substr(uniqid(), -5));
}

/**
 * Generate unique invoice number
 */
function generateInvoiceNumber() {
    return 'INV' . date('Ym') . strtoupper(substr(uniqid(), -5));
}

/**
 * Calculate rental days between two dates
 */
function calculateRentalDays($pickup, $return) {
    $start = new DateTime($pickup);
    $end = new DateTime($return);
    $diff = $start->diff($end);
    return max(1, (int)$diff->days);
}

/**
 * Check if car is available for date range
 */
function isCarAvailable($pdo, $carId, $pickupDate, $returnDate, $excludeBookingId = null) {
    $sql = "SELECT COUNT(*) FROM bookings 
            WHERE car_id = :car_id 
            AND status NOT IN ('Cancelled', 'Completed')
            AND (
                (pickup_date <= :return_date AND return_date >= :pickup_date)
            )";
    
    if ($excludeBookingId) {
        $sql .= " AND id != :exclude_id";
    }
    
    $stmt = $pdo->prepare($sql);
    $params = [
        ':car_id' => $carId,
        ':pickup_date' => $pickupDate,
        ':return_date' => $returnDate
    ];
    if ($excludeBookingId) {
        $params[':exclude_id'] = $excludeBookingId;
    }
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}

/**
 * Get car status badge HTML
 */
function statusBadge($status) {
    $classes = [
        'Available' => 'bg-success',
        'Reserved' => 'bg-warning text-dark',
        'Rented' => 'bg-primary',
        'Maintenance' => 'bg-secondary',
        'Inactive' => 'bg-dark',
        'Pending' => 'bg-warning text-dark',
        'Confirmed' => 'bg-info',
        'Active' => 'bg-primary',
        'Completed' => 'bg-success',
        'Cancelled' => 'bg-danger',
        'Draft' => 'bg-secondary',
        'Sent' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger'
    ];
    $class = $classes[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . e($status) . '</span>';
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * CSRF Token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get setting value
 */
function getSetting($pdo, $key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $settings[$key] ?? $default;
}

/**
 * Upload image with validation
 */
function uploadCarImage($file, $targetDir = 'assets/uploads/') {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, webp'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Max 5MB'];
    }
    
    // Verify image
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
        return ['success' => false, 'error' => 'Invalid image file'];
    }
    
    $filename = uniqid('car_') . '.' . $ext;
    $path = $targetDir . $filename;
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => true, 'path' => $path];
    }
    
    return ['success' => false, 'error' => 'Failed to save file'];
}

/**
 * Get available cars for search
 */
function searchAvailableCars($pdo, $filters = []) {
    $sql = "SELECT c.*, l.name as location_name 
            FROM cars c 
            LEFT JOIN locations l ON c.location_id = l.id 
            WHERE c.status = 'Available'";
    $params = [];
    
    if (!empty($filters['brand'])) {
        $sql .= " AND c.brand = :brand";
        $params[':brand'] = $filters['brand'];
    }
    if (!empty($filters['category'])) {
        $sql .= " AND c.category = :category";
        $params[':category'] = $filters['category'];
    }
    if (!empty($filters['transmission'])) {
        $sql .= " AND c.transmission = :transmission";
        $params[':transmission'] = $filters['transmission'];
    }
    if (!empty($filters['fuel_type'])) {
        $sql .= " AND c.fuel_type = :fuel_type";
        $params[':fuel_type'] = $filters['fuel_type'];
    }
    if (!empty($filters['seats'])) {
        $sql .= " AND c.seats >= :seats";
        $params[':seats'] = (int)$filters['seats'];
    }
    if (!empty($filters['min_price'])) {
        $sql .= " AND c.daily_price >= :min_price";
        $params[':min_price'] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= " AND c.daily_price <= :max_price";
        $params[':max_price'] = $filters['max_price'];
    }
    if (!empty($filters['location_id'])) {
        $sql .= " AND c.location_id = :location_id";
        $params[':location_id'] = $filters['location_id'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (c.brand LIKE :search OR c.model LIKE :search OR c.description LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    
    // Availability check for dates
    if (!empty($filters['pickup_date']) && !empty($filters['return_date'])) {
        $sql .= " AND c.id NOT IN (
            SELECT car_id FROM bookings 
            WHERE status NOT IN ('Cancelled', 'Completed')
            AND pickup_date <= :return_date AND return_date >= :pickup_date
        )";
        $params[':pickup_date'] = $filters['pickup_date'];
        $params[':return_date'] = $filters['return_date'];
    }
    
    $order = $filters['sort'] ?? 'daily_price ASC';
    $allowedSorts = ['daily_price ASC', 'daily_price DESC', 'year DESC', 'brand ASC'];
    if (!in_array($order, $allowedSorts)) {
        $order = 'daily_price ASC';
    }
    $sql .= " ORDER BY " . $order;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
