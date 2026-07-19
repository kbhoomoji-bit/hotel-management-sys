<!-- Admin Sidebar Navigation -->
<aside class="admin-sidebar shadow-sm">
    <div class="mb-4 text-center">
        <h6 class="text-uppercase text-light-50 fw-bold small tracking-wide">Main Management</h6>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="rooms.php" class="nav-link <?= (in_array($currentPage, ['rooms.php', 'room_add.php', 'room_edit.php'])) ? 'active' : '' ?>">
                <i class="fas fa-bed"></i> Room Management
            </a>
        </li>
        <li class="nav-item">
            <a href="bookings.php" class="nav-link <?= ($currentPage == 'bookings.php') ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> Booking System
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="nav-link <?= ($currentPage == 'customers.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Customer Directory
            </a>
        </li>
        <li class="nav-item">
            <a href="staff.php" class="nav-link <?= ($currentPage == 'staff.php') ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i> Staff Management
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i> Reports & Analytics
            </a>
        </li>
    </ul>

    <hr class="border-secondary my-4">

    <div class="px-2">
        <a href="../logout.php" class="btn btn-outline-danger w-100 btn-sm">
            <i class="fas fa-sign-out-alt me-2"></i> Log Out
        </a>
    </div>
</aside>

<!-- Admin Main Content Container Start -->
<main class="admin-content">
    <div class="container-fluid">
        <?= get_flash_message(); ?>
