<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth.php';

// Enforce admin permission
requireAdmin();

$user = getLoggedInUser($pdo);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Grand Horizon Hotel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">

<!-- Admin Navbar Top Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-secondary">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="fas fa-hotel text-warning me-2"></i>Grand Horizon <span class="badge bg-warning text-dark fs-6 ms-2">Admin Panel</span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="../index.php" class="btn btn-outline-light btn-sm" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i> Visit Website
            </a>
            <div class="dropdown">
                <a class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" href="#" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_pic'] ?? 'default_avatar.png') ?>" alt="Admin Avatar" width="32" height="32" class="rounded-circle me-2 border border-warning">
                    <strong><?= htmlspecialchars($user['name'] ?? 'Admin') ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminDropdown">
                    <li><a class="dropdown-menu-item dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="admin-wrapper">
