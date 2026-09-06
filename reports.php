<?php
declare(strict_types=1);
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/admin_header.php';

// ---------- Shared date range filter (Sales / Discounted Sales / Losses / Income Statement) ----------
$dateFrom = $_GET['from'] ?? date('Y-m-01'); // start of this month
$dateTo   = $_GET['to'] ?? date('Y-m-d');    // today
$periodId = $_GET['period_id'] ?? '';

try {
    // ===== SALES REPORT =====
    $salesStmt = $pdo->prepare(
        "SELECT s.*, u.full_name
         FROM sales s JOIN users u ON u.user_id = s.user_id
         WHERE DATE(s.sale_date) BETWEEN ? AND ? AND s.sale_status = 'Completed'
         ORDER BY s.sale_date DESC"
    );
    $salesStmt->execute([$dateFrom, $dateTo]);
    $salesInRange = $salesStmt->fetchAll();

    $totalRevenue = 0.0; $totalItems = 0; $totalDiscountGiven = 0.0;
    $paymentBreakdown = [];
    foreach ($salesInRange as $s) {
        $totalRevenue += (float)$s['total_amount'];
        $totalDiscountGiven += (float)$s['discount_amount'];
    }
    $itemCountStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(si.quantity),0) FROM sale_items si
         JOIN sales s ON s.sale_id = si.sale_id
         WHERE DATE(s.sale_date) BETWEEN ? AND ? AND s.sale_status='Completed'"
    );
    $itemCountStmt->execute([$dateFrom, $dateTo]);
    $totalItems = (int)$itemCountStmt->fetchColumn();

    $paymentStmt = $pdo->prepare(
        "SELECT p.payment_method, COUNT(*) AS cnt, SUM(p.amount_paid) AS total
         FROM payments p JOIN sales s ON s.sale_id = p.sale_id
         WHERE DATE(s.sale_date) BETWEEN ? AND ? AND s.sale_status='Completed'
         GROUP BY p.payment_method"
    );
    $paymentStmt->execute([$dateFrom, $dateTo]);
    $paymentBreakdown = $paymentStmt->fetchAll();

    // ===== DISCOUNTED SALES REPORT =====
    $discSalesStmt = $pdo->prepare(
        "SELECT s.*, u.full_name, d.discount_name
         FROM sales s
         JOIN users u ON u.user_id = s.user_id
         LEFT JOIN discount_types d ON d.discount_id = s.discount_id
         WHERE DATE(s.sale_date) BETWEEN ? AND ? AND s.sale_status = 'Completed' AND s.discount_amount > 0
         ORDER BY s.sale_date DESC"
    );
    $discSalesStmt->execute([$dateFrom, $dateTo]);
    $discountedSales = $discSalesStmt->fetchAll();
    $totalDiscountedTxns = count($discountedSales);

    // ===== SALARY (PAYROLL) REPORT =====
    $payPeriods = $pdo->query("SELECT period_id, period_name FROM pay_periods ORDER BY start_date DESC")->fetchAll();

    $salaryWhere = '';
    $salaryParams = [];
    if ($periodId !== '') {
        $salaryWhere = 'WHERE p.period_id = ?';
        $salaryParams[] = $periodId;
    }
    $salaryStmt = $pdo->prepare(
        "SELECT p.*, u.full_name, pp.period_name
         FROM payroll p
         JOIN users u ON u.user_id = p.user_id
         JOIN pay_periods pp ON pp.period_id = p.period_id
         $salaryWhere
         ORDER BY p.payroll_id DESC"
    );
    $salaryStmt->execute($salaryParams);
    $payrollRows = $salaryStmt->fetchAll();

    $totalGross = 0.0; $totalDeductions = 0.0; $totalNet = 0.0;
    foreach ($payrollRows as $p) {
        $totalGross += (float)$p['gross_pay'];
        $totalDeductions += (float)$p['total_deductions'];
        $totalNet += (float)$p['net_pay'];
    }

    // ===== LOSSES REPORT =====
    $lossesStmt = $pdo->prepare(
        "SELECT l.*, pr.product_name, pr.cost_price, u.full_name
         FROM losses l
         JOIN products pr ON pr.product_id = l.product_id
         JOIN users u ON u.user_id = l.user_id
         WHERE DATE(l.loss_date) BETWEEN ? AND ?
         ORDER BY l.loss_date DESC"
    );
    $lossesStmt->execute([$dateFrom, $dateTo]);
    $losses = $lossesStmt->fetchAll();

    $totalUnitsLost = 0; $totalValueLost = 0.0; $reasonBreakdown = [];
    foreach ($losses as $l) {
        $totalUnitsLost += (int)$l['quantity'];
        $value = (float)$l['cost_price'] * (int)$l['quantity'];
        $totalValueLost += $value;
        $reasonBreakdown[$l['reason']] = ($reasonBreakdown[$l['reason']] ?? 0) + $value;
    }

    $lossProducts = $pdo->query("SELECT product_id, product_name, stock_quantity FROM products WHERE status='Active' ORDER BY product_name")->fetchAll();

    // ===== INCOME STATEMENT =====
    $revenueStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE DATE(sale_date) BETWEEN ? AND ? AND sale_status='Completed'"
    );
    $revenueStmt->execute([$dateFrom, $dateTo]);
    $revenue = (float)$revenueStmt->fetchColumn();

    $expensesStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ?"
    );
    $expensesStmt->execute([$dateFrom, $dateTo]);
    $expensesTotal = (float)$expensesStmt->fetchColumn();

    $netIncome = $revenue - $expensesTotal;

} catch (PDOException $e) {
    error_log('Reports page query failed: ' . $e->getMessage());
    $salesInRange = []; $totalRevenue = 0; $totalItems = 0; $totalDiscountGiven = 0; $paymentBreakdown = [];
    $discountedSales = []; $totalDiscountedTxns = 0;
    $payPeriods = []; $payrollRows = []; $totalGross = 0; $totalDeductions = 0; $totalNet = 0;
    $losses = []; $totalUnitsLost = 0; $totalValueLost = 0; $reasonBreakdown = []; $lossProducts = [];
    $revenue = 0; $expensesTotal = 0; $netIncome = 0;
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p>Sales, discounted sales, salary, losses, and income statement — all filterable.</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-sales">Sales</button>
    <button class="tab-btn" data-tab="tab-discounted">Discounted Sales</button>
    <button class="tab-btn" data-tab="tab-salary">Salary</button>
    <button class="tab-btn" data-tab="tab-losses">Losses</button>
    <button class="tab-btn" data-tab="tab-income">Income Statement</button>
</div>

<!-- Shared date filter for Sales / Discounted Sales / Losses / Income Statement -->
<form method="GET" class="date-filter-row" id="dateFilterForm">
    <div class="form-row"><label>From</label><input type="date" name="from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="form-row"><label>To</label><input type="date" name="to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>"></div>
    <button type="submit" class="btn btn-primary">Apply</button>
</form>

<!-- ================= SALES TAB ================= -->
<div id="tab-sales" class="tab-panel active">
    <div class="stat-grid" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Total Revenue</span><div class="stat-icon">₱</div></div>
            <div class="stat-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="stat-sub"><?= count($salesInRange) ?> transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Items Sold</span><div class="stat-icon blue">📦</div></div>
            <div class="stat-value"><?= number_format($totalItems) ?></div>
            <div class="stat-sub">Total units across all sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Avg. Transaction</span><div class="stat-icon purple">📊</div></div>
            <div class="stat-value">₱<?= number_format(count($salesInRange) > 0 ? $totalRevenue / count($salesInRange) : 0, 2) ?></div>
            <div class="stat-sub">Per completed sale</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Discounts Given</span><div class="stat-icon red">🏷️</div></div>
            <div class="stat-value">₱<?= number_format($totalDiscountGiven, 2) ?></div>
            <div class="stat-sub">Total discount amount</div>
        </div>
    </div>

    <div class="content-grid" style="margin-bottom:22px;">
        <div class="panel">
            <div class="panel-head"><div><h2>Sales in Range</h2><p><?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?></p></div></div>
            <?php if (empty($salesInRange)): ?>
                <p class="empty-note">No completed sales in this range.</p>
            <?php else: ?>
            <div style="overflow-x:auto; max-height:420px;">
            <table class="data-table">
                <thead><tr><th>Receipt #</th><th>Cashier</th><th>Date</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($salesInRange as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['receipt_number'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($s['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(date('M j, g:i A', strtotime($s['sale_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>₱<?= number_format((float)$s['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><div><h2>Payment Method Breakdown</h2></div></div>
            <?php if (empty($paymentBreakdown)): ?>
                <p class="empty-note">No payments in this range.</p>
            <?php else: foreach ($paymentBreakdown as $pb): ?>
                <div class="payment-breakdown-row">
                    <span><?= htmlspecialchars($pb['payment_method'], ENT_QUOTES, 'UTF-8') ?> (<?= (int)$pb['cnt'] ?>)</span>
                    <strong>₱<?= number_format((float)$pb['total'], 2) ?></strong>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<!-- ================= DISCOUNTED SALES TAB ================= -->
<div id="tab-discounted" class="tab-panel">
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Discounted Transactions</span><div class="stat-icon red">🏷️</div></div>
            <div class="stat-value"><?= $totalDiscountedTxns ?></div>
            <div class="stat-sub">In selected range</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Total Discount Given</span><div class="stat-icon">₱</div></div>
            <div class="stat-value">₱<?= number_format($totalDiscountGiven, 2) ?></div>
            <div class="stat-sub">Across all discounted sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">% of All Sales</span><div class="stat-icon purple">📊</div></div>
            <div class="stat-value"><?= count($salesInRange) > 0 ? number_format(($totalDiscountedTxns / count($salesInRange)) * 100, 1) : '0' ?>%</div>
            <div class="stat-sub">Of total transactions</div>
        </div>
    </div>

    <div class="panel">
        <?php if (empty($discountedSales)): ?>
            <p class="empty-note">No discounted sales in this range.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Receipt #</th><th>Date</th><th>Discount Type</th><th>Customer ID</th><th>Discount Amount</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($discountedSales as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['receipt_number'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(date('M j, Y', strtotime($s['sale_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['discount_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['customer_id_number'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>-₱<?= number_format((float)$s['discount_amount'], 2) ?></td>
                    <td>₱<?= number_format((float)$s['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= SALARY TAB ================= -->
<div id="tab-salary" class="tab-panel">
    <form method="GET" style="margin-bottom:18px; display:flex; gap:10px; align-items:end;">
        <input type="hidden" name="from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-row">
            <label>Pay Period</label>
            <select name="period_id">
                <option value="">All periods</option>
                <?php foreach ($payPeriods as $pp): ?>
                    <option value="<?= $pp['period_id'] ?>" <?= (string)$periodId === (string)$pp['period_id'] ? 'selected' : '' ?>><?= htmlspecialchars($pp['period_name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Total Gross Pay</span><div class="stat-icon">₱</div></div>
            <div class="stat-value">₱<?= number_format($totalGross, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Total Deductions</span><div class="stat-icon red">➖</div></div>
            <div class="stat-value">₱<?= number_format($totalDeductions, 2) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Total Net Pay</span><div class="stat-icon blue">💵</div></div>
            <div class="stat-value">₱<?= number_format($totalNet, 2) ?></div>
        </div>
    </div>

    <div class="panel">
        <?php if (empty($payrollRows)): ?>
            <p class="empty-note">No payroll records for this filter.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Employee</th><th>Period</th><th>Hours</th><th>Gross</th><th>Deductions</th><th>Net Pay</th></tr></thead>
            <tbody>
                <?php foreach ($payrollRows as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['period_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format((float)$p['regular_hours'], 1) ?> reg + <?= number_format((float)$p['overtime_hours'], 1) ?> OT</td>
                    <td>₱<?= number_format((float)$p['gross_pay'], 2) ?></td>
                    <td>-₱<?= number_format((float)$p['total_deductions'], 2) ?></td>
                    <td><strong>₱<?= number_format((float)$p['net_pay'], 2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= LOSSES TAB ================= -->
<div id="tab-losses" class="tab-panel">
    <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Units Lost</span><div class="stat-icon red">📉</div></div>
            <div class="stat-value"><?= number_format($totalUnitsLost) ?></div>
            <div class="stat-sub">In selected range</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Cost Value Lost</span><div class="stat-icon">₱</div></div>
            <div class="stat-value">₱<?= number_format($totalValueLost, 2) ?></div>
            <div class="stat-sub">At cost price</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-top"><span class="label">Top Reason</span><div class="stat-icon purple">🏷️</div></div>
            <?php arsort($reasonBreakdown); $topReason = array_key_first($reasonBreakdown); ?>
            <div class="stat-value" style="font-size:18px;"><?= $topReason ?? '—' ?></div>
            <div class="stat-sub"><?= $topReason ? '₱' . number_format($reasonBreakdown[$topReason], 2) : 'No losses yet' ?></div>
        </div>
    </div>

    <div class="content-grid" style="margin-bottom:22px;">
        <div class="panel">
            <div class="panel-head"><div><h2>Losses in Range</h2></div></div>
            <?php if (empty($losses)): ?>
                <p class="empty-note">No losses recorded in this range.</p>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Date</th><th>Product</th><th>Qty</th><th>Reason</th><th>Value</th></tr></thead>
                <tbody>
                    <?php foreach ($losses as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['loss_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($l['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int)$l['quantity'] ?></td>
                        <td><?= htmlspecialchars($l['reason'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>₱<?= number_format((float)$l['cost_price'] * (int)$l['quantity'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head"><div><h2>Record a Loss</h2><p>Expired, damaged, missing, or other shrinkage</p></div></div>
            <form id="lossForm">
                <div class="form-row">
                    <label>Product</label>
                    <select id="loss_product_id" required>
                        <option value="">Select product</option>
                        <?php foreach ($lossProducts as $lp): ?>
                            <option value="<?= $lp['product_id'] ?>"><?= htmlspecialchars($lp['product_name'], ENT_QUOTES, 'UTF-8') ?> (stock: <?= (int)$lp['stock_quantity'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid-2">
                    <div class="form-row"><label>Quantity</label><input type="number" min="1" id="loss_quantity" required></div>
                    <div class="form-row">
                        <label>Reason</label>
                        <select id="loss_reason" required>
                            <option value="Expired">Expired</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Missing">Missing</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row"><label>Remarks (optional)</label><input type="text" id="loss_remarks"></div>
                <div class="form-msg" id="lossFormMsg"></div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Record Loss</button>
            </form>
        </div>
    </div>
</div>

<!-- ================= INCOME STATEMENT TAB ================= -->
<div id="tab-income" class="tab-panel">
    <div class="panel" style="max-width:520px;">
        <div class="panel-head"><div><h2>Income Statement</h2><p><?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?> to <?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?></p></div></div>
        <div class="calc-box">
            <div class="calc-row"><span>Revenue (completed sales)</span><span>₱<?= number_format($revenue, 2) ?></span></div>
            <div class="calc-row"><span>Less: Expenses</span><span class="neg">-₱<?= number_format($expensesTotal, 2) ?></span></div>
            <div class="calc-row net"><span>Net Income</span><span style="color:<?= $netIncome >= 0 ? 'var(--green)' : 'var(--mrdiy-red)' ?>;">₱<?= number_format($netIncome, 2) ?></span></div>
        </div>
        <p style="color:var(--text-gray); font-size:12.5px; margin-top:12px;">Revenue = total of completed sales in range. Expenses = total logged in the Expenses module for the same range.</p>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

document.getElementById('lossForm')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('lossFormMsg');
    const fd = new FormData();
    fd.append('action', 'add_loss');
    fd.append('product_id', document.getElementById('loss_product_id').value);
    fd.append('quantity', document.getElementById('loss_quantity').value);
    fd.append('reason', document.getElementById('loss_reason').value);
    fd.append('remarks', document.getElementById('loss_remarks').value);

    fetch('report_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
