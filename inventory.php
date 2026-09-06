<?php
declare(strict_types=1);
$pageTitle = 'Inventory';
require_once __DIR__ . '/includes/admin_header.php';

try {
    $products = $pdo->query(
        "SELECT p.*, c.category_name, s.supplier_id, s.supplier_name, ps.supplier_product_code
         FROM products p
         JOIN categories c ON c.category_id = p.category_id
         LEFT JOIN product_suppliers ps ON ps.product_id = p.product_id
         LEFT JOIN suppliers s ON s.supplier_id = ps.supplier_id
         ORDER BY p.product_name"
    )->fetchAll();

    $categories = $pdo->query(
        "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count
         FROM categories c
         ORDER BY c.category_name"
    )->fetchAll();

    $activeSuppliers = $pdo->query(
        "SELECT supplier_id, supplier_name FROM suppliers WHERE status = 'Active' ORDER BY supplier_name"
    )->fetchAll();

    $lowStockProducts = $pdo->query(
        "SELECT product_id, product_name, stock_quantity, reorder_level
         FROM products
         WHERE status = 'Active' AND stock_quantity <= reorder_level
         ORDER BY (reorder_level - stock_quantity) DESC"
    )->fetchAll();

    $expiringProducts = $pdo->query(
        "SELECT product_id, product_name, expiration_date, stock_quantity
         FROM products
         WHERE status = 'Active' AND expiration_date IS NOT NULL
           AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
         ORDER BY expiration_date ASC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Inventory query failed: ' . $e->getMessage());
    $products = []; $categories = []; $lowStockProducts = []; $expiringProducts = []; $activeSuppliers = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Inventory</h1>
        <p>Track stock levels, restocks, and losses.</p>
    </div>
</div>

<div class="tab-bar">
    <button class="tab-btn active" data-tab="tab-products">Products</button>
    <button class="tab-btn" data-tab="tab-alerts">Stock Alerts <?= (count($lowStockProducts) + count($expiringProducts)) > 0 ? '(' . (count($lowStockProducts) + count($expiringProducts)) . ')' : '' ?></button>
    <button class="tab-btn" data-tab="tab-categories">Categories</button>
</div>

<!-- ================= PRODUCTS TAB ================= -->
<div id="tab-products" class="tab-panel active">
    <div class="section-actions">
        <input type="text" id="productTableSearch" placeholder="🔍 Search by product name or category..." style="max-width:320px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
        <div style="display:flex; gap:10px;">
            <button class="btn btn-dark" id="openRestockModal">🔁 Restock</button>
            <button class="btn btn-primary" id="openAddProductModal">+ Add Product</button>
        </div>
    </div>

    <div class="panel">
        <?php if (empty($products)): ?>
            <p class="empty-note">No products yet. Click "Add Product" to create your first one.</p>
        <?php else: ?>
        <p class="empty-note" id="noResultsNote" style="display:none;">No products match your search.</p>
        <div style="overflow-x:auto;">
        <table class="data-table" id="productsTable">
            <thead>
                <tr>
                    <th>Code</th><th>Product</th><th>Category</th><th>Supplier</th><th>Stock</th>
                    <th>Price</th><th>Expiration</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p):
                    $isLow = $p['stock_quantity'] <= $p['reorder_level'];
                    $isExpired = $p['expiration_date'] && $p['expiration_date'] < date('Y-m-d');
                ?>
                <tr data-search="<?= htmlspecialchars(mb_strtolower($p['product_name'] . ' ' . $p['category_name'] . ' ' . ($p['supplier_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>">
                    <td><?= htmlspecialchars($p['product_code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($p['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $p['supplier_name'] ? htmlspecialchars($p['supplier_name'], ENT_QUOTES, 'UTF-8') : '<span style="color:var(--text-gray);">— none —</span>' ?></td>
                    <td>
                        <?= (int)$p['stock_quantity'] ?>
                        <?php if ($isLow): ?><span class="pill low">Low</span><?php endif; ?>
                    </td>
                    <td>₱<?= number_format((float)$p['selling_price'], 2) ?></td>
                    <td>
                        <?= $p['expiration_date'] ? htmlspecialchars($p['expiration_date'], ENT_QUOTES, 'UTF-8') : '—' ?>
                        <?php if ($isExpired): ?><span class="pill expired">Expired</span><?php endif; ?>
                    </td>
                    <td><span class="pill <?= $p['status'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($p['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn-outline btn-sm js-edit-product"
                                data-id="<?= $p['product_id'] ?>"
                                data-category="<?= $p['category_id'] ?>"
                                data-code="<?= htmlspecialchars($p['product_code'], ENT_QUOTES, 'UTF-8') ?>"
                                data-barcode="<?= htmlspecialchars($p['barcode'], ENT_QUOTES, 'UTF-8') ?>"
                                data-name="<?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-description="<?= htmlspecialchars((string)$p['description'], ENT_QUOTES, 'UTF-8') ?>"
                                data-cost="<?= $p['cost_price'] ?>"
                                data-selling="<?= $p['selling_price'] ?>"
                                data-reorder="<?= $p['reorder_level'] ?>"
                                data-expiration="<?= $p['expiration_date'] ?>"
                                data-supplier="<?= $p['supplier_id'] ?? '' ?>"
                                data-supplier-code="<?= htmlspecialchars((string)($p['supplier_product_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            >Edit</button>
                            <?php if ($p['status'] === 'Active'): ?>
                                <button class="btn-danger-text js-toggle-status" data-id="<?= $p['product_id'] ?>" data-status="Inactive">Deactivate</button>
                            <?php else: ?>
                                <button class="btn-danger-text js-toggle-status" data-id="<?= $p['product_id'] ?>" data-status="Active">Reactivate</button>
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
</div>

<!-- ================= STOCK ALERTS TAB ================= -->
<div id="tab-alerts" class="tab-panel">
    <div class="alert-grid">
        <div class="alert-card">
            <div class="alert-card-head low"><div class="icon">⚠️</div><h3>Low Stock Alert</h3></div>
            <?php if (empty($lowStockProducts)): ?>
                <p class="empty-note">No products are low on stock. 🎉</p>
            <?php else: foreach ($lowStockProducts as $lp): ?>
                <div class="alert-row">
                    <span><?= htmlspecialchars($lp['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="qty"><?= (int)$lp['stock_quantity'] ?> / <?= (int)$lp['reorder_level'] ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="alert-card">
            <div class="alert-card-head exp"><div class="icon">⏳</div><h3>Expiration Alert (next 30 days)</h3></div>
            <?php if (empty($expiringProducts)): ?>
                <p class="empty-note">Nothing expiring soon.</p>
            <?php else: foreach ($expiringProducts as $ep): ?>
                <div class="alert-row">
                    <span><?= htmlspecialchars($ep['product_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="qty"><?= htmlspecialchars($ep['expiration_date'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>

<!-- ================= CATEGORIES TAB ================= -->
<div id="tab-categories" class="tab-panel">
    <div class="section-actions">
        <div></div>
        <button class="btn btn-primary" id="openAddCategoryModal">+ Add Category</button>
    </div>
    <div class="panel">
        <?php if (empty($categories)): ?>
            <p class="empty-note">No categories yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Category</th><th>Description</th><th>Products</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string)($c['description'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$c['product_count'] ?></td>
                    <td><span class="pill <?= $c['status'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td>
                        <?php if ($c['status'] === 'Active'): ?>
                            <button class="btn-danger-text js-toggle-category" data-id="<?= $c['category_id'] ?>" data-status="Inactive">Deactivate</button>
                        <?php else: ?>
                            <button class="btn-danger-text js-toggle-category" data-id="<?= $c['category_id'] ?>" data-status="Active">Reactivate</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- ================= ADD/EDIT PRODUCT MODAL ================= -->
<div class="modal-overlay" id="productModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="productModalTitle">Add Product</h2>
            <button class="modal-close js-close-modal" data-modal="productModal">&times;</button>
        </div>
        <div class="form-msg" id="productFormMsg"></div>
        <form id="productForm">
            <input type="hidden" name="action" id="productFormAction" value="add_product">
            <input type="hidden" name="product_id" id="f_product_id">

            <div class="form-row">
                <label>Category</label>
                <select name="category_id" id="f_category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['category_name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid-2">
                <div class="form-row"><label>Product Code</label><input type="text" name="product_code" id="f_product_code" required></div>
                <div class="form-row"><label>Barcode</label><input type="text" name="barcode" id="f_barcode" required></div>
            </div>

            <div class="form-row"><label>Product Name</label><input type="text" name="product_name" id="f_product_name" required></div>
            <div class="form-row"><label>Description</label><textarea name="description" id="f_description" rows="2"></textarea></div>

            <div class="form-grid-2">
                <div class="form-row"><label>Cost Price</label><input type="number" step="0.01" min="0" name="cost_price" id="f_cost_price"></div>
                <div class="form-row"><label>Selling Price</label><input type="number" step="0.01" min="0" name="selling_price" id="f_selling_price"></div>
            </div>

            <div class="form-grid-2">
                <div class="form-row" id="stockQtyRow"><label>Initial Stock Quantity</label><input type="number" min="0" name="stock_quantity" id="f_stock_quantity" value="0"></div>
                <div class="form-row"><label>Reorder Level</label><input type="number" min="0" name="reorder_level" id="f_reorder_level" value="5"></div>
            </div>

            <div class="form-row"><label>Expiration Date (optional)</label><input type="date" name="expiration_date" id="f_expiration_date"></div>

            <div class="form-row">
                <label>Supplier (optional)</label>
                <select name="supplier_id" id="f_supplier_id">
                    <option value="">No supplier</option>
                    <?php foreach ($activeSuppliers as $sup): ?>
                        <option value="<?= $sup['supplier_id'] ?>"><?= htmlspecialchars($sup['supplier_name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row" id="supplierCodeRow" style="display:none;">
                <label>Supplier's Product Code (optional)</label>
                <input type="text" name="supplier_product_code" id="f_supplier_product_code">
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="productModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= ADD CATEGORY MODAL ================= -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Add Category</h2>
            <button class="modal-close js-close-modal" data-modal="categoryModal">&times;</button>
        </div>
        <div class="form-msg" id="categoryFormMsg"></div>
        <form id="categoryForm">
            <input type="hidden" name="action" value="add_category">
            <div class="form-row"><label>Category Name</label><input type="text" name="category_name" required></div>
            <div class="form-row"><label>Description</label><textarea name="description" rows="2"></textarea></div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="categoryModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= RESTOCK MODAL ================= -->
<div class="modal-overlay" id="restockModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Restock Product</h2>
            <button class="modal-close js-close-modal" data-modal="restockModal">&times;</button>
        </div>
        <div class="form-msg" id="restockFormMsg"></div>
        <form id="restockForm">
            <input type="hidden" name="action" value="restock">

            <div class="form-row">
                <label>Select Product</label>
                <select name="product_id" id="r_product_id" required>
                    <option value="">Select product</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name'], ENT_QUOTES, 'UTF-8') ?> (current: <?= (int)$p['stock_quantity'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Select Supplier</label>
                <select name="supplier_id" id="r_supplier_id" required>
                    <option value="">Select a product first</option>
                </select>
                <div class="field-error" id="r_supplier_warning" style="display:none; margin-top:6px;">
                    ⚠️ No supplier is linked to this product yet. Go to <strong>Suppliers</strong> and link one before you can restock it.
                </div>
            </div>

            <div class="form-row"><label>Quantity to Add</label><input type="number" min="1" name="quantity" id="r_quantity" required></div>
            <div class="form-row"><label>Remarks (optional)</label><input type="text" name="remarks" id="r_remarks"></div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="restockModal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="restockSubmitBtn">Confirm Restock</button>
            </div>
        </form>
    </div>
</div>

<script>
// ---------- Product table search (by name or category) ----------
const productTableSearch = document.getElementById('productTableSearch');
if (productTableSearch) {
    productTableSearch.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#productsTable tbody tr');
        let visibleCount = 0;
        rows.forEach(row => {
            const match = (row.dataset.search || '').includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        const noResults = document.getElementById('noResultsNote');
        if (noResults) noResults.style.display = visibleCount === 0 && q !== '' ? 'block' : 'none';
    });
}

// ---------- Tabs ----------
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});

// ---------- Modal open/close ----------
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modal));
});
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

// ---------- Add Product ----------
document.getElementById('openAddProductModal').addEventListener('click', () => {
    document.getElementById('productForm').reset();
    document.getElementById('productFormAction').value = 'add_product';
    document.getElementById('f_product_id').value = '';
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('stockQtyRow').style.display = 'block';
    document.getElementById('productFormMsg').style.display = 'none';
    document.getElementById('f_supplier_id').value = '';
    document.getElementById('supplierCodeRow').style.display = 'none';
    openModal('productModal');
});

// ---------- Edit Product (prefill from data attributes) ----------
document.querySelectorAll('.js-edit-product').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('productForm').reset();
        document.getElementById('productFormAction').value = 'edit_product';
        document.getElementById('f_product_id').value = btn.dataset.id;
        document.getElementById('f_category_id').value = btn.dataset.category;
        document.getElementById('f_product_code').value = btn.dataset.code;
        document.getElementById('f_barcode').value = btn.dataset.barcode;
        document.getElementById('f_product_name').value = btn.dataset.name;
        document.getElementById('f_description').value = btn.dataset.description;
        document.getElementById('f_cost_price').value = btn.dataset.cost;
        document.getElementById('f_selling_price').value = btn.dataset.selling;
        document.getElementById('f_reorder_level').value = btn.dataset.reorder;
        document.getElementById('f_expiration_date').value = btn.dataset.expiration !== '' ? btn.dataset.expiration : '';
        document.getElementById('f_supplier_id').value = btn.dataset.supplier || '';
        document.getElementById('f_supplier_product_code').value = btn.dataset.supplierCode || '';
        document.getElementById('supplierCodeRow').style.display = btn.dataset.supplier ? 'block' : 'none';
        document.getElementById('productModalTitle').textContent = 'Edit Product';
        document.getElementById('stockQtyRow').style.display = 'none'; // stock changes go through Restock, not edit
        document.getElementById('productFormMsg').style.display = 'none';
        openModal('productModal');
    });
});

