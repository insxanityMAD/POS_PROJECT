<?php
declare(strict_types=1);
$pageTitle = 'POS / Sales';
$allowedRoles = ['Admin', 'Manager', 'Cashier'];
require_once __DIR__ . '/../../includes/admin_header.php';

try {
    $discounts = $pdo->query("SELECT * FROM discount_types WHERE is_active = 1 ORDER BY discount_name")->fetchAll();
    $taxes     = $pdo->query("SELECT * FROM tax_settings ORDER BY tax_rate")->fetchAll();
    $qrSettingsRaw = $pdo->query("SELECT * FROM qr_payment_settings WHERE is_active = 1")->fetchAll();
    $qrSettings = [];
    foreach ($qrSettingsRaw as $qr) {
        $qrSettings[$qr['payment_name']] = $qr;
    }

    $recentSales = $pdo->query(
        "SELECT s.sale_id, s.receipt_number, s.sale_date, s.total_amount, s.sale_status, u.full_name
         FROM sales s
         JOIN users u ON u.user_id = s.user_id
         ORDER BY s.sale_date DESC
         LIMIT 50"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('POS page query failed: ' . $e->getMessage());
    $discounts = []; $taxes = []; $qrSettings = []; $recentSales = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>POS / Sales</h1>
        <p>Ring up a new sale and review transaction history.</p>
    </div>
</div>

<div class="pos-grid">

    <!-- ================= LEFT: SEARCH + CART ================= -->
    <div>
        <div class="panel" style="margin-bottom:18px;">
            <div class="search-box-wrap">
                <div class="search-input-row">
                    <input type="text" id="productSearch" placeholder="🔍 Search product by name...">
                    <input type="text" id="barcodeInput" placeholder="🔢 Scan or type barcode + Enter" style="max-width:220px;">
                </div>
                <div class="search-results" id="searchResults"></div>
            </div>
            <div class="scan-preview" id="scanPreview">
                <span class="sp-icon">✅</span>
                <span class="sp-name" id="spName"></span>
                <span class="sp-price" id="spPrice"></span>
            </div>
            <div class="shortcut-legend">
                <span><kbd>F2</kbd> Focus barcode</span>
                <span><kbd>F3</kbd> Focus search</span>
                <span><kbd>↑↓</kbd> Navigate results</span>
                <span><kbd>Enter</kbd> Add / Select</span>
                <span><kbd>1-4</kbd> Payment method</span>
                <span><kbd>Ctrl</kbd>+<kbd>Enter</kbd> Confirm payment</span>
                <span><kbd>Esc</kbd> Close</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><div><h2>Cart</h2><p>Items in this sale</p></div></div>
            <div id="cartEmpty" class="cart-empty">🛒 Cart is empty. Search or scan a product to begin.</div>
            <table class="data-table" id="cartTable" style="display:none;">
                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="cartBody"></tbody>
            </table>
        </div>
    </div>

    <!-- ================= RIGHT: SUMMARY / DISCOUNT / TAX / PAYMENT ================= -->
    <div class="panel">
        <div class="panel-head"><div><h2>Order Summary</h2></div></div>

        <div class="form-row">
            <label>Discount</label>
            <select id="discountSelect">
                <option value="">None</option>
                <?php foreach ($discounts as $d): ?>
                    <option value="<?= $d['discount_id'] ?>" data-requires-id="<?= $d['requires_id'] ? '1' : '0' ?>">
                        <?= htmlspecialchars($d['discount_name'], ENT_QUOTES, 'UTF-8') ?> (<?= rtrim(rtrim(number_format((float)$d['discount_rate'], 2), '0'), '.') ?>%)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row" id="idNumberRow" style="display:none;">
            <label>ID Number <span style="color:var(--mrdiy-red); font-weight:800;">* Required for this discount</span></label>
            <input type="text" id="customerIdNumber" placeholder="PWD / Senior Citizen ID">
            <div class="field-error" id="customerIdWarning" style="display:none;">⚠️ This discount cannot be applied without an ID number.</div>
        </div>

        <div class="form-row">
            <label>Tax</label>
            <select id="taxSelect">
                <option value="">No tax</option>
                <?php foreach ($taxes as $t): ?>
                    <option value="<?= $t['tax_id'] ?>" <?= $t['is_active'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['tax_name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-top:16px;">
            <div class="summary-row"><span>Subtotal</span><span id="sumSubtotal">₱0.00</span></div>
            <div class="summary-row"><span>Discount</span><span class="neg" id="sumDiscount">-₱0.00</span></div>
            <div class="summary-row"><span>Tax</span><span id="sumTax">₱0.00</span></div>
            <div class="summary-row total"><span>Total</span><span id="sumTotal">₱0.00</span></div>
        </div>

        <div class="form-row" style="margin-top:18px;"><label>Payment Method</label></div>
        <div class="payment-grid">
            <div class="payment-option selected" data-method="Cash"><span class="pi">💵</span>Cash</div>
            <div class="payment-option" data-method="GCash"><span class="pi">📱</span>GCash</div>
            <div class="payment-option" data-method="Maya"><span class="pi">📱</span>Maya</div>
            <div class="payment-option" data-method="Bank"><span class="pi">🏦</span>Bank</div>
        </div>

        <div class="qr-box" id="qrBox">
            <div id="qrImageWrap"></div>
            <div class="qr-account" id="qrAccountName"></div>
            <div class="qr-number" id="qrAccountNumber"></div>
        </div>

        <div class="form-row" id="amountPaidRow">
            <label>Amount Received</label>
            <input type="number" step="0.01" min="0" id="amountPaid">
        </div>
        <div class="form-row" id="referenceRow" style="display:none;">
            <label>Reference Number</label>
            <input type="text" id="referenceNumber" placeholder="Transaction reference">
        </div>
        <div class="change-box" id="changeBox" style="display:none;"></div>

        <div class="form-msg" id="posFormMsg"></div>

        <button class="btn btn-primary" id="confirmPaymentBtn" style="width:100%; justify-content:center; margin-top:16px;">Confirm Payment →</button>
    </div>
</div>

<!-- ================= TRANSACTION HISTORY ================= -->
<div class="panel" style="margin-top:18px;">
    <div class="panel-head">
        <div><h2>Transaction History</h2><p>Recent sales — void a transaction to reverse it and restore stock.</p></div>
    </div>
    <?php if (empty($recentSales)): ?>
        <p class="empty-note">No sales recorded yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="data-table">
        <thead><tr><th>Receipt #</th><th>Cashier</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($recentSales as $sale): ?>
            <tr>
                <td><?= htmlspecialchars($sale['receipt_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($sale['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($sale['sale_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                <td>₱<?= number_format((float)$sale['total_amount'], 2) ?></td>
                <td><span class="pill <?= $sale['sale_status'] === 'Completed' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($sale['sale_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <?php if ($sale['sale_status'] === 'Completed' && in_array($_SESSION['role_name'] ?? '', ['Admin', 'Manager'], true)): ?>
                        <button class="btn-danger-text js-void-sale" data-id="<?= $sale['sale_id'] ?>" data-receipt="<?= htmlspecialchars($sale['receipt_number'], ENT_QUOTES, 'UTF-8') ?>">Void</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ================= VOID SALE MODAL ================= -->
<div class="modal-overlay" id="voidSaleModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Void Transaction</h2>
            <button class="modal-close js-close-modal" data-modal="voidSaleModal">&times;</button>
        </div>
        <p style="font-size:13.5px; color:var(--text-gray); margin-bottom:14px;">
            Voiding <strong id="voidReceiptLabel"></strong> will restore the stock for every item on this sale and mark it as Voided. This action is logged and cannot be undone.
        </p>
        <div class="form-msg" id="voidFormMsg"></div>
        <div class="form-row">
            <label>Reason (optional)</label>
            <input type="text" id="voidReason" placeholder="e.g. Customer changed their mind, wrong item scanned">
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-outline js-close-modal" data-modal="voidSaleModal">Cancel</button>
            <button type="button" class="btn btn-primary" id="voidSaleConfirmBtn" style="background:var(--mrdiy-red);">Yes, Void This Sale</button>
        </div>
    </div>
</div>

<!-- ================= CONFIRM PAYMENT MODAL ================= -->
<div class="modal-overlay" id="confirmPaymentModal">
    <div class="modal-box" style="text-align:center;">
        <div style="font-size:44px; margin-bottom:8px;">🧾</div>
        <h2 style="margin-bottom:6px;">Confirm This Sale?</h2>
        <p style="color:var(--text-gray); font-size:13.5px; margin-bottom:18px;">Please review before completing the transaction.</p>
        <div class="calc-box" style="text-align:left;">
            <div class="calc-row"><span>Items</span><span id="confirmItemsCount"></span></div>
            <div class="calc-row"><span>Payment Method</span><span id="confirmMethod"></span></div>
            <div class="calc-row net"><span>Total Due</span><span id="confirmTotal"></span></div>
        </div>
        <div class="form-msg" id="confirmModalMsg" style="text-align:left;"></div>
        <div class="form-actions" style="justify-content:center; margin-top:18px;">
            <button type="button" class="btn btn-outline" id="confirmPaymentCancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmPaymentYes">Yes, Confirm Payment</button>
        </div>
    </div>
</div>

<!-- ================= RECEIPT MODAL ================= -->
<div class="modal-overlay" id="receiptModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Receipt</h2>
            <button class="modal-close js-close-modal" data-modal="receiptModal">&times;</button>
        </div>
        <div class="receipt-box" id="receiptContent"></div>
        <div class="form-actions">
            <button class="btn btn-outline" id="printReceiptBtn">🖨️ Print</button>
            <button class="btn btn-primary" id="newSaleBtn">+ New Sale</button>
        </div>
    </div>
</div>

<script>
const QR_SETTINGS = <?= json_encode($qrSettings) ?>;

let cart = [];
let selectedPaymentMethod = 'Cash';

const money = n => '₱' + Number(n).toFixed(2);
const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ---------- Void Sale ----------
let pendingVoidSaleId = null;

document.querySelectorAll('.js-void-sale').forEach(btn => {
    btn.addEventListener('click', () => {
        pendingVoidSaleId = btn.dataset.id;
        document.getElementById('voidReceiptLabel').textContent = btn.dataset.receipt;
        document.getElementById('voidReason').value = '';
        document.getElementById('voidFormMsg').style.display = 'none';
        openModal('voidSaleModal');
    });
});

document.getElementById('voidSaleConfirmBtn').addEventListener('click', function () {
    if (!pendingVoidSaleId) return;
    const msg = document.getElementById('voidFormMsg');
    this.disabled = true;
    this.textContent = 'Voiding...';

    const fd = new FormData();
    fd.append('action', 'void_sale');
    fd.append('sale_id', pendingVoidSaleId);
    fd.append('reason', document.getElementById('voidReason').value);

    fetch('pos_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            this.disabled = false;
            this.textContent = 'Yes, Void This Sale';
            if (data.success) {
                location.reload();
            } else {
                msg.textContent = data.message;
                msg.className = 'form-msg error';
                showToast(data.message);
            }
        })
        .catch(() => {
            this.disabled = false;
            this.textContent = 'Yes, Void This Sale';
            msg.textContent = 'Network error. Please try again.';
            msg.className = 'form-msg error';
            showToast('Network error. Please try again.');
        });
});

// ---------- Product search (debounced) ----------
let searchTimer;
const searchInput = document.getElementById('productSearch');
const searchResults = document.getElementById('searchResults');

searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (!q) { searchResults.classList.remove('open'); return; }
    searchTimer = setTimeout(() => {
        fetch('pos_actions.php?action=search_products&q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => renderSearchResults(data.products || []));
    }, 250);
});

let activeResultIndex = -1;
searchInput.addEventListener('keydown', function (e) {
    const items = searchResults.querySelectorAll('.search-result-item:not(.disabled)');
    if (!items.length || !searchResults.classList.contains('open')) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeResultIndex = Math.min(activeResultIndex + 1, items.length - 1);
        highlightResult(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeResultIndex = Math.max(activeResultIndex - 1, 0);
        highlightResult(items);
    } else if (e.key === 'Enter' && activeResultIndex >= 0) {
        e.preventDefault();
        items[activeResultIndex].click();
        activeResultIndex = -1;
    } else if (e.key === 'Escape') {
        searchResults.classList.remove('open');
        activeResultIndex = -1;
    }
});
function highlightResult(items) {
    items.forEach((el, i) => el.classList.toggle('kbd-active', i === activeResultIndex));
    items[activeResultIndex].scrollIntoView({ block: 'nearest' });
}

function renderSearchResults(products) {
    activeResultIndex = -1;
    if (!products.length) {
        searchResults.innerHTML = '<div class="search-result-item disabled">No matching products</div>';
        searchResults.classList.add('open');
        return;
    }
    searchResults.innerHTML = products.map(p => `
        <div class="search-result-item ${p.stock_quantity <= 0 ? 'disabled' : ''}" data-product='${escapeHtml(JSON.stringify(p))}'>
            <div>
                <div class="sr-name">${escapeHtml(p.product_name)}</div>
                <div class="sr-meta">${escapeHtml(p.barcode)} · Stock: ${p.stock_quantity}${p.stock_quantity <= 0 ? ' (Out of stock)' : ''}</div>
            </div>
            <div class="sr-price">${money(p.selling_price)}</div>
        </div>
    `).join('');
    searchResults.classList.add('open');
    searchResults.querySelectorAll('.search-result-item:not(.disabled)').forEach(el => {
        el.addEventListener('click', () => {
            showScanPreview(JSON.parse(el.dataset.product));
            addToCart(JSON.parse(el.dataset.product));
            searchInput.value = '';
            searchResults.classList.remove('open');
        });
    });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.search-box-wrap')) searchResults.classList.remove('open');
});

// ---------- Barcode scan / manual entry ----------
document.getElementById('barcodeInput').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const code = this.value.trim();
    if (!code) return;
    fetch('pos_actions.php?action=lookup_barcode&barcode=' + encodeURIComponent(code))
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if ((data.product.stock_quantity ?? 0) <= 0) {
                    alert(data.product.product_name + ' is out of stock and cannot be added to the cart.');
                } else {
                    showScanPreview(data.product);
                    addToCart(data.product);
                }
                this.value = '';
            } else {
                alert(data.message);
            }
        });
});

