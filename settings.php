<?php
declare(strict_types=1);
$pageTitle = 'Settings';
require_once __DIR__ . '/includes/admin_header.php';

try {
    $taxRates = $pdo->query("SELECT * FROM tax_settings ORDER BY tax_rate")->fetchAll();
    $discountTypes = $pdo->query("SELECT * FROM discount_types ORDER BY discount_name")->fetchAll();
    $qrSettings = $pdo->query("SELECT * FROM qr_payment_settings ORDER BY payment_name")->fetchAll();
} catch (PDOException $e) {
    error_log('Settings page query failed: ' . $e->getMessage());
    $taxRates = []; $discountTypes = []; $qrSettings = [];
}

$activeTab = $_GET['tab'] ?? 'tax';
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Settings</h1>
        <p>System-wide configuration for tax rates, discounts, and QR payment display.</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn" data-tab="tab-tax">Tax Rates</button>
    <button class="tab-btn" data-tab="tab-discounts">Discounts</button>
    <button class="tab-btn" data-tab="tab-qr">QR Payments</button>
</div>

<!-- ================= TAX RATES TAB ================= -->
<div id="tab-tax" class="tab-panel">
    <div class="section-actions">
        <p style="color:var(--text-gray); font-size:13px; margin:0;">Marking a rate "Active" makes it the default pre-selected tax in POS. Only one can be active at a time.</p>
        <button class="btn btn-primary" id="openAddTaxModal">+ Add Tax Rate</button>
    </div>
    <div class="panel">
        <?php if (empty($taxRates)): ?>
            <p class="empty-note">No tax rates yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Rate</th><th>Active in POS</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($taxRates as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['tax_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= rtrim(rtrim(number_format((float)$t['tax_rate'], 2), '0'), '.') ?>%</td>
                    <td><span class="pill <?= $t['is_active'] ? 'active' : 'inactive' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn-outline btn-sm js-edit-tax"
                                data-id="<?= $t['tax_id'] ?>"
                                data-name="<?= htmlspecialchars($t['tax_name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-rate="<?= $t['tax_rate'] ?>"
                                data-active="<?= $t['is_active'] ?>"
                            >Edit</button>
                            <?php if (!$t['is_active']): ?>
                                <button class="btn-danger-text js-set-active-tax" data-id="<?= $t['tax_id'] ?>">Make Active</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= DISCOUNTS TAB ================= -->
<div id="tab-discounts" class="tab-panel">
    <div class="section-actions">
        <div></div>
        <button class="btn btn-primary" id="openAddDiscountModal">+ Add Discount Type</button>
    </div>
    <div class="panel">
        <?php if (empty($discountTypes)): ?>
            <p class="empty-note">No discount types yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Rate</th><th>Requires ID</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($discountTypes as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['discount_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= rtrim(rtrim(number_format((float)$d['discount_rate'], 2), '0'), '.') ?>%</td>
                    <td><?= $d['requires_id'] ? 'Yes' : 'No' ?></td>
                    <td><span class="pill <?= $d['is_active'] ? 'active' : 'inactive' ?>"><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn-outline btn-sm js-edit-discount"
                                data-id="<?= $d['discount_id'] ?>"
                                data-name="<?= htmlspecialchars($d['discount_name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-rate="<?= $d['discount_rate'] ?>"
                                data-requires="<?= $d['requires_id'] ?>"
                            >Edit</button>
                            <?php if ($d['is_active']): ?>
                                <button class="btn-danger-text js-toggle-discount" data-id="<?= $d['discount_id'] ?>" data-status="0">Deactivate</button>
                            <?php else: ?>
                                <button class="btn-danger-text js-toggle-discount" data-id="<?= $d['discount_id'] ?>" data-status="1">Reactivate</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= QR PAYMENTS TAB ================= -->
<div id="tab-qr" class="tab-panel">
    <p style="color:var(--text-gray); font-size:13px; margin-bottom:16px;">These details show to cashiers in POS when a customer pays via GCash, Maya, or Bank. Leave QR Image URL blank to show an auto-generated sample QR instead.</p>
    <div class="content-grid">
        <?php foreach ($qrSettings as $qr): ?>
        <div class="panel">
            <div class="panel-head"><div><h2><?= htmlspecialchars($qr['payment_name'], ENT_QUOTES, 'UTF-8') ?></h2></div></div>
            <form class="qr-settings-form" data-id="<?= $qr['qr_id'] ?>">
                <div class="form-row"><label>Account Name</label><input type="text" name="account_name" value="<?= htmlspecialchars((string)($qr['account_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="form-row"><label>Account Number</label><input type="text" name="account_number" value="<?= htmlspecialchars((string)($qr['account_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="form-row"><label>QR Image URL (optional)</label><input type="text" name="qr_image_path" value="<?= htmlspecialchars((string)($qr['qr_image_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://..."></div>
                <div class="form-row"><label class="checkbox-label"><input type="checkbox" name="is_active" <?= $qr['is_active'] ? 'checked' : '' ?>> Enabled in POS</label></div>
                <div class="form-msg qr-msg" style="display:none;"></div>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ================= ADD/EDIT TAX MODAL ================= -->
<div class="modal-overlay" id="taxModal">
    <div class="modal-box">
        <div class="modal-head"><h2 id="taxModalTitle">Add Tax Rate</h2><button class="modal-close js-close-modal" data-modal="taxModal">&times;</button></div>
        <div class="form-msg" id="taxFormMsg"></div>
        <form id="taxForm">
            <input type="hidden" id="t_action" value="add_tax">
            <input type="hidden" id="t_tax_id">
            <div class="form-row"><label>Tax Name</label><input type="text" id="t_tax_name" required></div>
            <div class="form-row"><label>Rate (%)</label><input type="number" step="0.01" min="0" id="t_tax_rate" required></div>
            <div class="form-row"><label class="checkbox-label"><input type="checkbox" id="t_is_active"> Set as active (default in POS)</label></div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="taxModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= ADD/EDIT DISCOUNT MODAL ================= -->
<div class="modal-overlay" id="discountModal">
    <div class="modal-box">
        <div class="modal-head"><h2 id="discountModalTitle">Add Discount Type</h2><button class="modal-close js-close-modal" data-modal="discountModal">&times;</button></div>
        <div class="form-msg" id="discountFormMsg"></div>
        <form id="discountForm">
            <input type="hidden" id="d_action" value="add_discount">
            <input type="hidden" id="d_discount_id">
            <div class="form-row"><label>Discount Name</label><input type="text" id="d_discount_name" required></div>
            <div class="form-row"><label>Rate (%)</label><input type="number" step="0.01" min="0" max="100" id="d_discount_rate" required></div>
            <div class="form-row"><label class="checkbox-label"><input type="checkbox" id="d_requires_id"> Requires an ID number at checkout</label></div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="discountModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
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

// ---------- Tabs (supports ?tab=xxx from dashboard quick-action links) ----------
const tabMap = { tax: 'tab-tax', discounts: 'tab-discounts', qr: 'tab-qr' };
const urlParams = new URLSearchParams(window.location.search);
const initialTab = tabMap[urlParams.get('tab')] || 'tab-tax';

function activateTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tabId));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.id === tabId));
}
activateTab(initialTab);

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.dataset.tab));
});

