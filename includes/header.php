<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Horizon Hotel - Luxury Stay & Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- Relative fallback for CSS path -->
    <script>
        // Ensure CSS resolves whether accessed from root or subfolder
        const links = document.getElementsByTagName('link');
        for (let link of links) {
            if (link.href.includes('/css/style.css') && window.location.pathname.includes('/customer/')) {
                link.href = '../css/style.css';
            }
        }
    </script>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">
            <i class="fas fa-hotel text-warning me-2"></i>Grand<span>Horizon</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>" href="/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'rooms.php') ? 'active' : '' ?>" href="/rooms.php">Rooms & Suites</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'about.php') ? 'active' : '' ?>" href="/about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == 'contact.php') ? 'active' : '' ?>" href="/contact.php">Contact</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="/admin/dashboard.php" class="btn btn-outline-accent btn-sm">
                            <i class="fas fa-user-shield me-1"></i> Admin Portal
                        </a>
                    <?php else: ?>
                        <a href="/customer/dashboard.php" class="btn btn-outline-accent btn-sm">
                            <i class="fas fa-user-circle me-1"></i> My Account
                        </a>
                    <?php endif; ?>
                    <a href="/logout.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-outline-light btn-sm px-3">Login</a>
                    <a href="/register.php" class="btn btn-accent btn-sm px-3">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <?= get_flash_message(); ?>
</div>