function showScanPreview(product) {
    const box = document.getElementById('scanPreview');
    document.getElementById('spName').textContent = product.product_name;
    document.getElementById('spPrice').textContent = money(product.selling_price);
    box.classList.add('show');
    clearTimeout(window.__scanPreviewTimer);
    window.__scanPreviewTimer = setTimeout(() => box.classList.remove('show'), 2200);
}

// ---------- Cart management ----------
function addToCart(product) {
    if ((product.stock_quantity ?? 0) <= 0) {
        alert(product.product_name + ' is out of stock and cannot be added to the cart.');
        return;
    }
    const existing = cart.find(i => i.product_id == product.product_id);
    if (existing) {
        if (existing.qty + 1 > product.stock_quantity) { alert('Not enough stock. Only ' + product.stock_quantity + ' available.'); return; }
        existing.qty += 1;
    } else {
        cart.push({
            product_id: product.product_id,
            product_name: product.product_name,
            price: parseFloat(product.selling_price),
            stock_quantity: product.stock_quantity,
            qty: 1,
        });
    }
    renderCart();
}

function renderCart() {
    const empty = document.getElementById('cartEmpty');
    const table = document.getElementById('cartTable');
    const body = document.getElementById('cartBody');

    if (!cart.length) {
        empty.style.display = 'block';
        table.style.display = 'none';
        updateSummary();
        return;
    }
    empty.style.display = 'none';
    table.style.display = 'table';

    body.innerHTML = cart.map((item, idx) => `
        <tr>
            <td>${escapeHtml(item.product_name)}</td>
            <td><input type="number" class="qty-input" min="1" max="${item.stock_quantity}" value="${item.qty}" data-idx="${idx}"></td>
            <td>${money(item.price)}</td>
            <td>${money(item.price * item.qty)}</td>
            <td><button class="remove-item" data-idx="${idx}">&times;</button></td>
        </tr>
    `).join('');

    body.querySelectorAll('.qty-input').forEach(inp => {
        inp.addEventListener('change', function () {
            const idx = this.dataset.idx;
            let val = parseInt(this.value) || 1;
            if (val > cart[idx].stock_quantity) { val = cart[idx].stock_quantity; alert('Not enough stock.'); }
            if (val < 1) val = 1;
            cart[idx].qty = val;
            renderCart();
        });
    });
    body.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function () {
            cart.splice(this.dataset.idx, 1);
            renderCart();
        });
    });

    updateSummary();
}