// ---------- Tax: Add ----------
document.getElementById('openAddTaxModal').addEventListener('click', () => {
    document.getElementById('taxForm').reset();
    document.getElementById('t_action').value = 'add_tax';
    document.getElementById('t_tax_id').value = '';
    document.getElementById('taxModalTitle').textContent = 'Add Tax Rate';
    document.getElementById('taxFormMsg').style.display = 'none';
    openModal('taxModal');
});

// ---------- Tax: Edit ----------
document.querySelectorAll('.js-edit-tax').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('t_action').value = 'edit_tax';
        document.getElementById('t_tax_id').value = btn.dataset.id;
        document.getElementById('t_tax_name').value = btn.dataset.name;
        document.getElementById('t_tax_rate').value = btn.dataset.rate;
        document.getElementById('t_is_active').checked = btn.dataset.active === '1';
        document.getElementById('taxModalTitle').textContent = 'Edit Tax Rate';
        document.getElementById('taxFormMsg').style.display = 'none';
        openModal('taxModal');
    });
});

document.getElementById('taxForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('taxFormMsg');
    const fd = new FormData();
    fd.append('action', document.getElementById('t_action').value);
    fd.append('tax_id', document.getElementById('t_tax_id').value);
    fd.append('tax_name', document.getElementById('t_tax_name').value);
    fd.append('tax_rate', document.getElementById('t_tax_rate').value);
    if (document.getElementById('t_is_active').checked) fd.append('is_active', '1');

    fetch('settings_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.href = 'settings.php?tab=tax'; }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Tax: Make Active ----------
document.querySelectorAll('.js-set-active-tax').forEach(btn => {
    btn.addEventListener('click', () => {
        const fd = new FormData();
        fd.append('action', 'toggle_tax');
        fd.append('tax_id', btn.dataset.id);
        fd.append('is_active', '1');
        fetch('settings_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.href = 'settings.php?tab=tax'; else alert(data.message); });
    });
});

// ---------- Discount: Add ----------
document.getElementById('openAddDiscountModal').addEventListener('click', () => {
    document.getElementById('discountForm').reset();
    document.getElementById('d_action').value = 'add_discount';
    document.getElementById('d_discount_id').value = '';
    document.getElementById('discountModalTitle').textContent = 'Add Discount Type';
    document.getElementById('discountFormMsg').style.display = 'none';
    openModal('discountModal');
});

// ---------- Discount: Edit ----------
document.querySelectorAll('.js-edit-discount').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('d_action').value = 'edit_discount';
        document.getElementById('d_discount_id').value = btn.dataset.id;
        document.getElementById('d_discount_name').value = btn.dataset.name;
        document.getElementById('d_discount_rate').value = btn.dataset.rate;
        document.getElementById('d_requires_id').checked = btn.dataset.requires === '1';
        document.getElementById('discountModalTitle').textContent = 'Edit Discount Type';
        document.getElementById('discountFormMsg').style.display = 'none';
        openModal('discountModal');
    });
});

