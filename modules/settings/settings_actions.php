<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../includes/log_helper.php';

header('Content-Type: application/json');

function respond(bool $success, string $message, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$action = $_REQUEST['action'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

try {
    switch ($action) {

        // ================= TAX RATES =================
        case 'add_tax': {
            $name = trim($_POST['tax_name'] ?? '');
            $rate = (float)($_POST['tax_rate'] ?? -1);
            $isActive = !empty($_POST['is_active']);

            if ($name === '' || $rate < 0) respond(false, 'Tax name and a valid rate are required.');

            if ($isActive) {
                $pdo->exec("UPDATE tax_settings SET is_active = 0");
            }
            $stmt = $pdo->prepare("INSERT INTO tax_settings (tax_name, tax_rate, is_active) VALUES (?,?,?)");
            $stmt->execute([$name, $rate, $isActive ? 1 : 0]);

            logAction($pdo, $userId, 'CREATE', 'Settings', (int)$pdo->lastInsertId(), "Added tax rate \"$name\" ($rate%)");
            respond(true, 'Tax rate added.');
        }

        case 'edit_tax': {
            $taxId = (int)($_POST['tax_id'] ?? 0);
            $name = trim($_POST['tax_name'] ?? '');
            $rate = (float)($_POST['tax_rate'] ?? -1);
            $isActive = !empty($_POST['is_active']);

            if ($taxId <= 0 || $name === '' || $rate < 0) respond(false, 'Tax name and a valid rate are required.');

            if ($isActive) {
                $pdo->exec("UPDATE tax_settings SET is_active = 0");
            }
            $stmt = $pdo->prepare("UPDATE tax_settings SET tax_name=?, tax_rate=?, is_active=? WHERE tax_id=?");
            $stmt->execute([$name, $rate, $isActive ? 1 : 0, $taxId]);

            logAction($pdo, $userId, 'UPDATE', 'Settings', $taxId, "Updated tax rate \"$name\" ($rate%)");
            respond(true, 'Tax rate updated.');
        }

        case 'toggle_tax': {
            $taxId = (int)($_POST['tax_id'] ?? 0);
            $newStatus = !empty($_POST['is_active']);
            if ($taxId <= 0) respond(false, 'Invalid request.');

            if ($newStatus) {
                $pdo->exec("UPDATE tax_settings SET is_active = 0");
            }
            $pdo->prepare("UPDATE tax_settings SET is_active=? WHERE tax_id=?")->execute([$newStatus ? 1 : 0, $taxId]);

            logAction($pdo, $userId, $newStatus ? 'REACTIVATE' : 'DEACTIVATE', 'Settings', $taxId, 'Tax rate active status changed');
            respond(true, 'Updated.');
        }

        // ================= DISCOUNT TYPES =================
        case 'add_discount': {
            $name = trim($_POST['discount_name'] ?? '');
            $rate = (float)($_POST['discount_rate'] ?? -1);
            $requiresId = !empty($_POST['requires_id']);

            if ($name === '' || $rate < 0) respond(false, 'Discount name and a valid rate are required.');

            $stmt = $pdo->prepare("INSERT INTO discount_types (discount_name, discount_rate, requires_id, is_active) VALUES (?,?,?,1)");
            $stmt->execute([$name, $rate, $requiresId ? 1 : 0]);

            logAction($pdo, $userId, 'CREATE', 'Settings', (int)$pdo->lastInsertId(), "Added discount type \"$name\" ($rate%)");
            respond(true, 'Discount type added.');
        }

        case 'edit_discount': {
            $discountId = (int)($_POST['discount_id'] ?? 0);
            $name = trim($_POST['discount_name'] ?? '');
            $rate = (float)($_POST['discount_rate'] ?? -1);
            $requiresId = !empty($_POST['requires_id']);

            if ($discountId <= 0 || $name === '' || $rate < 0) respond(false, 'Discount name and a valid rate are required.');

            $stmt = $pdo->prepare("UPDATE discount_types SET discount_name=?, discount_rate=?, requires_id=? WHERE discount_id=?");
            $stmt->execute([$name, $rate, $requiresId ? 1 : 0, $discountId]);

            logAction($pdo, $userId, 'UPDATE', 'Settings', $discountId, "Updated discount type \"$name\" ($rate%)");
            respond(true, 'Discount type updated.');
        }

        case 'toggle_discount_status': {
            $discountId = (int)($_POST['discount_id'] ?? 0);
            $newStatus = $_POST['new_status'] ?? '';
            if ($discountId <= 0 || !in_array($newStatus, ['1', '0'], true)) respond(false, 'Invalid request.');

            $pdo->prepare("UPDATE discount_types SET is_active=? WHERE discount_id=?")->execute([$newStatus, $discountId]);

            logAction($pdo, $userId, $newStatus === '1' ? 'REACTIVATE' : 'DEACTIVATE', 'Settings', $discountId, 'Discount type active status changed');
            respond(true, 'Updated.');
        }

        // ================= QR PAYMENT SETTINGS =================
        case 'update_qr': {
            $qrId = (int)($_POST['qr_id'] ?? 0);
            $accountName = trim($_POST['account_name'] ?? '') ?: null;
            $accountNumber = trim($_POST['account_number'] ?? '') ?: null;
            $qrImagePath = trim($_POST['qr_image_path'] ?? '') ?: null;
            $isActive = !empty($_POST['is_active']);

            if ($qrId <= 0) respond(false, 'Invalid request.');

            $stmt = $pdo->prepare(
                "UPDATE qr_payment_settings SET account_name=?, account_number=?, qr_image_path=?, is_active=? WHERE qr_id=?"
            );
            $stmt->execute([$accountName, $accountNumber, $qrImagePath, $isActive ? 1 : 0, $qrId]);

            logAction($pdo, $userId, 'UPDATE', 'Settings', $qrId, 'Updated QR payment settings');
            respond(true, 'QR payment settings updated.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    error_log('settings_actions error: ' . $e->getMessage());
    respond(false, 'Something went wrong. Please try again.');
}
