<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/header.php';

// Fetch featured rooms from database
$stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'Available' ORDER BY price DESC LIMIT 3");
$featured_rooms = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="hero-title text-uppercase">Experience Unrivaled Luxury</h1>
        <p class="hero-subtitle">Discover breathtaking rooms, exceptional dining, and world-class service tailored just for you at Grand Horizon Hotel.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="rooms.php" class="btn btn-accent btn-lg px-4 py-3"><i class="fas fa-key me-2"></i>Explore Rooms</a>
            <a href="about.php" class="btn btn-outline-light btn-lg px-4 py-3"><i class="fas fa-compass me-2"></i>Learn More</a>
        </div>
    </div>
</section>

<!-- Quick Booking Search Bar -->
<div class="container position-relative" style="margin-top: -40px; z-index: 10;">
    <div class="card card-custom p-4 shadow-lg border-0">
        <form action="rooms.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Room Type</label>
                <select name="type" class="form-select">
                    <option value="">All Room Types</option>
                    <option value="Standard">Standard Room</option>
                    <option value="Deluxe">Deluxe Room</option>
                    <option value="Suite">Executive Suite</option>
                    <option value="Family">Family Suite</option>
                    <option value="Luxury">Luxury Penthouse</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Check-in Date</label>
                <input type="date" class="form-control" name="check_in" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-uppercase text-muted">Check-out Date</label>
                <input type="date" class="form-control" name="check_out" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-navy w-100 py-2 fw-bold">
                    <i class="fas fa-search me-2"></i> Check Availability
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Featured Rooms Section -->
<section class="py-5 mt-4">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold text-uppercase tracking-wide">Handcrafted Luxury</span>
            <h2 class="display-6 fw-bold">Featured Accommodations</h2>
            <p class="text-muted">Choose from our finest selection of curated rooms and luxury suites.</p>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_rooms)): ?>
                <?php foreach ($featured_rooms as $room): ?>
                    <div class="col-md-4">
                        <div class="card card-custom h-100">
                            <div class="room-img-container">
                                <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80" alt="<?= htmlspecialchars($room['room_type']) ?>">
                                <div class="price-tag">$<?= number_format($room['price'], 2) ?><span class="fs-6 text-white-50">/night</span></div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-navy text-white px-3 py-1">Room <?= htmlspecialchars($room['room_number']) ?></span>
                                    <span class="badge-status badge-available">Available</span>
                                </div>
                                <h5 class="card-title fw-bold mb-2"><?= htmlspecialchars($room['room_type']) ?> Suite</h5>
                                <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars(mb_strimwidth($room['description'], 0, 100, '...')) ?></p>
                                
                                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center small text-muted">
                                    <span><i class="fas fa-users me-1 text-warning"></i> Capacity: <?= htmlspecialchars($room['capacity']) ?> Guests</span>
                                    <span><i class="fas fa-building me-1 text-warning"></i> Floor <?= htmlspecialchars($room['floor_number']) ?></span>
                                </div>

                                <a href="customer/make_booking.php?room_id=<?= $room['id'] ?>" class="btn btn-accent w-100 mt-3 fw-bold">
                                    <i class="fas fa-bookmark me-1"></i> Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4 text-muted">
                    <p>No featured rooms available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-5">
            <a href="rooms.php" class="btn btn-outline-accent px-4 py-2 fw-bold">View All Rooms <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>

<!-- Amenities Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-warning fw-bold text-uppercase">Exclusive Features</span>
            <h2 class="display-6 fw-bold">World-Class Amenities</h2>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-swimming-pool fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">Infinity Pool</h5>
                    <p class="small text-muted mb-0">Rooftop heated swimming pool with panoramic horizon views.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-utensils fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">Gourmet Dining</h5>
                    <p class="small text-muted mb-0">Michelin-starred culinary experiences prepared by top chefs.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-spa fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">Wellness Spa</h5>
                    <p class="small text-muted mb-0">Holistic body therapies, hydrotherapy, and sauna rooms.</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-4 rounded-4 bg-light h-100">
                    <i class="fas fa-wifi fs-1 text-warning mb-3"></i>
                    <h5 class="fw-bold">High-Speed Wi-Fi</h5>
                    <p class="small text-muted mb-0">Complimentary 1Gbps fiber optic internet in all rooms and lounges.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
