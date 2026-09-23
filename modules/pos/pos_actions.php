<?php
declare(strict_types=1);
$allowedRoles = ['Admin', 'Manager', 'Cashier'];
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../includes/log_helper.php';

header('Content-Type: application/json');

function respond(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // ---------- Live search-as-you-type ----------
        case 'search_products': {
            $q = trim($_GET['q'] ?? '');
            if ($q === '') respond(true, '', ['products' => []]);

            $stmt = $pdo->prepare(
                "SELECT product_id, product_name, barcode, product_code, selling_price, stock_quantity
                 FROM products
                 WHERE status = 'Active'
                   AND (product_name LIKE ? OR barcode LIKE ? OR product_code LIKE ?)
                 ORDER BY product_name
                 LIMIT 10"
            );
            $like = "%$q%";
            $stmt->execute([$like, $like, $like]);
            respond(true, '', ['products' => $stmt->fetchAll()]);
        }

        // ---------- Exact barcode lookup (scanner / manual entry + Enter) ----------
        case 'lookup_barcode': {
            $code = trim($_GET['barcode'] ?? '');
            if ($code === '') respond(false, 'No barcode entered.');

            $stmt = $pdo->prepare(
                "SELECT product_id, product_name, barcode, product_code, selling_price, stock_quantity
                 FROM products WHERE status='Active' AND (barcode = ? OR product_code = ?) LIMIT 1"
            );
            $stmt->execute([$code, $code]);
            $product = $stmt->fetch();

            if (!$product) respond(false, 'No product found for that code.');
            respond(true, '', ['product' => $product]);
        }

        // ---------- Complete a sale ----------
        case 'complete_sale': {
            $payload = json_decode(file_get_contents('php://input'), true);
            if (!is_array($payload)) respond(false, 'Invalid request.');

            $items          = $payload['items'] ?? [];
            $discountId     = !empty($payload['discount_id']) ? (int)$payload['discount_id'] : null;
            $customerIdNum  = trim($payload['customer_id_number'] ?? '') ?: null;
            $taxId          = !empty($payload['tax_id']) ? (int)$payload['tax_id'] : null;
            $paymentMethod  = $payload['payment_method'] ?? '';
            $amountPaid     = (float)($payload['amount_paid'] ?? 0);
            $referenceNum   = trim($payload['reference_number'] ?? '') ?: null;
            $userId         = (int)$_SESSION['user_id'];

            if (empty($items) || !is_array($items)) {
                respond(false, 'Cart is empty.');
            }
            if (!in_array($paymentMethod, ['Cash', 'GCash', 'Maya', 'Bank'], true)) {
                respond(false, 'Please select a valid payment method.');
            }

            $pdo->beginTransaction();

            // Re-fetch authoritative prices & stock from DB (never trust client-sent prices)
            $subtotal = 0.0;
            $lineItems = [];
            foreach ($items as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $quantity  = (int)($item['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    throw new RuntimeException('Invalid cart item.');
                }

                $pStmt = $pdo->prepare("SELECT product_name, selling_price, stock_quantity FROM products WHERE product_id = ? AND status='Active' FOR UPDATE");
                $pStmt->execute([$productId]);
                $product = $pStmt->fetch();

                if (!$product) throw new RuntimeException('A product in your cart is no longer available.');
                if ($quantity > (int)$product['stock_quantity']) {
                    throw new RuntimeException('Not enough stock for ' . $product['product_name'] . ' (only ' . $product['stock_quantity'] . ' left).');
                }

                $lineSubtotal = (float)$product['selling_price'] * $quantity;
                $subtotal += $lineSubtotal;
                $lineItems[] = [
                    'product_id'   => $productId,
                    'product_name' => $product['product_name'],
                    'quantity'     => $quantity,
                    'unit_price'   => (float)$product['selling_price'],
                    'subtotal'     => $lineSubtotal,
                ];
            }

            // Discount
            $discountRate = 0.0;
            if ($discountId) {
                $dStmt = $pdo->prepare("SELECT discount_rate, requires_id FROM discount_types WHERE discount_id = ? AND is_active = 1");
                $dStmt->execute([$discountId]);
                $discount = $dStmt->fetch();
                if ($discount) {
                    if ((bool)$discount['requires_id'] && !$customerIdNum) {
                        throw new RuntimeException('An ID number is required for this discount.');
                    }
                    $discountRate = (float)$discount['discount_rate'];
                }
            }
            $discountAmount = round($subtotal * ($discountRate / 100), 2);

            // Tax
            $taxRate = 0.0;
            if ($taxId) {
                $tStmt = $pdo->prepare("SELECT tax_rate FROM tax_settings WHERE tax_id = ?");
                $tStmt->execute([$taxId]);
                $tax = $tStmt->fetch();
                if ($tax) $taxRate = (float)$tax['tax_rate'];
            }
            $taxableAmount = $subtotal - $discountAmount;
            $taxAmount = round($taxableAmount * ($taxRate / 100), 2);

            $totalAmount = round($taxableAmount + $taxAmount, 2);

            if ($paymentMethod === 'Cash' && $amountPaid < $totalAmount) {
                throw new RuntimeException('Amount paid is less than the total amount due.');
            }
            $changeAmount = $paymentMethod === 'Cash' ? round($amountPaid - $totalAmount, 2) : 0.00;
            if ($paymentMethod !== 'Cash') $amountPaid = $totalAmount;

            $receiptNumber = 'RCT' . date('Ymd') . strtoupper(substr(uniqid(), -6));

            $saleStmt = $pdo->prepare(
                "INSERT INTO sales
                    (receipt_number, user_id, subtotal, discount_id, discount_amount,
                     customer_id_number, tax_id, tax_amount, total_amount, sale_status)
                 VALUES (?,?,?,?,?,?,?,?,?, 'Completed')"
            );
            $saleStmt->execute([
                $receiptNumber, $userId, $subtotal, $discountId, $discountAmount,
                $customerIdNum, $taxId, $taxAmount, $totalAmount,
            ]);
            $saleId = (int)$pdo->lastInsertId();

            $itemStmt  = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?,?,?,?,?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $invStmt   = $pdo->prepare("INSERT INTO inventory_transactions (product_id, user_id, transaction_type, quantity, remarks) VALUES (?,?, 'SALE', ?, ?)");

            foreach ($lineItems as $li) {
                $itemStmt->execute([$saleId, $li['product_id'], $li['quantity'], $li['unit_price'], $li['subtotal']]);
                $stockStmt->execute([$li['quantity'], $li['product_id']]);
                $invStmt->execute([$li['product_id'], $userId, $li['quantity'], 'Sale #' . $receiptNumber]);
            }

            $payStmt = $pdo->prepare(
                "INSERT INTO payments (sale_id, payment_method, amount_paid, change_amount, reference_number)
                 VALUES (?,?,?,?,?)"
            );
            $payStmt->execute([$saleId, $paymentMethod, $amountPaid, $changeAmount, $referenceNum]);

            $pdo->commit();

            logAction($pdo, $userId, 'SALE', 'POS', $saleId, "Completed sale $receiptNumber - Total: " . number_format($totalAmount, 2) . " via $paymentMethod");

            respond(true, 'Sale completed.', [
                'receipt' => [
                    'receipt_number' => $receiptNumber,
                    'sale_date'      => date('M j, Y g:i A'),
                    'items'          => array_map(function ($li) {
                        return $li;
                    }, $lineItems),
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount'      => $taxAmount,
                    'total_amount'    => $totalAmount,
                    'payment_method'  => $paymentMethod,
                    'amount_paid'     => $amountPaid,
                    'change_amount'   => $changeAmount,
                ],
            ]);
        }

        // ---------- Recent sales for the Transaction History list ----------
        case 'get_recent_sales': {
            $stmt = $pdo->query(
                "SELECT s.sale_id, s.receipt_number, s.sale_date, s.total_amount, s.sale_status, u.full_name
                 FROM sales s
                 JOIN users u ON u.user_id = s.user_id
                 ORDER BY s.sale_date DESC
                 LIMIT 100"
            );
            respond(true, '', ['sales' => $stmt->fetchAll()]);
        }

        // ---------- Void a completed sale (Admin/Manager only) ----------
        case 'void_sale': {
            if (!in_array($_SESSION['role_name'] ?? '', ['Admin', 'Manager'], true)) {
                respond(false, 'Only an Admin or Manager can void a sale.');
            }

            $saleId = (int)($_POST['sale_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '') ?: null;
            $userId = (int)$_SESSION['user_id'];

            if ($saleId <= 0) respond(false, 'Invalid sale.');

            $pdo->beginTransaction();

            $saleStmt = $pdo->prepare("SELECT receipt_number, sale_status FROM sales WHERE sale_id = ? FOR UPDATE");
            $saleStmt->execute([$saleId]);
            $sale = $saleStmt->fetch();

            if (!$sale) throw new RuntimeException('Sale not found.');
            if ($sale['sale_status'] === 'Voided') throw new RuntimeException('This sale has already been voided.');

            // Restore stock for every item on this sale
            $itemsStmt = $pdo->prepare("SELECT product_id, quantity FROM sale_items WHERE sale_id = ?");
            $itemsStmt->execute([$saleId]);
            $items = $itemsStmt->fetchAll();

            $restoreStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
            $invStmt = $pdo->prepare(
                "INSERT INTO inventory_transactions (product_id, user_id, transaction_type, quantity, remarks) VALUES (?,?, 'RETURN', ?, ?)"
            );
            foreach ($items as $it) {
                $restoreStmt->execute([$it['quantity'], $it['product_id']]);
                $invStmt->execute([$it['product_id'], $userId, $it['quantity'], 'Voided sale ' . $sale['receipt_number']]);
            }

            $voidStmt = $pdo->prepare("UPDATE sales SET sale_status = 'Voided' WHERE sale_id = ?");
            $voidStmt->execute([$saleId]);

            $pdo->commit();

            $desc = 'Voided sale ' . $sale['receipt_number'] . ($reason ? " - Reason: $reason" : '');
            logAction($pdo, $userId, 'VOID', 'POS', $saleId, $desc);

            respond(true, 'Sale voided and stock restored.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(false, $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('pos_actions error: ' . $e->getMessage());
    respond(false, 'Something went wrong. Please try again.');
}