// ---------- Discount / Tax / Summary ----------
const discountSelect = document.getElementById('discountSelect');
const taxSelect = document.getElementById('taxSelect');

discountSelect.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const requiresId = opt.dataset.requiresId === '1';
    document.getElementById('idNumberRow').style.display = requiresId ? 'block' : 'none';
    document.getElementById('customerIdWarning').style.display = 'none';
    updateSummary();
});

document.getElementById('customerIdNumber').addEventListener('input', function () {
    if (this.value.trim()) document.getElementById('customerIdWarning').style.display = 'none';
});
taxSelect.addEventListener('change', updateSummary);

function getDiscountRate() {
    const opt = discountSelect.options[discountSelect.selectedIndex];
    if (!discountSelect.value) return 0;
    const match = opt.textContent.match(/\(([\d.]+)%\)/);
    return match ? parseFloat(match[1]) : 0;
}
function getTaxRate() {
    const opt = taxSelect.options[taxSelect.selectedIndex];
    if (!taxSelect.value) return 0;
    <?php foreach ($taxes as $t): ?>
    if (taxSelect.value == '<?= $t['tax_id'] ?>') return <?= (float)$t['tax_rate'] ?>;
    <?php endforeach; ?>
    return 0;
}

function updateSummary() {
    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const discountAmount = subtotal * (getDiscountRate() / 100);
    const taxable = subtotal - discountAmount;
    const taxAmount = taxable * (getTaxRate() / 100);
    const total = taxable + taxAmount;

    document.getElementById('sumSubtotal').textContent = money(subtotal);
    document.getElementById('sumDiscount').textContent = '-' + money(discountAmount);
    document.getElementById('sumTax').textContent = money(taxAmount);
    document.getElementById('sumTotal').textContent = money(total);

    updateChange(total);
    return total;
}

