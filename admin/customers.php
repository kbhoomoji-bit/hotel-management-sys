<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

// Handle customer deletion
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        $stmt_del = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
        if ($stmt_del->execute([$delete_id])) {
            set_flash_message('success', 'Customer account deleted successfully.');
        } else {
            set_flash_message('error', 'Failed to delete customer.');
        }
    }
    header('Location: customers.php');
    exit;
}

// Search
$search = sanitize($_GET['search'] ?? '');
$query = "SELECT u.*, 
    (SELECT COUNT(*) FROM bookings b WHERE b.customer_id = u.id) as total_bookings,
    (SELECT SUM(total_price) FROM bookings b WHERE b.customer_id = u.id AND status != 'Cancelled') as total_spent
    FROM users u 
    WHERE u.role = 'customer'";

$params = [];
if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$query .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll();

// Detailed view if view_id is provided
$selected_customer = null;
$customer_bookings = [];
$view_id = filter_input(INPUT_GET, 'view_id', FILTER_VALIDATE_INT);
if ($view_id) {
    $stmt_u = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
    $stmt_u->execute([$view_id]);
    $selected_customer = $stmt_u->fetch();

    if ($selected_customer) {
        $stmt_b = $pdo->prepare("SELECT b.*, r.room_number, r.room_type, p.payment_status, p.payment_method, p.transaction_id 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            LEFT JOIN payments p ON p.booking_id = b.id 
            WHERE b.customer_id = ? 
            ORDER BY b.created_at DESC");
        $stmt_b->execute([$view_id]);
        $customer_bookings = $stmt_b->fetchAll();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Customer Management</h2>
        <p class="text-muted mb-0">Search registered customers, inspect reservation records, and view payments</p>
    </div>
</div>

<?php if ($selected_customer): ?>
    <!-- Customer Details View Modal / Banner -->
    <div class="card card-custom p-4 mb-4 border-warning border-2 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0 text-navy"><i class="fas fa-user-circle text-warning me-2"></i> History for <?= htmlspecialchars($selected_customer['name']) ?> (<?= htmlspecialchars($selected_customer['email']) ?>)</h4>
            <a href="customers.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i> Close Details</a>
        </div>

        <h5 class="fw-bold fs-6 text-muted mb-3">Booking & Payment History</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking #</th>
                        <th>Room</th>
                        <th>Check-in / Out</th>
                        <th>Total Price</th>
                        <th>Booking Status</th>
                        <th>Payment Status</th>
                        <th>Txn ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customer_bookings)): ?>
                        <?php foreach ($customer_bookings as $cb): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cb['booking_no']) ?></strong></td>
                                <td>Room <?= htmlspecialchars($cb['room_number']) ?> (<?= htmlspecialchars($cb['room_type']) ?>)</td>
                                <td><?= date('M d', strtotime($cb['check_in'])) ?> - <?= date('M d, Y', strtotime($cb['check_out'])) ?></td>
                                <td><strong>$<?= number_format($cb['total_price'], 2) ?></strong></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($cb['status']) ?></span></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($cb['payment_status'] ?? 'Pending') ?></span></td>
                                <td><code><?= htmlspecialchars($cb['transaction_id'] ?? 'N/A') ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-3 text-muted">No booking history for this customer.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Search Card -->
<div class="card card-custom p-4 mb-4 shadow-sm border-0">
    <form action="customers.php" method="GET" class="row g-3 align-items-end">
        <div class="col-md-9">
            <label class="form-label fw-bold small text-uppercase text-muted">Search Customers</label>
            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name, email, or phone number">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-navy w-100 py-2 fw-bold"><i class="fas fa-search"></i> Search</button>
            <a href="customers.php" class="btn btn-outline-secondary py-2"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Customer Directory Table -->
<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Total Bookings</th>
                    <th>Total Spent</th>
                    <th>Registered Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><strong class="text-navy"><?= htmlspecialchars($c['name']) ?></strong></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?: 'N/A') ?></td>
                            <td><span class="badge bg-navy text-white fs-6"><?= $c['total_bookings'] ?></span></td>
                            <td><strong class="text-warning">$<?= number_format($c['total_spent'] ?: 0, 2) ?></strong></td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="customers.php?view_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info me-1">
                                    <i class="fas fa-eye"></i> View History
                                </a>
                                <a href="customers.php?delete_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete customer account <?= htmlspecialchars($c['name']) ?>?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No customer records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
</body>
</html>
