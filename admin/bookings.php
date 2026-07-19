<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

// Handle booking status updates
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = sanitize($_GET['action']);
    $booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

    if ($booking_id) {
        $stmt_b = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt_b->execute([$booking_id]);
        $booking = $stmt_b->fetch();

        if ($booking) {
            $pdo->beginTransaction();

            try {
                if ($action === 'confirm') {
                    $stmt_u = $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?");
                    $stmt_u->execute([$booking_id]);
                    set_flash_message('success', "Booking #{$booking['booking_no']} confirmed.");
                } elseif ($action === 'checkin') {
                    $stmt_u = $pdo->prepare("UPDATE bookings SET status = 'CheckedIn' WHERE id = ?");
                    $stmt_u->execute([$booking_id]);
                    // Mark room as occupied
                    $stmt_r = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = ?");
                    $stmt_r->execute([$booking['room_id']]);
                    set_flash_message('success', "Guest checked in for Booking #{$booking['booking_no']}. Room marked Occupied.");
                } elseif ($action === 'checkout') {
                    $stmt_u = $pdo->prepare("UPDATE bookings SET status = 'CheckedOut' WHERE id = ?");
                    $stmt_u->execute([$booking_id]);
                    // Mark room as available
                    $stmt_r = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?");
                    $stmt_r->execute([$booking['room_id']]);
                    // Mark payment completed
                    $stmt_p = $pdo->prepare("UPDATE payments SET payment_status = 'Completed' WHERE booking_id = ?");
                    $stmt_p->execute([$booking_id]);
                    set_flash_message('success', "Guest checked out for Booking #{$booking['booking_no']}. Room marked Available.");
                } elseif ($action === 'cancel') {
                    $stmt_u = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ?");
                    $stmt_u->execute([$booking_id]);
                    // Mark room as available
                    $stmt_r = $pdo->prepare("UPDATE rooms SET status = 'Available' WHERE id = ?");
                    $stmt_r->execute([$booking['room_id']]);
                    set_flash_message('info', "Booking #{$booking['booking_no']} cancelled.");
                }

                $pdo->commit();
            } catch (Exception $ex) {
                $pdo->rollBack();
                set_flash_message('error', 'Status update failed: ' . $ex->getMessage());
            }
        }
    }
    header('Location: bookings.php');
    exit;
}

// Filter parameters
$search = sanitize($_GET['search'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');

$query = "SELECT b.*, u.name as customer_name, u.email as customer_email, r.room_number, r.room_type, p.payment_status, p.payment_method 
    FROM bookings b 
    JOIN users u ON b.customer_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    LEFT JOIN payments p ON p.booking_id = b.id 
    WHERE 1=1";

$params = [];
if (!empty($search)) {
    $query .= " AND (b.booking_no LIKE ? OR u.name LIKE ? OR r.room_number LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($status_filter)) {
    $query .= " AND b.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Booking Management</h2>
        <p class="text-muted mb-0">Monitor reservations, process check-ins, check-outs, and cancellations</p>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom p-4 mb-4 shadow-sm border-0">
    <form action="bookings.php" method="GET" class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-bold small text-uppercase text-muted">Search Booking</label>
            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Booking #, customer name, or room #">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold small text-uppercase text-muted">Filter Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Pending" <?= ($status_filter === 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="Confirmed" <?= ($status_filter === 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                <option value="CheckedIn" <?= ($status_filter === 'CheckedIn') ? 'selected' : '' ?>>Checked-In</option>
                <option value="CheckedOut" <?= ($status_filter === 'CheckedOut') ? 'selected' : '' ?>>Checked-Out</option>
                <option value="Cancelled" <?= ($status_filter === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-navy w-100 py-2 fw-bold"><i class="fas fa-filter"></i> Filter</button>
            <a href="bookings.php" class="btn btn-outline-secondary py-2"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Bookings Master Table -->
<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Room</th>
                    <th>Check-in / Out</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $b): ?>
                        <?php
                            $st = $b['status'];
                            $badge = 'bg-secondary';
                            if ($st === 'Confirmed') $badge = 'bg-primary';
                            if ($st === 'CheckedIn') $badge = 'bg-info text-dark';
                            if ($st === 'CheckedOut') $badge = 'bg-success';
                            if ($st === 'Cancelled') $badge = 'bg-danger';

                            $pst = $b['payment_status'] ?? 'Pending';
                            $pbadge = ($pst === 'Completed') ? 'badge-completed' : 'badge-pending';
                        ?>
                        <tr>
                            <td><strong class="text-navy"><?= htmlspecialchars($b['booking_no']) ?></strong></td>
                            <td>
                                <div><strong><?= htmlspecialchars($b['customer_name']) ?></strong></div>
                                <div class="small text-muted"><?= htmlspecialchars($b['customer_email']) ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border">Room <?= htmlspecialchars($b['room_number']) ?> (<?= htmlspecialchars($b['room_type']) ?>)</span></td>
                            <td class="small">
                                <div><i class="fas fa-sign-in-alt text-success me-1"></i> <?= date('M d, Y', strtotime($b['check_in'])) ?></div>
                                <div><i class="fas fa-sign-out-alt text-danger me-1"></i> <?= date('M d, Y', strtotime($b['check_out'])) ?></div>
                            </td>
                            <td><strong class="text-warning">$<?= number_format($b['total_price'], 2) ?></strong></td>
                            <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($st) ?></span></td>
                            <td><span class="badge-status <?= $pbadge ?>"><?= htmlspecialchars($pst) ?></span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Update
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <?php if ($st === 'Pending'): ?>
                                            <li><a class="dropdown-item text-primary" href="bookings.php?action=confirm&booking_id=<?= $b['id'] ?>"><i class="fas fa-check me-2"></i> Confirm Booking</a></li>
                                        <?php endif; ?>
                                        <?php if ($st === 'Confirmed' || $st === 'Pending'): ?>
                                            <li><a class="dropdown-item text-info" href="bookings.php?action=checkin&booking_id=<?= $b['id'] ?>"><i class="fas fa-key me-2"></i> Check-in Guest</a></li>
                                        <?php endif; ?>
                                        <?php if ($st === 'CheckedIn'): ?>
                                            <li><a class="dropdown-item text-success" href="bookings.php?action=checkout&booking_id=<?= $b['id'] ?>"><i class="fas fa-door-closed me-2"></i> Check-out Guest</a></li>
                                        <?php endif; ?>
                                        <?php if ($st !== 'Cancelled' && $st !== 'CheckedOut'): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="bookings.php?action=cancel&booking_id=<?= $b['id'] ?>" onclick="return confirm('Cancel this reservation?');"><i class="fas fa-times me-2"></i> Cancel Booking</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No reservations found matching search.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
</body>
</html>