// ---------- Payment method ----------
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', () => selectPaymentMethod(opt.dataset.method));
});

function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    document.querySelectorAll('.payment-option').forEach(o => {
        o.classList.toggle('selected', o.dataset.method === method);
    });
    const isCash = method === 'Cash';
    document.getElementById('amountPaidRow').style.display = isCash ? 'block' : 'none';
    document.getElementById('referenceRow').style.display = isCash ? 'none' : 'block';

    const qrBox = document.getElementById('qrBox');
    if (isCash) {
        qrBox.classList.remove('show');
    } else {
        renderQr(method);
        qrBox.classList.add('show');
    }
    updateChange(updateSummary());
}

function renderQr(method) {
    const settings = QR_SETTINGS[method];
    const wrap = document.getElementById('qrImageWrap');
    wrap.innerHTML = '';

    const img = document.createElement('img');
    img.style.maxWidth = '160px';
    img.style.borderRadius = '8px';

    if (settings && settings.qr_image_path) {
        // Admin-uploaded static QR image for this payment method
        img.src = settings.qr_image_path;
    } else {
        // Sample static QR (not linked to a live payment gateway) - encodes a
        // fixed sample payload so it always renders the same demo code.
        const samplePayload = 'MRDIY-' + method.toUpperCase() + '-SAMPLE-PAYMENT-QR';
        img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(samplePayload);
    }
    wrap.appendChild(img);

    document.getElementById('qrAccountName').textContent = settings && settings.account_name ? settings.account_name : method + ' Payment (Sample QR)';
    document.getElementById('qrAccountNumber').textContent = settings && settings.account_number ? settings.account_number : 'For demo purposes only';
}

