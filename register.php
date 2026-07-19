<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: customer/dashboard.php');
    }
    exit;
}

$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Full Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email address already exists.';
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')");
        
        if ($stmt->execute([$name, $email, $hashed_password, $phone])) {
            $user_id = $pdo->lastInsertId();
            
            // Set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'customer';

            set_flash_message('success', 'Registration successful! Welcome to Grand Horizon Hotel.');
            header('Location: customer/dashboard.php');
            exit;
        } else {
            $errors[] = 'Database error occurred. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <div class="stat-icon bg-warning-subtle text-warning mx-auto mb-2" style="width:60px; height:60px;">
                        <i class="fas fa-user-plus fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Create Account</h3>
                    <p class="text-muted small">Join Grand Horizon to book luxury rooms effortlessly</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Jane Doe" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-medium">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="+1 (555) 000-0000">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent w-100 py-2 mb-3 fw-bold">
                        <i class="fas fa-user-check me-2"></i> Register Account
                    </button>

                    <div class="text-center text-muted small">
                        Already have an account? <a href="login.php" class="text-warning fw-bold">Sign In here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
