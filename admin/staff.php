<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

$errors = [];

// Delete staff
if (isset($_GET['delete_id'])) {
    $delete_id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);
    if ($delete_id) {
        $stmt_d = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt_d->execute([$delete_id]);
        set_flash_message('success', 'Staff member deleted.');
    }
    header('Location: staff.php');
    exit;
}

// Add staff
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $designation = sanitize($_POST['designation'] ?? '');
    $salary = filter_input(INPUT_POST, 'salary', FILTER_VALIDATE_FLOAT);
    $status = sanitize($_POST['status'] ?? 'Active');

    if (empty($name) || empty($email) || empty($designation) || !$salary) {
        $errors[] = 'Please fill in all required fields accurately.';
    }

    if (empty($errors)) {
        // Check duplicate email
        $stmt_c = $pdo->prepare("SELECT id FROM staff WHERE email = ?");
        $stmt_c->execute([$email]);
        if ($stmt_c->fetch()) {
            $errors[] = 'Staff email already registered.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO staff (name, email, phone, designation, salary, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $designation, $salary, $status]);
            set_flash_message('success', "Staff member {$name} added successfully!");
            header('Location: staff.php');
            exit;
        }
    }
}

// Fetch staff members
$stmt = $pdo->query("SELECT * FROM staff ORDER BY created_at DESC");
$staff_members = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Staff Management</h2>
        <p class="text-muted mb-0">View hotel employees, manage designations, and monitor payroll</p>
    </div>
</div>

<div class="row g-4">
    <!-- Add Staff Form -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-3"><i class="fas fa-user-plus text-warning me-2"></i> Add Staff Member</h5>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger small">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="staff.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Full Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Robert Johnson" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="robert@hotel.com" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-bold">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000">
                </div>

                <div class="mb-3">
                    <label for="designation" class="form-label fw-bold">Designation *</label>
                    <input type="text" class="form-control" id="designation" name="designation" placeholder="e.g. Housekeeping Manager" required>
                </div>

                <div class="mb-3">
                    <label for="salary" class="form-label fw-bold">Monthly Salary ($) *</label>
                    <input type="number" step="0.01" class="form-control" id="salary" name="salary" placeholder="3500.00" required>
                </div>

                <div class="mb-4">
                    <label for="status" class="form-label fw-bold">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-navy w-100 py-2 fw-bold"><i class="fas fa-check-circle me-1"></i> Register Staff</button>
            </form>
        </div>
    </div>

    <!-- Staff List -->
    <div class="col-lg-8">
        <div class="card card-custom border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Staff Member</th>
                            <th>Designation</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($staff_members)): ?>
                            <?php foreach ($staff_members as $s): ?>
                                <tr>
                                    <td>
                                        <strong class="text-navy"><?= htmlspecialchars($s['name']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($s['email']) ?> | <?= htmlspecialchars($s['phone'] ?: 'N/A') ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['designation']) ?></span></td>
                                    <td><strong class="text-warning">$<?= number_format($s['salary'], 2) ?></strong></td>
                                    <td><span class="badge bg-<?= ($s['status'] === 'Active') ? 'success' : 'secondary' ?>"><?= htmlspecialchars($s['status']) ?></span></td>
                                    <td>
                                        <a href="staff.php?delete_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete staff member <?= htmlspecialchars($s['name']) ?>?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No staff members registered.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</main>
</div>
</body>
</html>
