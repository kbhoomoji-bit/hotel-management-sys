<?php
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';

// Financial summary queries
$total_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Completed'")->fetchColumn() ?: 0.00;
$total_pending_revenue = $pdo->query("SELECT SUM(amount) FROM payments WHERE payment_status = 'Pending'")->fetchColumn() ?: 0.00;

// Revenue breakdown by payment method
$stmt_pm = $pdo->query("SELECT payment_method, SUM(amount) as total, COUNT(*) as count FROM payments WHERE payment_status = 'Completed' GROUP BY payment_method");
$payment_methods = $stmt_pm->fetchAll();

// Bookings by status
$stmt_bs = $pdo->query("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
$booking_statuses = $stmt_bs->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Financial & Occupancy Reports</h2>
        <p class="text-muted mb-0">Exportable operational summary and revenue analytics</p>
    </div>
    <button onclick="window.print()" class="btn btn-navy fw-bold"><i class="fas fa-print me-1"></i> Print / Export Report</button>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fas fa-coins text-warning me-2"></i> Revenue Breakdown by Payment Method</h5>
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Method</th>
                        <th>Transactions</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payment_methods)): ?>
                        <?php foreach ($payment_methods as $pm): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($pm['payment_method']) ?></strong></td>
                                <td><?= $pm['count'] ?></td>
                                <td><strong class="text-warning">$<?= number_format($pm['total'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-2 text-muted">No completed payments found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom p-4 border-0 shadow-sm">
            <h5 class="fw-bold mb-3"><i class="fas fa-chart-pie text-warning me-2"></i> Bookings Summary by Status</h5>
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Total Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $total_all_b = array_sum(array_column($booking_statuses, 'count')) ?: 1;
                    ?>
                    <?php if (!empty($booking_statuses)): ?>
                        <?php foreach ($booking_statuses as $bs): ?>
                            <?php $pct = round(($bs['count'] / $total_all_b) * 100, 1); ?>
                            <tr>
                                <td><strong class="text-navy"><?= htmlspecialchars($bs['status']) ?></strong></td>
                                <td><?= $bs['count'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:8px;">
                                            <div class="progress-bar bg-warning" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <span class="small fw-bold"><?= $pct ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</div>
</body>
</html>
