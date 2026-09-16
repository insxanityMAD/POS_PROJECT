<?php
declare(strict_types=1);
$pageTitle = 'Expenses';
$allowedRoles = ['Admin', 'Manager'];
require_once __DIR__ . '/includes/admin_header.php';

try {
    $expenses = $pdo->query(
        "SELECT e.*, u.full_name
         FROM expenses e
         JOIN users u ON u.user_id = e.user_id
         ORDER BY e.expense_date DESC, e.expense_id DESC"
    )->fetchAll();

    $thisMonthTotal = (float)$pdo->query(
        "SELECT COALESCE(SUM(amount),0) FROM expenses
         WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())"
    )->fetchColumn();

    $allTimeTotal = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM expenses")->fetchColumn();

    $categoryBreakdown = $pdo->query(
        "SELECT expense_category, SUM(amount) AS total
         FROM expenses
         WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())
         GROUP BY expense_category
         ORDER BY total DESC"
    )->fetchAll();

    $existingCategories = $pdo->query("SELECT DISTINCT expense_category FROM expenses ORDER BY expense_category")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('Expenses page query failed: ' . $e->getMessage());
    $expenses = []; $thisMonthTotal = 0; $allTimeTotal = 0; $categoryBreakdown = []; $existingCategories = [];
}