document.getElementById('amountPaid').addEventListener('input', () => updateChange(updateSummary()));

function updateChange(total) {
    const box = document.getElementById('changeBox');
    if (selectedPaymentMethod !== 'Cash') { box.style.display = 'none'; return; }
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = paid - total;
    if (paid <= 0) { box.style.display = 'none'; return; }
    box.style.display = 'block';
    box.textContent = change >= 0 ? ('Change: ' + money(change)) : ('Short by ' + money(Math.abs(change)));
    box.style.color = change >= 0 ? 'var(--green)' : 'var(--mrdiy-red)';
    box.style.background = change >= 0 ? '#E4F5EC' : '#FDECEC';
}

// ---------- Confirm Payment ----------
let pendingSalePayload = null;

document.getElementById('confirmPaymentBtn').addEventListener('click', function () {
    const msg = document.getElementById('posFormMsg');
    msg.style.display = 'none';

    if (!cart.length) { msg.textContent = 'Cart is empty.'; msg.className = 'form-msg error'; showToast('Your cart is empty.'); return; }

    const amountPaidVal = parseFloat(document.getElementById('amountPaid').value) || 0;
    const totalDue = updateSummary();

    if (selectedPaymentMethod === 'Cash') {
        if (amountPaidVal <= 0) {
            msg.textContent = '⚠️ Please enter the amount received from the customer before confirming.';
            msg.className = 'form-msg error';
            showToast('Please enter the amount received.');
            document.getElementById('amountPaid').focus();
            return;
        }
        if (amountPaidVal < totalDue) {
            msg.textContent = '⚠️ Amount received (' + money(amountPaidVal) + ') is less than the total due (' + money(totalDue) + ').';
            msg.className = 'form-msg error';
            showToast('Amount received is less than the total due.');
            document.getElementById('amountPaid').focus();
            return;
        }
    }

    // A discount that requires an ID (PWD/Senior Citizen) must have the ID number filled in
    const selectedDiscountOpt = discountSelect.options[discountSelect.selectedIndex];
    const discountRequiresId = discountSelect.value && selectedDiscountOpt.dataset.requiresId === '1';
    const customerIdVal = document.getElementById('customerIdNumber').value.trim();
    if (discountRequiresId && !customerIdVal) {
        msg.textContent = '⚠️ This discount requires an ID number. Please enter it before confirming.';
        msg.className = 'form-msg error';
        document.getElementById('customerIdWarning').style.display = 'block';
        showToast('ID number is required for this discount.');
        document.getElementById('customerIdNumber').focus();
        return;
    }
    document.getElementById('customerIdWarning').style.display = 'none';

    pendingSalePayload = {
        items: cart.map(i => ({ product_id: i.product_id, quantity: i.qty })),
        discount_id: discountSelect.value || null,
        customer_id_number: customerIdVal,
        tax_id: taxSelect.value || null,
        payment_method: selectedPaymentMethod,
        amount_paid: amountPaidVal,
        reference_number: document.getElementById('referenceNumber').value,
    };

    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    document.getElementById('confirmItemsCount').textContent = totalQty + ' pc(s) / ' + cart.length + ' product' + (cart.length === 1 ? '' : 's');
    document.getElementById('confirmMethod').textContent = selectedPaymentMethod;
    document.getElementById('confirmTotal').textContent = money(totalDue);
    document.getElementById('confirmModalMsg').style.display = 'none';
    openModal('confirmPaymentModal');
});

