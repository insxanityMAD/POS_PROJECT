<?php
declare(strict_types=1);
$pageTitle = 'Payroll';
$allowedRoles = ['Admin', 'Manager'];
require_once __DIR__ . '/includes/admin_header.php';

try {
    $payPeriods = $pdo->query("SELECT * FROM pay_periods ORDER BY start_date DESC")->fetchAll();
    $employees  = $pdo->query(
        "SELECT u.user_id, u.full_name, r.role_name FROM users u JOIN roles r ON r.role_id = u.role_id
         WHERE u.status = 'Active' ORDER BY u.full_name"
    )->fetchAll();
    $payrollRecords = $pdo->query(
        "SELECT p.*, u.full_name, pp.period_name, pp.status AS period_status
         FROM payroll p
         JOIN users u ON u.user_id = p.user_id
         JOIN pay_periods pp ON pp.period_id = p.period_id
         ORDER BY p.payroll_id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Payroll page query failed: ' . $e->getMessage());
    $payPeriods = []; $employees = []; $payrollRecords = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Payroll</h1>
        <p>Process pay periods and view payslips.</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-process">Process Payroll</button>
    <button class="tab-btn" data-tab="tab-periods">Pay Periods</button>
    <button class="tab-btn" data-tab="tab-records">Payroll Records</button>
</div>

<!-- ================= PROCESS PAYROLL TAB ================= -->
<div id="tab-process" class="tab-panel active">
    <div class="pos-grid">
        <div class="panel">
            <div class="panel-head"><div><h2>Work Hours</h2><p>Select a pay period and employee, then enter hours worked.</p></div></div>

            <div class="form-row">
                <label>Pay Period</label>
                <select id="periodSelect">
                    <option value="">Select pay period</option>
                    <?php foreach ($payPeriods as $pp): if ($pp['status'] === 'Closed') continue; ?>
                        <option value="<?= $pp['period_id'] ?>">
                            <?= htmlspecialchars($pp['period_name'], ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars($pp['start_date'], ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($pp['end_date'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Employee</label>
                <select id="employeeSelect">
                    <option value="">Select employee</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['user_id'] ?>">
                            <?= htmlspecialchars($emp['full_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($emp['role_name'], ENT_QUOTES, 'UTF-8') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row"><label>Hourly Rate (₱)</label><input type="number" step="0.01" min="0" id="hourlyRate"></div>

            <div class="form-grid-2">
                <div class="form-row"><label>Regular Hours</label><input type="number" step="0.5" min="0" id="regularHours" value="0"></div>
                <div class="form-row"><label>Overtime Hours <span style="color:var(--text-gray); font-weight:400;">(x1.25 rate)</span></label><input type="number" step="0.5" min="0" id="overtimeHours" value="0"></div>
            </div>
            <div class="field-error" id="hoursWarning" style="display:none; margin-bottom:14px;">⚠️ This employee has 0 hours logged. Enter their actual worked hours before saving — payroll cannot be processed for someone who did not work.</div>

            <div class="calc-box">
                <div class="calc-row"><span>Gross Pay</span><span id="grossPayDisplay">₱0.00</span></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><div><h2>Deductions</h2><p>SSS, PhilHealth, Pag-IBIG, cash advance, etc.</p></div></div>

            <div id="deductionRows"></div>
            <button type="button" class="btn btn-outline btn-sm" id="addDeductionRow">+ Add Deduction</button>

            <div class="calc-box">
                <div class="calc-row"><span>Gross Pay</span><span id="grossPaySummary">₱0.00</span></div>
                <div class="calc-row"><span>Total Deductions</span><span class="neg" id="totalDeductionsDisplay">-₱0.00</span></div>
                <div class="calc-row net"><span>Net Pay</span><span id="netPayDisplay">₱0.00</span></div>
            </div>

            <div class="form-msg" id="payrollFormMsg"></div>
            <button class="btn btn-primary" id="savePayrollBtn" style="width:100%; justify-content:center;">Save Payroll Record</button>
        </div>
    </div>
</div>

<!-- ================= PAY PERIODS TAB ================= -->
<div id="tab-periods" class="tab-panel">
    <div class="section-actions">
        <input type="text" id="periodTableSearch" placeholder="🔍 Search pay periods..." style="max-width:280px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
        <button class="btn btn-primary" id="openAddPeriodModal">+ Add Pay Period</button>
    </div>
    <div class="panel">
        <?php if (empty($payPeriods)): ?>
            <p class="empty-note">No pay periods yet.</p>
        <?php else: ?>
        <p class="empty-note" id="noPeriodResults" style="display:none;">No pay periods match your search.</p>
        <table class="data-table" id="periodsTable">
            <thead><tr><th>Period</th><th>Start</th><th>End</th><th>Pay Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($payPeriods as $pp): ?>
                <tr data-search="<?= htmlspecialchars(mb_strtolower($pp['period_name']), ENT_QUOTES, 'UTF-8') ?>">
                    <td><?= htmlspecialchars($pp['period_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($pp['start_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($pp['end_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $pp['pay_date'] ? htmlspecialchars($pp['pay_date'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
                    <td><span class="pill <?= $pp['status'] === 'Open' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($pp['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                        <?php if ($pp['status'] === 'Open'): ?>
                            <button class="btn-danger-text js-close-period" data-id="<?= $pp['period_id'] ?>">Close Period</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= PAYROLL RECORDS TAB ================= -->
<div id="tab-records" class="tab-panel">
    <div class="section-actions">
        <input type="text" id="recordsTableSearch" placeholder="🔍 Search by employee or pay period..." style="max-width:320px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
        <div></div>
    </div>
    <div class="panel">
        <?php if (empty($payrollRecords)): ?>
            <p class="empty-note">No payroll records yet. Process one from the "Process Payroll" tab.</p>
        <?php else: ?>
        <p class="empty-note" id="noRecordsResults" style="display:none;">No payroll records match your search.</p>
        <table class="data-table" id="recordsTable">
            <thead><tr><th>Employee</th><th>Period</th><th>Hours</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($payrollRecords as $rec): ?>
                <tr data-search="<?= htmlspecialchars(mb_strtolower($rec['full_name'] . ' ' . $rec['period_name']), ENT_QUOTES, 'UTF-8') ?>">
                    <td><?= htmlspecialchars($rec['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($rec['period_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format((float)$rec['regular_hours'], 1) ?> reg + <?= number_format((float)$rec['overtime_hours'], 1) ?> OT</td>
                    <td>₱<?= number_format((float)$rec['gross_pay'], 2) ?></td>
                    <td>-₱<?= number_format((float)$rec['total_deductions'], 2) ?></td>
                    <td><strong>₱<?= number_format((float)$rec['net_pay'], 2) ?></strong></td>
                    <td><button class="btn btn-outline btn-sm js-view-payroll" data-id="<?= $rec['payroll_id'] ?>">View</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= ADD PAY PERIOD MODAL ================= -->
<div class="modal-overlay" id="periodModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Add Pay Period</h2>
            <button class="modal-close js-close-modal" data-modal="periodModal">&times;</button>
        </div>
        <div class="form-msg" id="periodFormMsg"></div>
        <form id="periodForm">
            <div class="form-row"><label>Period Name</label><input type="text" id="p_period_name" placeholder="e.g. Sept 1-15, 2026" required></div>
            <div class="form-grid-2">
                <div class="form-row"><label>Start Date</label><input type="date" id="p_start_date" required></div>
                <div class="form-row"><label>End Date</label><input type="date" id="p_end_date" required></div>
            </div>
            <div class="form-row"><label>Pay Date (optional)</label><input type="date" id="p_pay_date"></div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="periodModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Period</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= VIEW PAYROLL MODAL ================= -->
<div class="modal-overlay" id="viewPayrollModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Payslip</h2>
            <button class="modal-close js-close-modal" data-modal="viewPayrollModal">&times;</button>
        </div>
        <div id="payslipContent"></div>
    </div>
</div>

<!-- ================= PAYROLL SAVED SUCCESS MODAL ================= -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:10px;">✅</div>
        <h2 style="margin-bottom:8px;">Payroll Saved Successfully!</h2>
        <p style="color:var(--text-gray); font-size:13.5px; margin-bottom:20px;" id="successModalSub"></p>
        <div class="calc-box" style="text-align:left;">
            <div class="calc-row"><span>Gross Pay</span><span id="successGross"></span></div>
            <div class="calc-row"><span>Total Deductions</span><span class="neg" id="successDeductions"></span></div>
            <div class="calc-row net"><span>Net Pay</span><span id="successNet"></span></div>
        </div>
        <button class="btn btn-primary" id="successModalOk" style="width:100%; justify-content:center; margin-top:16px;">Done</button>
    </div>
</div>

<script>
// ---------- Table search helper ----------
function wireTableSearch(inputId, tableId, noResultsId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        let visibleCount = 0;
        rows.forEach(row => {
            const match = (row.dataset.search || '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        const noResults = document.getElementById(noResultsId);
        if (noResults) noResults.style.display = visibleCount === 0 && q !== '' ? 'block' : 'none';
    });
}
wireTableSearch('periodTableSearch', 'periodsTable', 'noPeriodResults');
wireTableSearch('recordsTableSearch', 'recordsTable', 'noRecordsResults');

// ---------- Tabs ----------
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.js-close-modal').forEach(btn => btn.addEventListener('click', () => closeModal(btn.dataset.modal)));
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

const money = n => '₱' + Number(n).toFixed(2);

// ---------- Gross / Net calculation ----------
const OT_MULTIPLIER = 1.25;

function calcGross() {
    const rate = parseFloat(document.getElementById('hourlyRate').value) || 0;
    const reg = parseFloat(document.getElementById('regularHours').value) || 0;
    const ot = parseFloat(document.getElementById('overtimeHours').value) || 0;
    return (rate * reg) + (rate * OT_MULTIPLIER * ot);
}

function getDeductions() {
    const rows = document.querySelectorAll('.deduction-row');
    const list = [];
    rows.forEach(row => {
        const name = row.querySelector('.ded-name').value.trim();
        const amount = parseFloat(row.querySelector('.ded-amount').value) || 0;
        if (name && amount > 0) list.push({ name, amount });
    });
    return list;
}

function recalc() {
    const gross = calcGross();
    const totalDed = getDeductions().reduce((s, d) => s + d.amount, 0);
    const net = gross - totalDed;

    document.getElementById('grossPayDisplay').textContent = money(gross);
    document.getElementById('grossPaySummary').textContent = money(gross);
    document.getElementById('totalDeductionsDisplay').textContent = '-' + money(totalDed);
    document.getElementById('netPayDisplay').textContent = money(net);

    checkHoursValidity();
}

function checkHoursValidity() {
    const reg = parseFloat(document.getElementById('regularHours').value) || 0;
    const ot = parseFloat(document.getElementById('overtimeHours').value) || 0;
    const rate = parseFloat(document.getElementById('hourlyRate').value) || 0;
    const noHours = reg <= 0 && ot <= 0;

    document.getElementById('hoursWarning').style.display = noHours ? 'block' : 'none';
    document.getElementById('savePayrollBtn').disabled = noHours || rate <= 0;
}

['hourlyRate', 'regularHours', 'overtimeHours'].forEach(id => {
    document.getElementById(id).addEventListener('input', recalc);
});
checkHoursValidity(); // run once on page load so the button starts correctly disabled

// ---------- Deduction rows ----------
function addDeductionRow(name = '', amount = '') {
    const wrap = document.getElementById('deductionRows');
    const row = document.createElement('div');
    row.className = 'deduction-row';
    row.innerHTML = `
        <input type="text" class="ded-name" placeholder="Deduction name (e.g. SSS)" value="${name}">
        <input type="number" step="0.01" min="0" class="ded-amount" placeholder="Amount" value="${amount}">
        <button type="button" class="remove-item">&times;</button>
    `;
    row.querySelector('.remove-item').addEventListener('click', () => { row.remove(); recalc(); });
    row.querySelectorAll('input').forEach(inp => inp.addEventListener('input', recalc));
    wrap.appendChild(row);
}
document.getElementById('addDeductionRow').addEventListener('click', () => addDeductionRow());
addDeductionRow(); // start with one blank row

// ---------- Save payroll record ----------
document.getElementById('savePayrollBtn').addEventListener('click', function () {
    const msg = document.getElementById('payrollFormMsg');
    msg.style.display = 'none';

    const periodId = document.getElementById('periodSelect').value;
    const userId = document.getElementById('employeeSelect').value;
    const hourlyRate = parseFloat(document.getElementById('hourlyRate').value) || 0;
    const regularHours = parseFloat(document.getElementById('regularHours').value) || 0;
    const overtimeHours = parseFloat(document.getElementById('overtimeHours').value) || 0;

    if (!periodId || !userId) {
        msg.textContent = 'Please select a pay period and an employee.';
        msg.className = 'form-msg error';
        showToast(msg.textContent);
        return;
    }
    if (hourlyRate <= 0 || (regularHours <= 0 && overtimeHours <= 0)) {
        msg.textContent = 'Enter a valid hourly rate and work hours.';
        msg.className = 'form-msg error';
        showToast(msg.textContent);
        return;
    }

    this.disabled = true;
    this.textContent = 'Saving...';

    fetch('payroll_actions.php?action=process_payroll', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            period_id: periodId,
            user_id: userId,
            hourly_rate: hourlyRate,
            regular_hours: regularHours,
            overtime_hours: overtimeHours,
            deductions: getDeductions(),
        }),
    })
    .then(r => r.json())
    .then(data => {
        this.disabled = false;
        this.textContent = 'Save Payroll Record';
        if (!data.success) {
            msg.textContent = data.message;
            msg.className = 'form-msg error';
            showToast(msg.textContent);
            return;
        }
        const empName = document.getElementById('employeeSelect').selectedOptions[0].textContent;
        const periodName = document.getElementById('periodSelect').selectedOptions[0].textContent;
        document.getElementById('successModalSub').textContent = empName + ' — ' + periodName;
        document.getElementById('successGross').textContent = money(data.summary.gross_pay);
        document.getElementById('successDeductions').textContent = '-' + money(data.summary.total_deductions);
        document.getElementById('successNet').textContent = money(data.summary.net_pay);
        openModal('successModal');
    })
    .catch(() => {
        this.disabled = false;
        this.textContent = 'Save Payroll Record';
        msg.textContent = 'Network error. Please try again.';
        msg.className = 'form-msg error';
        showToast(msg.textContent);
    });
});

document.getElementById('successModalOk').addEventListener('click', () => location.reload());

// ---------- Add Pay Period ----------
document.getElementById('openAddPeriodModal').addEventListener('click', () => {
    document.getElementById('periodForm').reset();
    document.getElementById('periodFormMsg').style.display = 'none';
    openModal('periodModal');
});

document.getElementById('periodForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('periodFormMsg');
    const fd = new FormData();
    fd.append('action', 'add_pay_period');
    fd.append('period_name', document.getElementById('p_period_name').value);
    fd.append('start_date', document.getElementById('p_start_date').value);
    fd.append('end_date', document.getElementById('p_end_date').value);
    fd.append('pay_date', document.getElementById('p_pay_date').value);

    fetch('payroll_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Close Pay Period ----------
document.querySelectorAll('.js-close-period').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!confirm('Close this pay period? It will no longer accept new payroll entries.')) return;
        const fd = new FormData();
        fd.append('action', 'close_pay_period');
        fd.append('period_id', btn.dataset.id);
        fetch('payroll_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});

// ---------- View Payslip ----------
document.querySelectorAll('.js-view-payroll').forEach(btn => {
    btn.addEventListener('click', () => {
        fetch('payroll_actions.php?action=get_payroll_details&payroll_id=' + btn.dataset.id)
            .then(r => r.json())
            .then(data => {
                if (!data.success) { alert(data.message); return; }
                const p = data.payroll;
                const dedRows = p.deductions.map(d => `
                    <div class="receipt-line"><span>${d.deduction_name}</span><span>-${money(d.amount)}</span></div>
                `).join('') || '<div class="empty-note">No deductions</div>';

                document.getElementById('payslipContent').innerHTML = `
                    <div class="receipt-box">
                        <div class="receipt-center"><strong>${p.full_name}</strong><br>${p.period_name}</div>
                        <hr>
                        <div class="receipt-line"><span>Hourly Rate</span><span>${money(p.hourly_rate)}</span></div>
                        <div class="receipt-line"><span>Regular Hours</span><span>${p.regular_hours}</span></div>
                        <div class="receipt-line"><span>Overtime Hours</span><span>${p.overtime_hours}</span></div>
                        <hr>
                        <div class="receipt-line"><strong>Gross Pay</strong><strong>${money(p.gross_pay)}</strong></div>
                        <hr>
                        ${dedRows}
                        <hr>
                        <div class="receipt-line"><strong>Total Deductions</strong><strong>-${money(p.total_deductions)}</strong></div>
                        <div class="receipt-line"><strong>Net Pay</strong><strong>${money(p.net_pay)}</strong></div>
                    </div>
                `;
                openModal('viewPayrollModal');
            });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
