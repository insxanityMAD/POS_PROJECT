<?php
declare(strict_types=1);
$pageTitle = 'Suppliers';
$allowedRoles = ['Admin', 'Manager'];
require_once __DIR__ . '/../../includes/admin_header.php';

try {
    $suppliers = $pdo->query(
        "SELECT s.*, (SELECT COUNT(*) FROM product_suppliers ps WHERE ps.supplier_id = s.supplier_id) AS linked_count
         FROM suppliers s
         ORDER BY s.supplier_name"
    )->fetchAll();

    $allProducts = $pdo->query(
        "SELECT product_id, product_name, product_code
         FROM products
         WHERE status = 'Active'
           AND product_id NOT IN (SELECT product_id FROM product_suppliers)
         ORDER BY product_name"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Suppliers page query failed: ' . $e->getMessage());
    $suppliers = []; $allProducts = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Suppliers</h1>
        <p>Add, edit, and deactivate suppliers, and link them to products for restocking and loss-tracing.</p>
    </div>
</div>

<div class="section-actions">
    <input type="text" id="supplierTableSearch" placeholder="🔍 Search by supplier name or code..." style="max-width:320px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
    <button class="btn btn-primary" id="openAddSupplierModal">+ Add Supplier</button>
</div>

<div class="panel">
    <?php if (empty($suppliers)): ?>
        <p class="empty-note">No suppliers yet. Click "Add Supplier" to create your first one.</p>
    <?php else: ?>
    <p class="empty-note" id="noSupplierResults" style="display:none;">No suppliers match your search.</p>
    <div style="overflow-x:auto;">
    <table class="data-table" id="suppliersTable">
        <thead>
            <tr>
                <th>Code</th><th>Supplier</th><th>Contact Person</th><th>Phone</th><th>Email</th>
                <th>Linked Products</th><th>Status</th><th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($suppliers as $s): ?>
            <tr data-search="<?= htmlspecialchars(mb_strtolower($s['supplier_name'] . ' ' . $s['supplier_code']), ENT_QUOTES, 'UTF-8') ?>">
                <td><?= htmlspecialchars($s['supplier_code'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['supplier_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['contact_person'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['phone'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($s['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <button class="btn btn-outline btn-sm js-manage-products"
                        data-id="<?= $s['supplier_id'] ?>"
                        data-name="<?= htmlspecialchars($s['supplier_name'], ENT_QUOTES, 'UTF-8') ?>">
                        📦 <?= (int)$s['linked_count'] ?> linked
                    </button>
                </td>
                <td><span class="pill <?= $s['status'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($s['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn-outline btn-sm js-edit-supplier"
                            data-id="<?= $s['supplier_id'] ?>"
                            data-code="<?= htmlspecialchars($s['supplier_code'], ENT_QUOTES, 'UTF-8') ?>"
                            data-name="<?= htmlspecialchars($s['supplier_name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-contact="<?= htmlspecialchars((string)$s['contact_person'], ENT_QUOTES, 'UTF-8') ?>"
                            data-phone="<?= htmlspecialchars((string)$s['phone'], ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars((string)$s['email'], ENT_QUOTES, 'UTF-8') ?>"
                            data-address="<?= htmlspecialchars((string)$s['address'], ENT_QUOTES, 'UTF-8') ?>"
                        >Edit</button>
                        <?php if ($s['status'] === 'Active'): ?>
                            <button class="btn-danger-text js-toggle-supplier" data-id="<?= $s['supplier_id'] ?>" data-status="Inactive">Deactivate</button>
                        <?php else: ?>
                            <button class="btn-danger-text js-toggle-supplier" data-id="<?= $s['supplier_id'] ?>" data-status="Active">Reactivate</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ================= ADD/EDIT SUPPLIER MODAL ================= -->
<div class="modal-overlay" id="supplierModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="supplierModalTitle">Add Supplier</h2>
            <button class="modal-close js-close-modal" data-modal="supplierModal">&times;</button>
        </div>
        <div class="form-msg" id="supplierFormMsg"></div>
        <form id="supplierForm">
            <input type="hidden" id="s_action" value="add_supplier">
            <input type="hidden" id="s_supplier_id">

            <div class="form-grid-2">
                <div class="form-row"><label>Supplier Code</label><input type="text" id="s_supplier_code" required></div>
                <div class="form-row"><label>Supplier Name</label><input type="text" id="s_supplier_name" required></div>
            </div>
            <div class="form-row"><label>Contact Person</label><input type="text" id="s_contact_person"></div>
            <div class="form-grid-2">
                <div class="form-row"><label>Phone</label><input type="text" id="s_phone"></div>
                <div class="form-row"><label>Email</label><input type="email" id="s_email"></div>
            </div>
            <div class="form-row"><label>Address</label><textarea id="s_address" rows="2"></textarea></div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="supplierModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MANAGE LINKED PRODUCTS MODAL ================= -->
<div class="modal-overlay" id="productsModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="productsModalTitle">Linked Products</h2>
            <button class="modal-close js-close-modal" data-modal="productsModal">&times;</button>
        </div>

        <div id="linkedProductsList" style="margin-bottom:18px;"></div>

        <h3 style="font-size:14px; margin-bottom:10px;">Link a New Product</h3>
        <div class="form-msg" id="linkFormMsg"></div>
        <form id="linkProductForm">
            <input type="hidden" id="link_supplier_id">
            <div class="form-row">
                <label>Product</label>
                <select id="link_product_id" required>
                    <option value="">Select product</option>
                    <?php foreach ($allProducts as $p): ?>
                        <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($p['product_code'], ENT_QUOTES, 'UTF-8') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid-2">
                <div class="form-row"><label>Supplier's Product Code (optional)</label><input type="text" id="link_supplier_code"></div>
                <div class="form-row"><label>Last Cost Price (optional)</label><input type="number" step="0.01" min="0" id="link_cost_price"></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">+ Link Product</button>
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
document.getElementById('supplierTableSearch').addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const rows = document.querySelectorAll('#suppliersTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const match = (row.dataset.search || '').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('noSupplierResults').style.display = visible === 0 && q !== '' ? 'block' : 'none';
});

// ---------- Add Supplier ----------
document.getElementById('openAddSupplierModal').addEventListener('click', () => {
    document.getElementById('supplierForm').reset();
    document.getElementById('s_action').value = 'add_supplier';
    document.getElementById('s_supplier_id').value = '';
    document.getElementById('supplierModalTitle').textContent = 'Add Supplier';
    document.getElementById('supplierFormMsg').style.display = 'none';
    openModal('supplierModal');
});

// ---------- Edit Supplier ----------
document.querySelectorAll('.js-edit-supplier').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('supplierForm').reset();
        document.getElementById('s_action').value = 'edit_supplier';
        document.getElementById('s_supplier_id').value = btn.dataset.id;
        document.getElementById('s_supplier_code').value = btn.dataset.code;
        document.getElementById('s_supplier_name').value = btn.dataset.name;
        document.getElementById('s_contact_person').value = btn.dataset.contact;
        document.getElementById('s_phone').value = btn.dataset.phone;
        document.getElementById('s_email').value = btn.dataset.email;
        document.getElementById('s_address').value = btn.dataset.address;
        document.getElementById('supplierModalTitle').textContent = 'Edit Supplier';
        document.getElementById('supplierFormMsg').style.display = 'none';
        openModal('supplierModal');
    });
});

