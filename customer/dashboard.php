<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$user = getLoggedInUser($pdo);

// Fetch statistics
$stmt_b_count = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
$stmt_b_count->execute([$user['id']]);
$total_bookings = $stmt_b_count->fetchColumn();

$stmt_spent = $pdo->prepare("SELECT SUM(total_price) FROM bookings WHERE customer_id = ? AND status != 'Cancelled'");
$stmt_spent->execute([$user['id']]);
$total_spent = $stmt_spent->fetchColumn() ?: 0.00;

// Fetch upcoming / active booking
$stmt_active = $pdo->prepare("SELECT b.*, r.room_number, r.room_type 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.customer_id = ? AND b.status IN ('Confirmed', 'CheckedIn', 'Pending') 
    ORDER BY b.check_in ASC LIMIT 1");
$stmt_active->execute([$user['id']]);
$active_booking = $stmt_active->fetch();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">Welcome Back, <?= htmlspecialchars($user['name']) ?>!</h2>
            <p class="text-muted mb-0">Manage your reservations, view booking history, and update profile details.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="make_booking.php" class="btn btn-accent fw-bold"><i class="fas fa-plus me-1"></i> Book New Room</a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $total_bookings ?></div>
                    <div class="stat-title">Total Bookings</div>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="fas fa-bookmark"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <div class="stat-val">$<?= number_format($total_spent, 2) ?></div>
                    <div class="stat-title">Total Spent</div>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div>
                    <div class="stat-val"><?= $active_booking ? '1 Active' : '0 Active' ?></div>
                    <div class="stat-title">Active Reservation</div>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Active Booking Spotlight -->
        <div class="col-lg-8">
            <div class="card card-custom p-4 border-0 shadow-sm">
                <h4 class="fw-bold mb-4"><i class="fas fa-clock text-warning me-2"></i> Current & Upcoming Reservation</h4>
                
                <?php if ($active_booking): ?>
                    <div class="p-4 bg-light rounded-4 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-navy text-white fs-6">Booking #<?= htmlspecialchars($active_booking['booking_no']) ?></span>
                            <span class="badge-status badge-booked"><?= htmlspecialchars($active_booking['status']) ?></span>
                        </div>
                        
                        <div class="row g-3 my-2">
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Room Reserved</span>
                                <strong class="fs-5 text-dark">Room <?= htmlspecialchars($active_booking['room_number']) ?> (<?= htmlspecialchars($active_booking['room_type']) ?>)</strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Total Cost</span>
                                <strong class="fs-5 text-warning">$<?= number_format($active_booking['total_price'], 2) ?></strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Check-in Date</span>
                                <strong><i class="fas fa-calendar-check text-success me-1"></i> <?= date('M d, Y', strtotime($active_booking['check_in'])) ?></strong>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted small d-block">Check-out Date</span>
                                <strong><i class="fas fa-calendar-times text-danger me-1"></i> <?= date('M d, Y', strtotime($active_booking['check_out'])) ?></strong>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <a href="booking_history.php" class="btn btn-navy btn-sm"><i class="fas fa-list me-1"></i> View All Reservations</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-minus fs-1 mb-3 text-secondary"></i>
                        <p class="mb-3">You have no active or upcoming room reservations right now.</p>
                        <a href="make_booking.php" class="btn btn-accent btn-sm fw-bold">Make a Reservation</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Shortcuts -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 border-0 shadow-sm mb-4">
                <h5 class="fw-bold mb-3">Quick Navigation</h5>
                <div class="list-group list-group-flush">
                    <a href="booking_history.php" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <i class="fas fa-history text-warning me-3 fs-5"></i>
                        <div>
                            <div class="fw-bold">Booking History</div>
                            <div class="small text-muted">View past & current invoices</div>
                        </div>
                    </a>
                    <a href="profile.php" class="list-group-item list-group-item-action py-3 d-flex align-items-center">
                        <i class="fas fa-user-edit text-warning me-3 fs-5"></i>
                        <div>
                            <div class="fw-bold">My Profile</div>
                            <div class="small text-muted">Update details & password</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
