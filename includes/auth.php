<?php
require_once __DIR__ . '/db_connect.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return isLoggedIn() && (getUserRole() === 'admin' || getUserRole() === 'staff');
}

function isCustomer() {
    return isLoggedIn() && getUserRole() === 'customer';
}

function requireLogin() {
    if (!isLoggedIn()) {
        set_flash_message('error', 'Please login to access this page.');
        header('Location: ../login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        set_flash_message('error', 'Please login to access the admin area.');
        header('Location: ../login.php');
        exit;
    }
    if (!isAdmin()) {
        set_flash_message('error', 'Unauthorized access! Admin privileges required.');
        header('Location: ../index.php');
        exit;
    }
}

function requireCustomer() {
    if (!isLoggedIn()) {
        set_flash_message('error', 'Please login as a customer to proceed.');
        header('Location: ../login.php');
        exit;
    }
    if (!isCustomer()) {
        set_flash_message('error', 'You must be logged in as a customer.');
        header('Location: ../index.php');
        exit;
    }
}

function getLoggedInUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, profile_pic, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
