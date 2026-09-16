<?php
declare(strict_types=1);
$allowedRoles = ['Admin', 'Manager'];
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

        // ---------- Record a loss (expired / damaged / missing / other) ----------
        case 'add_loss': {
            $productId = (int)($_POST['product_id'] ?? 0);
            $quantity  = (int)($_POST['quantity'] ?? 0);
            $reason    = $_POST['reason'] ?? '';
            $remarks   = trim($_POST['remarks'] ?? '') ?: null;
            $userId    = (int)$_SESSION['user_id'];

            if ($productId <= 0 || $quantity <= 0) {
                respond(false, 'Please select a product and enter a quantity greater than 0.');
            }
            if (!in_array($reason, ['Expired', 'Damaged', 'Missing', 'Other'], true)) {
                respond(false, 'Please select a valid reason.');
            }

            $pdo->beginTransaction();

            $pStmt = $pdo->prepare("SELECT product_name, stock_quantity FROM products WHERE product_id = ? FOR UPDATE");
            $pStmt->execute([$productId]);
            $product = $pStmt->fetch();
            if (!$product) throw new RuntimeException('Product not found.');
            if ($quantity > (int)$product['stock_quantity']) {
                throw new RuntimeException('Cannot record a loss greater than current stock (' . $product['stock_quantity'] . ' available).');
            }

            $lossStmt = $pdo->prepare(
                "INSERT INTO losses (product_id, user_id, quantity, reason, remarks) VALUES (?,?,?,?,?)"
            );
            $lossStmt->execute([$productId, $userId, $quantity, $reason, $remarks]);

            $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?")
                ->execute([$quantity, $productId]);

            $pdo->prepare(
                "INSERT INTO inventory_transactions (product_id, user_id, transaction_type, quantity, remarks) VALUES (?,?, 'LOSS', ?, ?)"
            )->execute([$productId, $userId, $quantity, "$reason" . ($remarks ? " - $remarks" : '')]);

            $pdo->commit();

            logAction($pdo, $userId, 'CREATE', 'Losses', $productId, "Recorded loss of $quantity unit(s) of \"{$product['product_name']}\" ($reason)");

            respond(true, 'Loss recorded successfully.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(false, $e->getMessage());
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('report_actions error: ' . $e->getMessage());
    respond(false, 'Something went wrong. Please try again.');
}