// ---------- Show supplier-code field only when a supplier is chosen ----------
document.getElementById('f_supplier_id').addEventListener('change', function () {
    document.getElementById('supplierCodeRow').style.display = this.value ? 'block' : 'none';
});

// ---------- Deactivate/Reactivate product ----------
document.querySelectorAll('.js-toggle-status').forEach(btn => {
    btn.addEventListener('click', () => {
        const label = btn.dataset.status === 'Inactive' ? 'deactivate' : 'reactivate';
        if (!confirm('Are you sure you want to ' + label + ' this product?')) return;

        const fd = new FormData();
        fd.append('action', 'toggle_product_status');
        fd.append('product_id', btn.dataset.id);
        fd.append('new_status', btn.dataset.status);

        fetch('inventory_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});

// ---------- Deactivate/Reactivate category ----------
document.querySelectorAll('.js-toggle-category').forEach(btn => {
    btn.addEventListener('click', () => {
        const label = btn.dataset.status === 'Inactive' ? 'deactivate' : 'reactivate';
        if (!confirm('Are you sure you want to ' + label + ' this category?')) return;

        const fd = new FormData();
        fd.append('action', 'toggle_category_status');
        fd.append('category_id', btn.dataset.id);
        fd.append('new_status', btn.dataset.status);

        fetch('inventory_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});

// ---------- Add Category button ----------
document.getElementById('openAddCategoryModal').addEventListener('click', () => {
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryFormMsg').style.display = 'none';
    openModal('categoryModal');
});

// ---------- Restock button (top-level) ----------
document.getElementById('openRestockModal').addEventListener('click', () => {
    document.getElementById('restockForm').reset();
    document.getElementById('restockFormMsg').style.display = 'none';
    document.getElementById('r_supplier_id').innerHTML = '<option value="">Select a product first</option>';
    document.getElementById('r_supplier_warning').style.display = 'none';
    document.getElementById('restockSubmitBtn').disabled = false;
    openModal('restockModal');
});

// ---------- Load suppliers when a product is chosen in Restock modal ----------
document.getElementById('r_product_id').addEventListener('change', function () {
    const supplierSelect = document.getElementById('r_supplier_id');
    const warning = document.getElementById('r_supplier_warning');
    const submitBtn = document.getElementById('restockSubmitBtn');
    supplierSelect.innerHTML = '<option value="">Loading...</option>';
    warning.style.display = 'none';
    submitBtn.disabled = false;

    if (!this.value) {
        supplierSelect.innerHTML = '<option value="">Select a product first</option>';
        return;
    }
    fetch('inventory_actions.php?action=get_suppliers_for_product&product_id=' + encodeURIComponent(this.value))
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.suppliers.length) {
                supplierSelect.innerHTML = '<option value="">No linked supplier</option>';
                warning.style.display = 'block';
                submitBtn.disabled = true;
                return;
            }
            supplierSelect.innerHTML = '<option value="">Select supplier</option>' +
                data.suppliers.map(s => `<option value="${s.supplier_id}">${s.supplier_name}</option>`).join('');
        });
});

// ---------- Generic form submit helper ----------
function submitJsonForm(form, msgEl, onSuccess) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        msgEl.style.display = 'none';
        const fd = new FormData(form);
        fetch('inventory_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    onSuccess();
                } else {
                    msgEl.textContent = data.message;
                    msgEl.className = 'form-msg error';
                }
            })
            .catch(() => {
                msgEl.textContent = 'Network error. Please try again.';
                msgEl.className = 'form-msg error';
            });
    });
}

submitJsonForm(document.getElementById('productForm'), document.getElementById('productFormMsg'), () => location.reload());
submitJsonForm(document.getElementById('categoryForm'), document.getElementById('categoryFormMsg'), () => location.reload());
submitJsonForm(document.getElementById('restockForm'), document.getElementById('restockFormMsg'), () => location.reload());
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
