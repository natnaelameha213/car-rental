<?php
/**
 * Authentication Helpers
 * Car Rental Management System
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require user login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to continue.');
        redirect('/car-rental/login.php');
    }
}

/**
 * Require admin login
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        setFlash('warning', 'Admin access required.');
        redirect('/car-rental/admin/login.php');
    }
}

/**
 * Get current user
 */
function currentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get current admin
 */
function currentAdmin($pdo) {
    if (!isAdminLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

/**
 * Login user
 */
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        return true;
    }
    return false;
}

/**
 * Login admin
 */
function loginAdmin($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active'");
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        return true;
    }
    return false;
}

/**
 * Register user
 */
function registerUser($pdo, $data) {
    // Check email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email already registered.'];
    }
    
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, phone, password, address, city, license_number) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'] ?? null,
        $hash,
        $data['address'] ?? null,
        $data['city'] ?? null,
        $data['license_number'] ?? null
    ]);
    
    return ['success' => true, 'user_id' => $pdo->lastInsertId()];
}

/**
 * Logout
 */
function logoutUser() {
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
}

function logoutAdmin() {
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
}
?>
