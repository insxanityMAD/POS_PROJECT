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

        // ---------- Add supplier ----------
        case 'add_supplier': {
            $code    = trim($_POST['supplier_code'] ?? '');
            $name    = trim($_POST['supplier_name'] ?? '');
            $contact = trim($_POST['contact_person'] ?? '') ?: null;
            $phone   = trim($_POST['phone'] ?? '') ?: null;
            $email   = trim($_POST['email'] ?? '') ?: null;
            $address = trim($_POST['address'] ?? '') ?: null;

            if ($code === '' || $name === '') {
                respond(false, 'Supplier code and name are required.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO suppliers (supplier_code, supplier_name, contact_person, phone, email, address, status)
                 VALUES (?,?,?,?,?,?, 'Active')"
            );
            $stmt->execute([$code, $name, $contact, $phone, $email, $address]);
            $newSupplierId = (int)$pdo->lastInsertId();
            logAction($pdo, $_SESSION['user_id'], 'CREATE', 'Suppliers', $newSupplierId, "Added supplier \"$name\" ($code)");
            respond(true, 'Supplier added successfully.');
        }

        // ---------- Edit supplier ----------
        case 'edit_supplier': {
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $code    = trim($_POST['supplier_code'] ?? '');
            $name    = trim($_POST['supplier_name'] ?? '');
            $contact = trim($_POST['contact_person'] ?? '') ?: null;
            $phone   = trim($_POST['phone'] ?? '') ?: null;
            $email   = trim($_POST['email'] ?? '') ?: null;
            $address = trim($_POST['address'] ?? '') ?: null;

            if ($supplierId <= 0 || $code === '' || $name === '') {
                respond(false, 'Supplier code and name are required.');
            }

            $stmt = $pdo->prepare(
                "UPDATE suppliers SET supplier_code=?, supplier_name=?, contact_person=?, phone=?, email=?, address=?
                 WHERE supplier_id = ?"
            );
            $stmt->execute([$code, $name, $contact, $phone, $email, $address, $supplierId]);
            logAction($pdo, $_SESSION['user_id'], 'UPDATE', 'Suppliers', $supplierId, "Updated supplier \"$name\" ($code)");
            respond(true, 'Supplier updated successfully.');
        }

        // ---------- Deactivate / Reactivate supplier ----------
        case 'toggle_supplier_status': {
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $newStatus  = $_POST['new_status'] ?? '';

            if ($supplierId <= 0 || !in_array($newStatus, ['Active', 'Inactive'], true)) {
                respond(false, 'Invalid request.');
            }

            $stmt = $pdo->prepare("UPDATE suppliers SET status = ? WHERE supplier_id = ?");
            $stmt->execute([$newStatus, $supplierId]);
            logAction($pdo, $_SESSION['user_id'], $newStatus === 'Inactive' ? 'DEACTIVATE' : 'REACTIVATE', 'Suppliers', $supplierId, "Supplier status changed to $newStatus");
            respond(true, $newStatus === 'Inactive' ? 'Supplier deactivated.' : 'Supplier reactivated.');
        }

        // ---------- Get products linked to a supplier (for the manage-products modal) ----------
        case 'get_linked_products': {
            $supplierId = (int)($_GET['supplier_id'] ?? 0);
            if ($supplierId <= 0) respond(false, 'Invalid supplier.');

            $stmt = $pdo->prepare(
                "SELECT ps.product_supplier_id, ps.supplier_product_code, ps.last_cost_price,
                        p.product_id, p.product_name, p.product_code
                 FROM product_suppliers ps
                 JOIN products p ON p.product_id = ps.product_id
                 WHERE ps.supplier_id = ?
                 ORDER BY p.product_name"
            );
            $stmt->execute([$supplierId]);
            respond(true, '', ['products' => $stmt->fetchAll()]);
        }

        // ---------- Link a product to a supplier ----------
        case 'link_product': {
            $supplierId  = (int)($_POST['supplier_id'] ?? 0);
            $productId   = (int)($_POST['product_id'] ?? 0);
            $supplierCode = trim($_POST['supplier_product_code'] ?? '') ?: null;
            $lastCost    = ($_POST['last_cost_price'] ?? '') !== '' ? (float)$_POST['last_cost_price'] : null;

            if ($supplierId <= 0 || $productId <= 0) {
                respond(false, 'Please select a product.');
            }

            // A product can only be linked to ONE supplier at a time.
            $existingLink = $pdo->prepare(
                "SELECT s.supplier_name
                 FROM product_suppliers ps
                 JOIN suppliers s ON s.supplier_id = ps.supplier_id
                 WHERE ps.product_id = ?"
            );
            $existingLink->execute([$productId]);
            $existingSupplierName = $existingLink->fetchColumn();

            if ($existingSupplierName) {
                respond(false, 'This product is already linked to ' . $existingSupplierName . '. Unlink it there first before linking it to a different supplier.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO product_suppliers (product_id, supplier_id, supplier_product_code, last_cost_price)
                 VALUES (?,?,?,?)"
            );
            $stmt->execute([$productId, $supplierId, $supplierCode, $lastCost]);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM product_suppliers WHERE supplier_id = ?");
            $countStmt->execute([$supplierId]);
            $linkedCount = (int)$countStmt->fetchColumn();

            logAction($pdo, $_SESSION['user_id'], 'LINK', 'Suppliers', $supplierId, "Linked product #$productId to supplier #$supplierId");

            respond(true, 'Product linked to supplier.', ['linked_count' => $linkedCount]);
        }

        // ---------- Unlink a product from a supplier ----------
        case 'unlink_product': {
            $linkId = (int)($_POST['product_supplier_id'] ?? 0);
            if ($linkId <= 0) respond(false, 'Invalid request.');

            $supplierLookup = $pdo->prepare("SELECT supplier_id FROM product_suppliers WHERE product_supplier_id = ?");
            $supplierLookup->execute([$linkId]);
            $supplierId = $supplierLookup->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM product_suppliers WHERE product_supplier_id = ?");
            $stmt->execute([$linkId]);

            $linkedCount = 0;
            if ($supplierId) {
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM product_suppliers WHERE supplier_id = ?");
                $countStmt->execute([$supplierId]);
                $linkedCount = (int)$countStmt->fetchColumn();
            }

            logAction($pdo, $_SESSION['user_id'], 'UNLINK', 'Suppliers', $supplierId ?: null, "Unlinked a product (link #$linkId)");

            respond(true, 'Product unlinked.', ['linked_count' => $linkedCount, 'supplier_id' => $supplierId]);
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    error_log('supplier_actions error: ' . $e->getMessage());
    if ($e->getCode() === '23000') {
        respond(false, 'That supplier code is already in use, or this product is already linked.');
    }
    respond(false, 'Something went wrong. Please try again.');
}
