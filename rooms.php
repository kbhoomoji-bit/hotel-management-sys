<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/header.php';

// Filter parameters
$search = sanitize($_GET['search'] ?? '');
$type = sanitize($_GET['type'] ?? '');
$status = sanitize($_GET['status'] ?? '');
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);

// Construct SQL query
$query = "SELECT * FROM rooms WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (room_number LIKE ? OR description LIKE ? OR room_type LIKE ?)";
    $params[] = "%{$search}%";
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

if ($max_price) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}

$query .= " ORDER BY price ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<div class="bg-dark text-white py-4 mb-4">
    <div class="container text-center">
        <h1 class="display-5 fw-bold mb-2">Our Rooms & Suites</h1>
        <p class="lead text-warning mb-0">Find the perfect room tailored to your style and budget</p>
    </div>
</div>

<div class="container py-3">
    <!-- Filter Bar -->
    <div class="card card-custom p-4 mb-5 shadow-sm border-0">
        <form action="rooms.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Search Room</label>
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Room # or keyword">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Room Type</label>
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
                <label class="form-label fw-bold small text-uppercase text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Available" <?= ($status === 'Available') ? 'selected' : '' ?>>Available</option>
                    <option value="Booked" <?= ($status === 'Booked') ? 'selected' : '' ?>>Booked</option>
                    <option value="Occupied" <?= ($status === 'Occupied') ? 'selected' : '' ?>>Occupied</option>
                    <option value="Maintenance" <?= ($status === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-navy w-100 py-2 fw-bold"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="rooms.php" class="btn btn-outline-secondary py-2"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>

    <!-- Rooms Display Grid -->
    <div class="row g-4">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <?php
                    $badge_class = 'badge-available';
                    if ($room['status'] === 'Booked') $badge_class = 'badge-booked';
                    if ($room['status'] === 'Occupied') $badge_class = 'badge-occupied';
                    if ($room['status'] === 'Maintenance') $badge_class = 'badge-maintenance';
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card card-custom h-100">
                        <div class="room-img-container">
                            <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80" alt="<?= htmlspecialchars($room['room_type']) ?>">
                            <div class="price-tag">$<?= number_format($room['price'], 2) ?><span class="fs-6 text-white-50">/night</span></div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-navy text-white px-3 py-1">Room <?= htmlspecialchars($room['room_number']) ?></span>
                                <span class="badge-status <?= $badge_class ?>"><?= htmlspecialchars($room['status']) ?></span>
                            </div>
                            <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($room['room_type']) ?> Room</h5>
                            <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($room['description']) ?></p>

                            <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center small text-muted">
                                <span><i class="fas fa-users me-1 text-warning"></i> Max <?= htmlspecialchars($room['capacity']) ?> Guests</span>
                                <span><i class="fas fa-layer-group me-1 text-warning"></i> Floor <?= htmlspecialchars($room['floor_number']) ?></span>
                            </div>

                            <?php if ($room['status'] === 'Available'): ?>
                                <a href="customer/make_booking.php?room_id=<?= $room['id'] ?>" class="btn btn-accent w-100 mt-3 fw-bold">
                                    <i class="fas fa-calendar-check me-1"></i> Book This Room
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 mt-3 fw-bold" disabled>
                                    <i class="fas fa-ban me-1"></i> Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="stat-icon bg-light text-muted mx-auto mb-3" style="width:70px; height:70px;">
                    <i class="fas fa-bed fs-2"></i>
                </div>
                <h4 class="fw-bold">No Rooms Found</h4>
                <p class="text-muted">Try adjusting your search filters or room type selections.</p>
                <a href="rooms.php" class="btn btn-accent mt-2">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
