<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

$room_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$room_id) {
    header('Location: rooms.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    set_flash_message('error', 'Room not found.');
    header('Location: rooms.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = sanitize($_POST['room_number'] ?? '');
    $room_type = sanitize($_POST['room_type'] ?? 'Standard');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
    $floor_number = filter_input(INPUT_POST, 'floor_number', FILTER_VALIDATE_INT);
    $description = sanitize($_POST['description'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Available');

    if (empty($room_number)) {
        $errors[] = 'Room number is required.';
    }
    if (!$price || $price <= 0) {
        $errors[] = 'Please enter a valid price.';
    }

    // Check duplicate room number
    $stmt_c = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ? AND id != ?");
    $stmt_c->execute([$room_number, $room_id]);
    if ($stmt_c->fetch()) {
        $errors[] = "Room number '{$room_number}' is already used by another room.";
    }

    $image_name = $room['image'];
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['room_image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $image_name = 'room_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/rooms/';
            move_uploaded_file($file_tmp, $upload_dir . $image_name);
        }
    }

    if (empty($errors)) {
        $stmt_u = $pdo->prepare("UPDATE rooms SET room_number = ?, room_type = ?, price = ?, capacity = ?, floor_number = ?, description = ?, image = ?, status = ? WHERE id = ?");
        if ($stmt_u->execute([$room_number, $room_type, $price, $capacity, $floor_number, $description, $image_name, $status, $room_id])) {
            set_flash_message('success', "Room {$room_number} updated successfully!");
            header('Location: rooms.php');
            exit;
        } else {
            $errors[] = 'Database update failed.';
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Edit Room <?= htmlspecialchars($room['room_number']) ?></h2>
        <p class="text-muted mb-0">Modify room details, pricing, and availability status</p>
    </div>
    <a href="rooms.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Rooms</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom p-4 shadow-sm border-0">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="room_edit.php?id=<?= $room['id'] ?>" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="room_number" class="form-label fw-bold">Room Number</label>
                        <input type="text" class="form-control" id="room_number" name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="room_type" class="form-label fw-bold">Room Type</label>
                        <select name="room_type" id="room_type" class="form-select" required>
                            <option value="Standard" <?= ($room['room_type'] === 'Standard') ? 'selected' : '' ?>>Standard</option>
                            <option value="Deluxe" <?= ($room['room_type'] === 'Deluxe') ? 'selected' : '' ?>>Deluxe</option>
                            <option value="Suite" <?= ($room['room_type'] === 'Suite') ? 'selected' : '' ?>>Suite</option>
                            <option value="Family" <?= ($room['room_type'] === 'Family') ? 'selected' : '' ?>>Family</option>
                            <option value="Luxury" <?= ($room['room_type'] === 'Luxury') ? 'selected' : '' ?>>Luxury</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label fw-bold">Price / Night ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $room['price'] ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="capacity" class="form-label fw-bold">Capacity (Guests)</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" value="<?= $room['capacity'] ?>" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label for="floor_number" class="form-label fw-bold">Floor Number</label>
                        <input type="number" class="form-control" id="floor_number" name="floor_number" value="<?= $room['floor_number'] ?>" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Available" <?= ($room['status'] === 'Available') ? 'selected' : '' ?>>Available</option>
                            <option value="Booked" <?= ($room['status'] === 'Booked') ? 'selected' : '' ?>>Booked</option>
                            <option value="Occupied" <?= ($room['status'] === 'Occupied') ? 'selected' : '' ?>>Occupied</option>
                            <option value="Maintenance" <?= ($room['status'] === 'Maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="room_image" class="form-label fw-bold">Update Image</label>
                        <input type="file" class="form-control" id="room_image" name="room_image" accept="image/*">
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($room['description']) ?></textarea>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-navy py-2 px-4 fw-bold">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
</div>
</body>
</html>
