<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Password reset instructions have been sent to your email address.';
        } else {
            $error = 'No account found with that email address.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <div class="stat-icon bg-warning-subtle text-warning mx-auto mb-2" style="width:60px; height:60px;">
                        <i class="fas fa-key fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Forgot Password</h3>
                    <p class="text-muted small">Enter your email to receive password reset instructions</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success small">
                        <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger small">
                        <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="forgot_password.php" method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-navy w-100 py-2 mb-3 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                    </button>

                    <div class="text-center text-muted small">
                        <a href="login.php" class="text-warning fw-bold"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
