<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$user = getLoggedInUser($pdo);

// Handle booking cancellation
if (isset($_GET['cancel_id'])) {
    $cancel_id = filter_input(INPUT_GET, 'cancel_id', FILTER_VALIDATE_INT);
    if ($cancel_id) {
        $stmt_c = $pdo->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND customer_id = ? AND status = 'Pending'");
        if ($stmt_c->execute([$cancel_id, $user['id']])) {
            set_flash_message('success', 'Booking cancelled successfully.');
        }
    }
    header('Location: booking_history.php');
    exit;
}

// Fetch all bookings for logged-in user
$stmt = $pdo->prepare("SELECT b.*, r.room_number, r.room_type, p.payment_status, p.payment_method 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    LEFT JOIN payments p ON p.booking_id = b.id 
    WHERE b.customer_id = ? 
    ORDER BY b.created_at DESC");
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">My Booking History</h2>
            <p class="text-muted mb-0">Track all your current, past, and cancelled reservations</p>
        </div>
        <a href="make_booking.php" class="btn btn-accent fw-bold"><i class="fas fa-plus me-1"></i> New Booking</a>
    </div>

    <div class="card card-custom border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Booking #</th>
                        <th>Room Details</th>
                        <th>Dates</th>
                        <th>Guests</th>
                        <th>Total Price</th>
                        <th>Booking Status</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): ?>
                            <?php
                                $b_status = $b['status'];
                                $badge_b = 'bg-secondary';
                                if ($b_status === 'Confirmed') $badge_b = 'bg-primary';
                                if ($b_status === 'CheckedIn') $badge_b = 'bg-info text-dark';
                                if ($b_status === 'CheckedOut') $badge_b = 'bg-success';
                                if ($b_status === 'Cancelled') $badge_b = 'bg-danger';

                                $p_status = $b['payment_status'] ?? 'Pending';
                                $badge_p = ($p_status === 'Completed') ? 'badge-completed' : 'badge-pending';
                            ?>
                            <tr>
                                <td><strong class="text-navy"><?= htmlspecialchars($b['booking_no']) ?></strong></td>
                                <td>
                                    <strong>Room <?= htmlspecialchars($b['room_number']) ?></strong>
                                    <span class="d-block small text-muted"><?= htmlspecialchars($b['room_type']) ?></span>
                                </td>
                                <td class="small">
                                    <div><i class="fas fa-sign-in-alt text-success me-1"></i> <?= date('M d, Y', strtotime($b['check_in'])) ?></div>
                                    <div><i class="fas fa-sign-out-alt text-danger me-1"></i> <?= date('M d, Y', strtotime($b['check_out'])) ?></div>
                                </td>
                                <td class="small"><?= $b['adults'] ?> Adults, <?= $b['children'] ?> Children</td>
                                <td><strong class="text-warning fs-6">$<?= number_format($b['total_price'], 2) ?></strong></td>
                                <td><span class="badge <?= $badge_b ?>"><?= htmlspecialchars($b['status']) ?></span></td>
                                <td><span class="badge-status <?= $badge_p ?>"><?= htmlspecialchars($p_status) ?></span></td>
                                <td>
                                    <?php if ($b['status'] === 'Pending'): ?>
                                        <a href="booking_history.php?cancel_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                            Cancel
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">You have no reservation records yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
