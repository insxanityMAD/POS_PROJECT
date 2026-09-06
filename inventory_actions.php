<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/admin_guard.php';
require_once __DIR__ . '/includes/log_helper.php';

header('Content-Type: application/json');

function respond(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // ---------- Suppliers for a given product (restock dropdown) ----------
        case 'get_suppliers_for_product': {
            $productId = (int)($_GET['product_id'] ?? 0);

            $linked = $pdo->prepare(
                "SELECT s.supplier_id, s.supplier_name
                 FROM product_suppliers ps
                 JOIN suppliers s ON s.supplier_id = ps.supplier_id
                 WHERE ps.product_id = ? AND s.status = 'Active'
                 ORDER BY s.supplier_name"
            );
            $linked->execute([$productId]);
            $suppliers = $linked->fetchAll();

            // No fallback: only a supplier actually linked to this product may restock it.
            respond(true, '', ['suppliers' => $suppliers]);
        }

        // ---------- Add product ----------
        case 'add_product': {
            $categoryId    = (int)($_POST['category_id'] ?? 0);
            $productCode   = trim($_POST['product_code'] ?? '');
            $barcode       = trim($_POST['barcode'] ?? '');
            $productName   = trim($_POST['product_name'] ?? '');
            $description   = trim($_POST['description'] ?? '') ?: null;
            $costPrice     = (float)($_POST['cost_price'] ?? 0);
            $sellingPrice  = (float)($_POST['selling_price'] ?? 0);
            $stockQty      = (int)($_POST['stock_quantity'] ?? 0);
            $reorderLevel  = (int)($_POST['reorder_level'] ?? 5);
            $expiration    = trim($_POST['expiration_date'] ?? '') ?: null;
            $supplierId    = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
            $supplierCode  = trim($_POST['supplier_product_code'] ?? '') ?: null;

            if ($categoryId <= 0 || $productCode === '' || $barcode === '' || $productName === '') {
                respond(false, 'Category, product code, barcode, and product name are required.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO products
                    (category_id, product_code, barcode, product_name, description,
                     cost_price, selling_price, stock_quantity, reorder_level, expiration_date, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?, 'Active')"
            );
            $stmt->execute([
                $categoryId, $productCode, $barcode, $productName, $description,
                $costPrice, $sellingPrice, $stockQty, $reorderLevel, $expiration,
            ]);
            $newProductId = (int)$pdo->lastInsertId();

            if ($supplierId) {
                $link = $pdo->prepare(
                    "INSERT INTO product_suppliers (product_id, supplier_id, supplier_product_code, last_cost_price)
                     VALUES (?,?,?,?)"
                );
                $link->execute([$newProductId, $supplierId, $supplierCode, $costPrice ?: null]);
            }

            $pdo->commit();

            logAction($pdo, $_SESSION['user_id'], 'CREATE', 'Products', $newProductId, "Added product \"$productName\" ($productCode)");

            respond(true, 'Product added successfully.');
        }

        // ---------- Edit product ----------
        case 'edit_product': {
            $productId     = (int)($_POST['product_id'] ?? 0);
            $categoryId    = (int)($_POST['category_id'] ?? 0);
            $productCode   = trim($_POST['product_code'] ?? '');
            $barcode       = trim($_POST['barcode'] ?? '');
            $productName   = trim($_POST['product_name'] ?? '');
            $description   = trim($_POST['description'] ?? '') ?: null;
            $costPrice     = (float)($_POST['cost_price'] ?? 0);
            $sellingPrice  = (float)($_POST['selling_price'] ?? 0);
            $reorderLevel  = (int)($_POST['reorder_level'] ?? 5);
            $expiration    = trim($_POST['expiration_date'] ?? '') ?: null;
            $supplierId    = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
            $supplierCode  = trim($_POST['supplier_product_code'] ?? '') ?: null;

            if ($productId <= 0 || $categoryId <= 0 || $productCode === '' || $barcode === '' || $productName === '') {
                respond(false, 'Category, product code, barcode, and product name are required.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE products SET
                    category_id = ?, product_code = ?, barcode = ?, product_name = ?, description = ?,
                    cost_price = ?, selling_price = ?, reorder_level = ?, expiration_date = ?
                 WHERE product_id = ?"
            );
            $stmt->execute([
                $categoryId, $productCode, $barcode, $productName, $description,
                $costPrice, $sellingPrice, $reorderLevel, $expiration, $productId,
            ]);

            // Replace whatever supplier link existed (a product has at most one supplier)
            $pdo->prepare("DELETE FROM product_suppliers WHERE product_id = ?")->execute([$productId]);
            if ($supplierId) {
                $link = $pdo->prepare(
                    "INSERT INTO product_suppliers (product_id, supplier_id, supplier_product_code, last_cost_price)
                     VALUES (?,?,?,?)"
                );
                $link->execute([$productId, $supplierId, $supplierCode, $costPrice ?: null]);
            }

            $pdo->commit();

            logAction($pdo, $_SESSION['user_id'], 'UPDATE', 'Products', $productId, "Updated product \"$productName\" ($productCode)");

            respond(true, 'Product updated successfully.');
        }

        // ---------- Deactivate / Reactivate product ----------
        case 'toggle_product_status': {
            $productId = (int)($_POST['product_id'] ?? 0);
            $newStatus = $_POST['new_status'] ?? '';

            if ($productId <= 0 || !in_array($newStatus, ['Active', 'Inactive'], true)) {
                respond(false, 'Invalid request.');
            }

            $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE product_id = ?");
            $stmt->execute([$newStatus, $productId]);

            logAction($pdo, $_SESSION['user_id'], $newStatus === 'Inactive' ? 'DEACTIVATE' : 'REACTIVATE', 'Products', $productId, "Product status changed to $newStatus");

            respond(true, $newStatus === 'Inactive' ? 'Product deactivated.' : 'Product reactivated.');
        }

        // ---------- Deactivate / Reactivate category ----------
        case 'toggle_category_status': {
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $newStatus  = $_POST['new_status'] ?? '';

            if ($categoryId <= 0 || !in_array($newStatus, ['Active', 'Inactive'], true)) {
                respond(false, 'Invalid request.');
            }

            $stmt = $pdo->prepare("UPDATE categories SET status = ? WHERE category_id = ?");
            $stmt->execute([$newStatus, $categoryId]);

            logAction($pdo, $_SESSION['user_id'], $newStatus === 'Inactive' ? 'DEACTIVATE' : 'REACTIVATE', 'Categories', $categoryId, "Category status changed to $newStatus");

            respond(true, $newStatus === 'Inactive' ? 'Category deactivated.' : 'Category reactivated.');
        }

        // ---------- Add category ----------
        case 'add_category': {
            $name = trim($_POST['category_name'] ?? '');
            $desc = trim($_POST['description'] ?? '') ?: null;

            if ($name === '') {
                respond(false, 'Category name is required.');
            }

            $stmt = $pdo->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);

            logAction($pdo, $_SESSION['user_id'], 'CREATE', 'Categories', (int)$pdo->lastInsertId(), "Added category \"$name\"");

            respond(true, 'Category added successfully.');
        }

        // ---------- Restock ----------
        case 'restock': {
            $productId  = (int)($_POST['product_id'] ?? 0);
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $quantity   = (int)($_POST['quantity'] ?? 0);
            $remarks    = trim($_POST['remarks'] ?? '') ?: null;
            $userId     = (int)$_SESSION['user_id'];

            if ($productId <= 0 || $supplierId <= 0 || $quantity <= 0) {
                respond(false, 'Please select a product, a supplier, and enter a quantity greater than 0.');
            }

            // Enforce: only a supplier actually linked to this product may restock it.
            $linkCheck = $pdo->prepare("SELECT 1 FROM product_suppliers WHERE product_id = ? AND supplier_id = ?");
            $linkCheck->execute([$productId, $supplierId]);
            if (!$linkCheck->fetchColumn()) {
                respond(false, 'This supplier is not linked to the selected product. Link them first under Suppliers before restocking.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO inventory_transactions
                    (product_id, user_id, supplier_id, transaction_type, quantity, remarks)
                 VALUES (?,?,?, 'RESTOCK', ?, ?)"
            );
            $stmt->execute([$productId, $userId, $supplierId, $quantity, $remarks]);

            $update = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
            $update->execute([$quantity, $productId]);

            $pdo->commit();

            $nameLookup = $pdo->prepare(
                "SELECT p.product_name, s.supplier_name FROM products p, suppliers s
                 WHERE p.product_id = ? AND s.supplier_id = ?"
            );
            $nameLookup->execute([$productId, $supplierId]);
            $names = $nameLookup->fetch();
            $desc = $names
                ? "Restocked +{$quantity} units of \"{$names['product_name']}\" from {$names['supplier_name']}"
                : "Restocked +{$quantity} units (product #$productId)";
            logAction($pdo, $userId, 'RESTOCK', 'Inventory', $productId, $desc);

            respond(true, 'Restock recorded successfully.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('inventory_actions error: ' . $e->getMessage());

    // Friendly message for common duplicate-key errors
    if ($e->getCode() === '23000') {
        respond(false, 'That product code or barcode is already in use.');
    }
    respond(false, 'Something went wrong. Please try again.');
}