document.getElementById('confirmPaymentCancel').addEventListener('click', () => closeModal('confirmPaymentModal'));

document.getElementById('confirmPaymentYes').addEventListener('click', function () {
    if (!pendingSalePayload) return;
    const modalMsg = document.getElementById('confirmModalMsg');
    const mainBtn = document.getElementById('confirmPaymentBtn');
    modalMsg.style.display = 'none';

    this.disabled = true;
    this.textContent = 'Processing...';
    mainBtn.disabled = true;

    fetch('pos_actions.php?action=complete_sale', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(pendingSalePayload),
    })
    .then(r => r.json())
    .then(data => {
        this.disabled = false;
        this.textContent = 'Yes, Confirm Payment';
        mainBtn.disabled = false;

        if (!data.success) {
            // Keep the modal open and show the error right here, so it's impossible to miss
            modalMsg.textContent = data.message;
            modalMsg.className = 'form-msg error';
            modalMsg.style.display = 'block';
            showToast(data.message);
            return;
        }
        closeModal('confirmPaymentModal');
        pendingSalePayload = null;
        showToast('Sale completed successfully!', 'success');
        showReceipt(data.receipt);
    })
    .catch(() => {
        this.disabled = false;
        this.textContent = 'Yes, Confirm Payment';
        mainBtn.disabled = false;
        modalMsg.textContent = 'Network error. Please try again.';
        modalMsg.className = 'form-msg error';
        modalMsg.style.display = 'block';
        showToast('Network error. Please try again.');
    });
});