$maxCategoryTotal = 0;
foreach ($categoryBreakdown as $c) {
    $maxCategoryTotal = max($maxCategoryTotal, (float)$c['total']);
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Expenses</h1>
        <p>Log and review store expenses.</p>
    </div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
    <div class="stat-card">
        <div class="stat-card-top"><span class="label">This Month</span><div class="stat-icon">₱</div></div>
        <div class="stat-value">₱<?= number_format($thisMonthTotal, 2) ?></div>
        <div class="stat-sub">Total logged this month</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top"><span class="label">All Time</span><div class="stat-icon blue">📊</div></div>
        <div class="stat-value">₱<?= number_format($allTimeTotal, 2) ?></div>
        <div class="stat-sub">Total expenses ever logged</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top"><span class="label">Top Category</span><div class="stat-icon purple">🏷️</div></div>
        <div class="stat-value" style="font-size:18px;"><?= !empty($categoryBreakdown) ? htmlspecialchars($categoryBreakdown[0]['expense_category'], ENT_QUOTES, 'UTF-8') : '—' ?></div>
        <div class="stat-sub"><?= !empty($categoryBreakdown) ? '₱' . number_format((float)$categoryBreakdown[0]['total'], 2) . ' this month' : 'No expenses yet' ?></div>
    </div>
</div>

<div class="content-grid" style="margin-bottom:22px;">
    <div class="panel">
        <div class="panel-head">
            <div><h2>Category Breakdown</h2><p>This month, by category</p></div>
        </div>
        <?php if (empty($categoryBreakdown)): ?>
            <p class="empty-note">No expenses logged this month yet.</p>
        <?php else: foreach ($categoryBreakdown as $c):
            $pct = $maxCategoryTotal > 0 ? ((float)$c['total'] / $maxCategoryTotal) * 100 : 0;
        ?>
            <div class="cat-breakdown-row">
                <div class="cat-breakdown-label">
                    <span><?= htmlspecialchars($c['expense_category'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span>₱<?= number_format((float)$c['total'], 2) ?></span>
                </div>
                <div class="cat-breakdown-bar-track"><div class="cat-breakdown-bar-fill" style="width:<?= $pct ?>%;"></div></div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="panel">
        <div class="panel-head"><div><h2>Quick Add</h2><p>Log a new expense</p></div></div>
        <form id="quickExpenseForm">
            <div class="form-row"><label>Category</label>
                <input type="text" id="qe_category" list="categoryList" placeholder="e.g. Utilities" required>
                <datalist id="categoryList">
                    <?php foreach ($existingCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-row"><label>Amount (₱)</label><input type="number" step="0.01" min="0.01" id="qe_amount" required></div>
            <div class="form-row"><label>Date</label><input type="date" id="qe_date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="form-row"><label>Description (optional)</label><input type="text" id="qe_description"></div>
            <div class="form-msg" id="quickExpenseMsg"></div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Add Expense</button>
        </form>
    </div>
</div>

<div class="section-actions">
    <input type="text" id="expenseTableSearch" placeholder="🔍 Search by category or description..." style="max-width:320px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
    <div></div>
</div>

<div class="panel">
    <?php if (empty($expenses)): ?>
        <p class="empty-note">No expenses logged yet.</p>
    <?php else: ?>
    <p class="empty-note" id="noExpenseResults" style="display:none;">No expenses match your search.</p>
    <div style="overflow-x:auto;">
    <table class="data-table" id="expensesTable">
        <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Logged By</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($expenses as $e): ?>
            <tr data-search="<?= htmlspecialchars(mb_strtolower($e['expense_category'] . ' ' . ($e['description'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                <td><?= htmlspecialchars($e['expense_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($e['expense_category'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($e['description'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td>₱<?= number_format((float)$e['amount'], 2) ?></td>
                <td><?= htmlspecialchars($e['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn-outline btn-sm js-edit-expense"
                            data-id="<?= $e['expense_id'] ?>"
                            data-category="<?= htmlspecialchars($e['expense_category'], ENT_QUOTES, 'UTF-8') ?>"
                            data-description="<?= htmlspecialchars((string)($e['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            data-amount="<?= $e['amount'] ?>"
                            data-date="<?= $e['expense_date'] ?>"
                        >Edit</button>
                        <button class="btn-danger-text js-delete-expense" data-id="<?= $e['expense_id'] ?>">Delete</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ================= EDIT EXPENSE MODAL ================= -->
<div class="modal-overlay" id="editExpenseModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Edit Expense</h2>
            <button class="modal-close js-close-modal" data-modal="editExpenseModal">&times;</button>
        </div>
        <div class="form-msg" id="editExpenseMsg"></div>
        <form id="editExpenseForm">
            <input type="hidden" id="ee_id">
            <div class="form-row"><label>Category</label><input type="text" id="ee_category" list="categoryList" required></div>
            <div class="form-row"><label>Amount (₱)</label><input type="number" step="0.01" min="0.01" id="ee_amount" required></div>
            <div class="form-row"><label>Date</label><input type="date" id="ee_date" required></div>
            <div class="form-row"><label>Description (optional)</label><input type="text" id="ee_description"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="editExpenseModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.js-close-modal').forEach(btn => btn.addEventListener('click', () => closeModal(btn.dataset.modal)));
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

// ---------- Search ----------
document.getElementById('expenseTableSearch')?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const rows = document.querySelectorAll('#expensesTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const match = (row.dataset.search || '').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const noResults = document.getElementById('noExpenseResults');
    if (noResults) noResults.style.display = visible === 0 && q !== '' ? 'block' : 'none';
});

// ---------- Quick Add ----------
document.getElementById('quickExpenseForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('quickExpenseMsg');
    const fd = new FormData();
    fd.append('action', 'add_expense');
    fd.append('expense_category', document.getElementById('qe_category').value);
    fd.append('amount', document.getElementById('qe_amount').value);
    fd.append('expense_date', document.getElementById('qe_date').value);
    fd.append('description', document.getElementById('qe_description').value);

    fetch('expense_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                msg.textContent = data.message;
                msg.className = 'form-msg error';
                showToast(msg.textContent);
            }
        });
});

// ---------- Edit Expense ----------
document.querySelectorAll('.js-edit-expense').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ee_id').value = btn.dataset.id;
        document.getElementById('ee_category').value = btn.dataset.category;
        document.getElementById('ee_description').value = btn.dataset.description;
        document.getElementById('ee_amount').value = btn.dataset.amount;
        document.getElementById('ee_date').value = btn.dataset.date;
        document.getElementById('editExpenseMsg').style.display = 'none';
        openModal('editExpenseModal');
    });
});

document.getElementById('editExpenseForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('editExpenseMsg');
    const fd = new FormData();
    fd.append('action', 'edit_expense');
    fd.append('expense_id', document.getElementById('ee_id').value);
    fd.append('expense_category', document.getElementById('ee_category').value);
    fd.append('amount', document.getElementById('ee_amount').value);
    fd.append('expense_date', document.getElementById('ee_date').value);
    fd.append('description', document.getElementById('ee_description').value);

    fetch('expense_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Delete Expense ----------
document.querySelectorAll('.js-delete-expense').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!confirm('Delete this expense? This cannot be undone.')) return;
        const fd = new FormData();
        fd.append('action', 'delete_expense');
        fd.append('expense_id', btn.dataset.id);
        fetch('expense_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
