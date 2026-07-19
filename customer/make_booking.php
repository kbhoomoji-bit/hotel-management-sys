<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$user = getLoggedInUser($pdo);
$selected_room_id = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);
$errors = [];

// Fetch all available rooms for dropdown selection
$stmt_rooms = $pdo->query("SELECT * FROM rooms WHERE status != 'Maintenance' ORDER BY room_type, price ASC");
$all_rooms = $stmt_rooms->fetchAll();

// If room ID provided, fetch room details
$selected_room = null;
if ($selected_room_id) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$selected_room_id]);
    $selected_room = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $check_in = sanitize($_POST['check_in'] ?? '');
    $check_out = sanitize($_POST['check_out'] ?? '');
    $adults = filter_input(INPUT_POST, 'adults', FILTER_VALIDATE_INT) ?: 1;
    $children = filter_input(INPUT_POST, 'children', FILTER_VALIDATE_INT) ?: 0;
    $payment_method = sanitize($_POST['payment_method'] ?? 'Credit Card');

    // Validation
    if (!$room_id) {
        $errors[] = 'Please select a valid room.';
    }
    if (empty($check_in) || empty($check_out)) {
        $errors[] = 'Check-in and Check-out dates are required.';
    } elseif (strtotime($check_out) <= strtotime($check_in)) {
        $errors[] = 'Check-out date must be after Check-in date.';
    } elseif (strtotime($check_in) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Check-in date cannot be in the past.';
    }

    // Double Booking Check
    if (empty($errors)) {
        $stmt_overlap = $pdo->prepare("SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
            AND status IN ('Confirmed', 'CheckedIn', 'Pending') 
            AND (
                (check_in <= ? AND check_out >= ?) OR
                (check_in <= ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )");
        $stmt_overlap->execute([
            $room_id,
            $check_in, $check_in,
            $check_out, $check_out,
            $check_in, $check_out
        ]);
        if ($stmt_overlap->fetchColumn() > 0) {
            $errors[] = 'Sorry, this room is already booked for the selected dates. Please choose different dates or another room.';
        }
    }

    // Execute Reservation Insertion
    if (empty($errors)) {
        // Fetch target room details to calculate pricing
        $stmt_r = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt_r->execute([$room_id]);
        $target_room = $stmt_r->fetch();

        $diffDays = ceil(abs(strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24));
        $total_price = $diffDays * $target_room['price'];
        $booking_no = 'BK-' . date('Y') . '-' . rand(1000, 9999);
        $payment_no = 'PAY-' . date('Y') . '-' . rand(1000, 9999);
        $txn_id = 'TXN-' . strtoupper(substr(md5(time()), 0, 8));

        $pdo->beginTransaction();

        try {
            // Insert Booking
            $stmt_b = $pdo->prepare("INSERT INTO bookings (booking_no, customer_id, room_id, check_in, check_out, adults, children, total_days, total_price, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
            $stmt_b->execute([$booking_no, $user['id'], $room_id, $check_in, $check_out, $adults, $children, $diffDays, $total_price]);
            $booking_id = $pdo->lastInsertId();

            // Insert Payment
            $stmt_p = $pdo->prepare("INSERT INTO payments (booking_id, payment_no, amount, payment_method, payment_status, transaction_id) 
                VALUES (?, ?, ?, ?, 'Completed', ?)");
            $stmt_p->execute([$booking_id, $payment_no, $total_price, $payment_method, $txn_id]);

            // Update Room status if check-in is today
            if ($check_in === date('Y-m-d')) {
                $stmt_u = $pdo->prepare("UPDATE rooms SET status = 'Occupied' WHERE id = ?");
            } else {
                $stmt_u = $pdo->prepare("UPDATE rooms SET status = 'Booked' WHERE id = ?");
            }
            $stmt_u->execute([$room_id]);

            $pdo->commit();

            set_flash_message('success', "Reservation {$booking_no} created successfully! Total paid: $" . number_format($total_price, 2));
            header('Location: booking_history.php');
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Reservation failed: ' . $ex->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <h3 class="fw-bold mb-0"><i class="fas fa-calendar-plus text-warning me-2"></i> Book Your Stay</h3>
                    <a href="../rooms.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Rooms</a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="make_booking.php" method="POST" id="bookingForm">
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="room_id" class="form-label fw-bold">Select Room & Suite</label>
                            <select name="room_id" id="room_id" class="form-select form-select-lg" required>
                                <option value="">-- Choose a Room --</option>
                                <?php foreach ($all_rooms as $r): ?>
                                    <?php 
                                        $selected = ($selected_room && $selected_room['id'] == $r['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $r['id'] ?>" data-price="<?= $r['price'] ?>" <?= $selected ?>>
                                        Room <?= htmlspecialchars($r['room_number']) ?> - <?= htmlspecialchars($r['room_type']) ?> ($<?= number_format($r['price'], 2) ?>/night - Max <?= $r['capacity'] ?> Guests)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="room_price" value="<?= $selected_room['price'] ?? 0 ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="check_in" class="form-label fw-bold">Check-in Date</label>
                            <input type="date" class="form-control" id="check_in" name="check_in" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="check_out" class="form-label fw-bold">Check-out Date</label>
                            <input type="date" class="form-control" id="check_out" name="check_out" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="adults" class="form-label fw-bold">Adults</label>
                            <select name="adults" id="adults" class="form-select">
                                <option value="1">1 Adult</option>
                                <option value="2" selected>2 Adults</option>
                                <option value="3">3 Adults</option>
                                <option value="4">4 Adults</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="children" class="form-label fw-bold">Children</label>
                            <select name="children" id="children" class="form-select">
                                <option value="0" selected>0 Children</option>
                                <option value="1">1 Child</option>
                                <option value="2">2 Children</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="payment_method" class="form-label fw-bold">Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select">
                                <option value="Credit Card">Credit Card (Instant Confirmation)</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Cash at Check-in">Pay Cash at Check-in</option>
                            </select>
                        </div>
                    </div>

                    <!-- Availability status banner -->
                    <div id="availability_status" class="mb-4"></div>

                    <!-- Summary Card -->
                    <div class="p-4 bg-light rounded-3 mb-4 border">
                        <h5 class="fw-bold text-navy mb-3"><i class="fas fa-receipt me-2"></i> Reservation Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Stay Duration:</span>
                            <strong id="total_days_display">1 Night(s)</strong>
                            <input type="hidden" name="total_days" id="total_days_input" value="1">
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="fs-5 fw-bold text-dark">Total Price:</span>
                            <span class="fs-4 fw-bold text-warning" id="total_price_display">$0.00</span>
                            <input type="hidden" name="total_price" id="total_price_input" value="0">
                        </div>
                    </div>

                    <button type="submit" id="btn_submit_booking" class="btn btn-accent btn-lg w-100 fw-bold">
                        <i class="fas fa-lock me-2"></i> Confirm & Pay Reservation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roomSelect = document.getElementById('room_id');
        const roomPriceInput = document.getElementById('room_price');

        if(roomSelect) {
            roomSelect.addEventListener('change', function() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const price = selectedOption.getAttribute('data-price') || 0;
                roomPriceInput.value = price;
                if (window.jQuery && $('#check_in').val()) {
                    $('#check_in').trigger('change');
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
