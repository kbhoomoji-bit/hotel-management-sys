<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

// 1. Total Rooms
$total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();

// 2. Available Rooms
$available_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Available'")->fetchColumn();

// 3. Occupied Rooms
$occupied_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Occupied'")->fetchColumn();

// 4. Booked Rooms
$booked_rooms = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status = 'Booked'")->fetchColumn();

// 5. Total Customers
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// 6. Total Staff
$total_staff = $pdo->query("SELECT COUNT(*) FROM staff WHERE status = 'Active'")->fetchColumn();

// 7. Total Revenue (sum of completed payments)
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed'")->fetchColumn() ?: 0.00;

// 8. Today's Check-ins
$todays_checkins = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_in = CURRENT_DATE() AND status != 'Cancelled'")->fetchColumn();

// 9. Today's Check-outs
$todays_checkouts = $pdo->query("SELECT COUNT(*) FROM bookings WHERE check_out = CURRENT_DATE() AND status != 'Cancelled'")->fetchColumn();

// 10. Pending Payments
$pending_payments = $pdo->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'Pending'")->fetchColumn();

// Recent 5 Bookings
$stmt_recent = $pdo->query("SELECT b.*, u.name as customer_name, r.room_number, r.room_type 
    FROM bookings b 
    JOIN users u ON b.customer_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.created_at DESC LIMIT 5");
$recent_bookings = $stmt_recent->fetchAll();

// Graph Data: Monthly Booking Types Count
$stmt_b_types = $pdo->query("SELECT r.room_type, COUNT(b.id) as total_b 
    FROM rooms r 
    LEFT JOIN bookings b ON r.id = b.room_id 
    GROUP BY r.room_type");
$type_labels = [];
$type_counts = [];
while ($row = $stmt_b_types->fetch()) {
    $type_labels[] = $row['room_type'];
    $type_counts[] = (int)$row['total_b'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Executive Dashboard</h2>
        <p class="text-muted mb-0">Real-time overview of hotel operations, occupancy, and financial metrics</p>
    </div>
    <div>
        <a href="room_add.php" class="btn btn-accent btn-sm fw-bold me-2"><i class="fas fa-plus me-1"></i> Add Room</a>
        <a href="reports.php" class="btn btn-navy btn-sm fw-bold"><i class="fas fa-print me-1"></i> Reports</a>
    </div>
</div>

<!-- 10 Summary Metric Cards Grid -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Rooms -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $total_rooms ?></div>
                <div class="stat-title">Total Rooms</div>
            </div>
            <div class="stat-icon bg-navy text-white"><i class="fas fa-door-open"></i></div>
        </div>
    </div>

    <!-- Card 2: Available Rooms -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val text-success"><?= $available_rooms ?></div>
                <div class="stat-title">Available Rooms</div>
            </div>
            <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>

    <!-- Card 3: Occupied Rooms -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val text-warning"><?= $occupied_rooms ?></div>
                <div class="stat-title">Occupied Rooms</div>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-bed"></i></div>
        </div>
    </div>

    <!-- Card 4: Booked Rooms -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val text-primary"><?= $booked_rooms ?></div>
                <div class="stat-title">Booked Rooms</div>
            </div>
            <div class="stat-icon bg-primary-subtle text-primary"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>

    <!-- Card 5: Total Customers -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $total_customers ?></div>
                <div class="stat-title">Total Customers</div>
            </div>
            <div class="stat-icon bg-info-subtle text-info"><i class="fas fa-users"></i></div>
        </div>
    </div>

    <!-- Card 6: Total Staff -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $total_staff ?></div>
                <div class="stat-title">Total Staff</div>
            </div>
            <div class="stat-icon bg-dark-subtle text-dark"><i class="fas fa-user-tie"></i></div>
        </div>
    </div>

    <!-- Card 7: Total Revenue -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val text-accent" style="color: #C5A880;">$<?= number_format($total_revenue, 2) ?></div>
                <div class="stat-title">Total Revenue</div>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>

    <!-- Card 8: Today's Check-ins -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $todays_checkins ?></div>
                <div class="stat-title">Today's Check-ins</div>
            </div>
            <div class="stat-icon bg-success-subtle text-success"><i class="fas fa-sign-in-alt"></i></div>
        </div>
    </div>

    <!-- Card 9: Today's Check-outs -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?= $todays_checkouts ?></div>
                <div class="stat-title">Today's Check-outs</div>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-sign-out-alt"></i></div>
        </div>
    </div>

    <!-- Card 10: Pending Payments -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <div class="stat-val text-danger"><?= $pending_payments ?></div>
                <div class="stat-title">Pending Payments</div>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Revenue Graph -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-area text-warning me-2"></i> Revenue Overview Graph</h5>
            <div style="height: 280px; position: relative;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Booking Graph -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 border-0 shadow-sm h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar text-warning me-2"></i> Bookings by Room Type</h5>
            <div style="height: 280px; position: relative;">
                <canvas id="bookingChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-list me-2 text-warning"></i> Recent Bookings</h5>
        <a href="bookings.php" class="btn btn-outline-light btn-sm">View All Bookings</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer Name</th>
                    <th>Room #</th>
                    <th>Check-in / Check-out</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_bookings)): ?>
                    <?php foreach ($recent_bookings as $b): ?>
                        <tr>
                            <td><strong class="text-navy"><?= htmlspecialchars($b['booking_no']) ?></strong></td>
                            <td><?= htmlspecialchars($b['customer_name']) ?></td>
                            <td><span class="badge bg-light text-dark border">Room <?= htmlspecialchars($b['room_number']) ?></span></td>
                            <td class="small">
                                <?= date('M d', strtotime($b['check_in'])) ?> - <?= date('M d, Y', strtotime($b['check_out'])) ?>
                            </td>
                            <td><strong class="text-warning">$<?= number_format($b['total_price'], 2) ?></strong></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($b['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-3 text-muted">No recent bookings recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../js/admin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeLabels = <?= json_encode($type_labels) ?>;
        const typeCounts = <?= json_encode($type_counts) ?>;
        
        initAdminDashboardCharts(
            { labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'], values: [1200, 2400, 3200, 4800, 5600, 7200, 8900] },
            { labels: typeLabels, values: typeCounts }
        );
    });
</script>

</main>
</div>
</body>
</html>
