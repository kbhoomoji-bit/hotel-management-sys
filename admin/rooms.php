<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

// Handle room deletion
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        $stmt_del = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        if ($stmt_del->execute([$delete_id])) {
            set_flash_message('success', 'Room deleted successfully.');
        } else {
            set_flash_message('error', 'Failed to delete room.');
        }
    }
    header('Location: rooms.php');
    exit;
}

// Filters & Search
$search = sanitize($_GET['search'] ?? '');
$type = sanitize($_GET['type'] ?? '');
$status = sanitize($_GET['status'] ?? '');

$query = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (room_number LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($type)) {
    $query .= " AND room_type = ?";
    $params[] = $type;
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY room_number ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Room Management</h2>
        <p class="text-muted mb-0">View, search, filter, add, edit, or delete hotel rooms</p>
    </div>
    <a href="room_add.php" class="btn btn-accent fw-bold"><i class="fas fa-plus me-1"></i> Add New Room</a>
</div>

<!-- Search & Filter Card -->
<div class="card card-custom p-4 mb-4 shadow-sm border-0">
    <form action="rooms.php" method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold small text-uppercase text-muted">Search Room</label>
            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Room # or keyword">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small text-uppercase text-muted">Filter Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="Standard" <?= ($type === 'Standard') ? 'selected' : '' ?>>Standard</option>
                <option value="Deluxe" <?= ($type === 'Deluxe') ? 'selected' : '' ?>>Deluxe</option>
                <option value="Suite" <?= ($type === 'Suite') ? 'selected' : '' ?>>Suite</option>
                <option value="Family" <?= ($type === 'Family') ? 'selected' : '' ?>>Family</option>
                <option value="Luxury" <?= ($type === 'Luxury') ? 'selected' : '' ?>>Luxury</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small text-uppercase text-muted">Filter Status</label>
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="Available" <?= ($status === 'Available') ? 'selected' : '' ?>>Available</option>
                <option value="Booked" <?= ($status === 'Booked') ? 'selected' : '' ?>>Booked</option>
                <option value="Occupied" <?= ($status === 'Occupied') ? 'selected' : '' ?>>Occupied</option>
                <option value="Maintenance" <?= ($status === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-navy w-100 py-2 fw-bold"><i class="fas fa-search"></i> Search</button>
            <a href="rooms.php" class="btn btn-outline-secondary py-2"><i class="fas fa-undo"></i></a>
        </div>
    </form>
</div>

<!-- Rooms Table -->
<div class="card card-custom border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Room #</th>
                    <th>Type</th>
                    <th>Price / Night</th>
                    <th>Capacity</th>
                    <th>Floor</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $r): ?>
                        <?php
                            $badge_c = 'badge-available';
                            if ($r['status'] === 'Booked') $badge_c = 'badge-booked';
                            if ($r['status'] === 'Occupied') $badge_c = 'badge-occupied';
                            if ($r['status'] === 'Maintenance') $badge_c = 'badge-maintenance';
                        ?>
                        <tr>
                            <td><strong class="fs-6 text-navy">Room <?= htmlspecialchars($r['room_number']) ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($r['room_type']) ?></span></td>
                            <td><strong class="text-warning">$<?= number_format($r['price'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($r['capacity']) ?> Guests</td>
                            <td>Floor <?= htmlspecialchars($r['floor_number']) ?></td>
                            <td><span class="badge-status <?= $badge_c ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td>
                                <a href="room_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="rooms.php?delete_id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete Room <?= $r['room_number'] ?>?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No rooms found matching your search.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
</div>
</body>
</html>