document.getElementById('discountForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('discountFormMsg');
    const fd = new FormData();
    fd.append('action', document.getElementById('d_action').value);
    fd.append('discount_id', document.getElementById('d_discount_id').value);
    fd.append('discount_name', document.getElementById('d_discount_name').value);
    fd.append('discount_rate', document.getElementById('d_discount_rate').value);
    if (document.getElementById('d_requires_id').checked) fd.append('requires_id', '1');

    fetch('settings_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.href = 'settings.php?tab=discounts'; }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Discount: Deactivate/Reactivate ----------
document.querySelectorAll('.js-toggle-discount').forEach(btn => {
    btn.addEventListener('click', () => {
        const fd = new FormData();
        fd.append('action', 'toggle_discount_status');
        fd.append('discount_id', btn.dataset.id);
        fd.append('new_status', btn.dataset.status);
        fetch('settings_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.href = 'settings.php?tab=discounts'; else alert(data.message); });
    });
});

// ---------- QR Settings: Save ----------
document.querySelectorAll('.qr-settings-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const msg = this.querySelector('.qr-msg');
        const btn = this.querySelector('button[type=submit]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const fd = new FormData();
        fd.append('action', 'update_qr');
        fd.append('qr_id', this.dataset.id);
        fd.append('account_name', this.account_name.value);
        fd.append('account_number', this.account_number.value);
        fd.append('qr_image_path', this.qr_image_path.value);
        if (this.is_active.checked) fd.append('is_active', '1');

        fetch('settings_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    btn.textContent = '✅ Saved!';
                    msg.style.display = 'none';
                    setTimeout(() => { btn.textContent = originalText; }, 2000);
                } else {
                    btn.textContent = originalText;
                    msg.textContent = data.message;
                    msg.className = 'form-msg error';
                    showToast(msg.textContent);
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.textContent = originalText;
                msg.textContent = 'Network error. Please try again.';
                msg.className = 'form-msg error';
                showToast(msg.textContent);
            });
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