function showReceipt(r) {
    const itemsHtml = r.items.map(i => `
        <div class="receipt-line"><span>${escapeHtml(i.product_name)} x${i.quantity}</span><span>${money(i.subtotal)}</span></div>
    `).join('');
    const totalItems = r.items.reduce((sum, i) => sum + i.quantity, 0);
    const totalLines = r.items.length;

    document.getElementById('receiptContent').innerHTML = `
        <div class="receipt-center"><strong>MR. DIY</strong><br>Sale Receipt</div>
        <hr>
        <div class="receipt-line"><span>Receipt #</span><span>${r.receipt_number}</span></div>
        <div class="receipt-line"><span>Date</span><span>${r.sale_date}</span></div>
        <hr>
        ${itemsHtml}
        <hr>
        <div class="receipt-line"><span>Total Items</span><span>${totalItems} pc(s) (${totalLines} product${totalLines === 1 ? '' : 's'})</span></div>
        <hr>
        <div class="receipt-line"><span>Subtotal</span><span>${money(r.subtotal)}</span></div>
        <div class="receipt-line"><span>Discount</span><span>-${money(r.discount_amount)}</span></div>
        <div class="receipt-line"><span>Tax</span><span>${money(r.tax_amount)}</span></div>
        <div class="receipt-line"><strong>Total</strong><strong>${money(r.total_amount)}</strong></div>
        <hr>
        <div class="receipt-line"><span>Payment</span><span>${r.payment_method}</span></div>
        <div class="receipt-line"><span>Amount Paid</span><span>${money(r.amount_paid)}</span></div>
        <div class="receipt-line"><span>Change</span><span>${money(r.change_amount)}</span></div>
        <hr>
        <div class="receipt-center">Thank you for shopping!</div>
    `;
    document.getElementById('receiptModal').classList.add('open');
}

document.getElementById('printReceiptBtn').addEventListener('click', () => window.print());
document.getElementById('newSaleBtn').addEventListener('click', () => location.reload());
document.querySelectorAll('.js-close-modal').forEach(btn => {
    btn.addEventListener('click', () => { location.reload(); });
});

// ---------- Global keyboard shortcuts (mouseless operation) ----------
document.addEventListener('keydown', function (e) {
    const tag = (document.activeElement.tagName || '').toLowerCase();
    const inTextField = tag === 'input' || tag === 'textarea' || tag === 'select';

    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('barcodeInput').focus();
        return;
    }
    if (e.key === 'F3') {
        e.preventDefault();
        document.getElementById('productSearch').focus();
        return;
    }
    if (e.key === 'Enter' && e.ctrlKey) {
        e.preventDefault();
        document.getElementById('confirmPaymentBtn').click();
        return;
    }
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        searchResults.classList.remove('open');
        return;
    }
    // Quick payment method select via 1-4, only when not typing in a field
    if (!inTextField && ['1', '2', '3', '4'].includes(e.key)) {
        const methods = ['Cash', 'GCash', 'Maya', 'Bank'];
        selectPaymentMethod(methods[parseInt(e.key) - 1]);
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
