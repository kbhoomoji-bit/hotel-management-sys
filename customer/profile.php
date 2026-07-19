<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$user = getLoggedInUser($pdo);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors[] = 'Full Name is required.';
    }

    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        }
    }

    // Avatar upload handling
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_name = $_FILES['profile_pic']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $new_name = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $profile_pic = $new_name;
            }
        } else {
            $errors[] = 'Invalid avatar file format. Allowed: JPG, PNG, WEBP.';
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_pic = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $profile_pic, $hashed, $user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_pic = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $profile_pic, $user['id']]);
        }

        $_SESSION['name'] = $name;
        set_flash_message('success', 'Profile details updated successfully!');
        header('Location: profile.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-custom p-4 shadow-sm border-0">
                <h3 class="fw-bold mb-4"><i class="fas fa-user-cog text-warning me-2"></i> Account Profile Settings</h3>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_pic'] ?? 'default_avatar.png') ?>" alt="Profile Picture" width="100" height="100" class="rounded-circle border border-warning shadow-sm mb-3 object-fit-cover">
                        <div>
                            <label for="profile_pic" class="btn btn-outline-secondary btn-sm"><i class="fas fa-camera me-1"></i> Change Avatar</label>
                            <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address (Read-only)</label>
                        <input type="email" class="form-control bg-light" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold text-navy mb-3"><i class="fas fa-shield-alt text-warning me-2"></i> Change Password (Optional)</h5>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="btn btn-navy py-2 px-4 fw-bold w-100">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