// ---------- Save Supplier (add or edit) ----------
document.getElementById('supplierForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('supplierFormMsg');
    const fd = new FormData();
    fd.append('action', document.getElementById('s_action').value);
    fd.append('supplier_id', document.getElementById('s_supplier_id').value);
    fd.append('supplier_code', document.getElementById('s_supplier_code').value);
    fd.append('supplier_name', document.getElementById('s_supplier_name').value);
    fd.append('contact_person', document.getElementById('s_contact_person').value);
    fd.append('phone', document.getElementById('s_phone').value);
    fd.append('email', document.getElementById('s_email').value);
    fd.append('address', document.getElementById('s_address').value);

    fetch('supplier_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Deactivate/Reactivate Supplier ----------
document.querySelectorAll('.js-toggle-supplier').forEach(btn => {
    btn.addEventListener('click', () => {
        const label = btn.dataset.status === 'Inactive' ? 'deactivate' : 'reactivate';
        if (!confirm('Are you sure you want to ' + label + ' this supplier?')) return;
        const fd = new FormData();
        fd.append('action', 'toggle_supplier_status');
        fd.append('supplier_id', btn.dataset.id);
        fd.append('new_status', btn.dataset.status);
        fetch('supplier_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});

// ---------- Manage Linked Products ----------
document.querySelectorAll('.js-manage-products').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('productsModalTitle').textContent = 'Linked Products — ' + btn.dataset.name;
        document.getElementById('link_supplier_id').value = btn.dataset.id;
        document.getElementById('linkProductForm').reset();
        document.getElementById('linkFormMsg').style.display = 'none';
        loadLinkedProducts(btn.dataset.id);
        openModal('productsModal');
    });
});

function loadLinkedProducts(supplierId) {
    const list = document.getElementById('linkedProductsList');
    list.innerHTML = '<p class="empty-note">Loading...</p>';
    fetch('supplier_actions.php?action=get_linked_products&supplier_id=' + supplierId)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { list.innerHTML = '<p class="empty-note">' + data.message + '</p>'; return; }
            if (!data.products.length) { list.innerHTML = '<p class="empty-note">No products linked yet.</p>'; return; }
            list.innerHTML = data.products.map(p => `
                <div class="linked-product-row">
                    <div>
                        <div class="lp-name">${p.product_name}</div>
                        <div class="lp-meta">${p.product_code}${p.supplier_product_code ? ' · Their code: ' + p.supplier_product_code : ''}${p.last_cost_price ? ' · Last cost: ₱' + parseFloat(p.last_cost_price).toFixed(2) : ''}</div>
                    </div>
                    <button class="btn-danger-text js-unlink-product"
                        data-link-id="${p.product_supplier_id}"
                        data-product-id="${p.product_id}"
                        data-product-name="${p.product_name}"
                        data-product-code="${p.product_code}">Unlink</button>
                </div>
            `).join('');

            list.querySelectorAll('.js-unlink-product').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!confirm('Unlink this product from the supplier?')) return;
                    const fd = new FormData();
                    fd.append('action', 'unlink_product');
                    fd.append('product_supplier_id', btn.dataset.linkId);
                    fetch('supplier_actions.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                loadLinkedProducts(supplierId);
                                updateLinkedBadge(supplierId, d.linked_count);
                                addProductOption(btn.dataset.productId, btn.dataset.productName, btn.dataset.productCode);
                            } else {
                                alert(d.message);
                            }
                        });
                });
            });
        });
}

// ---------- Link a new product ----------
document.getElementById('linkProductForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('linkFormMsg');
    const supplierId = document.getElementById('link_supplier_id').value;
    const productSelect = document.getElementById('link_product_id');
    const selectedProductId = productSelect.value;

    const fd = new FormData();
    fd.append('action', 'link_product');
    fd.append('supplier_id', supplierId);
    fd.append('product_id', selectedProductId);
    fd.append('supplier_product_code', document.getElementById('link_supplier_code').value);
    fd.append('last_cost_price', document.getElementById('link_cost_price').value);

    fetch('supplier_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.reset();
                msg.textContent = '✅ Product linked successfully!';
                msg.className = 'form-msg success';
                loadLinkedProducts(supplierId);
                updateLinkedBadge(supplierId, data.linked_count);
                removeProductOption(selectedProductId);
                setTimeout(() => { msg.style.display = 'none'; }, 2000);
            } else {
                msg.textContent = data.message;
                msg.className = 'form-msg error';
                showToast(msg.textContent);
            }
        });
});

function removeProductOption(productId) {
    const opt = document.querySelector('#link_product_id option[value="' + productId + '"]');
    if (opt) opt.remove();
}
function addProductOption(productId, productName, productCode) {
    const select = document.getElementById('link_product_id');
    if (document.querySelector('#link_product_id option[value="' + productId + '"]')) return; // already there
    const opt = document.createElement('option');
    opt.value = productId;
    opt.textContent = productName + ' (' + productCode + ')';
    select.appendChild(opt);
}

function updateLinkedBadge(supplierId, count) {
    const btn = document.querySelector('.js-manage-products[data-id="' + supplierId + '"]');
    if (btn) btn.innerHTML = '📦 ' + count + ' linked';
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
