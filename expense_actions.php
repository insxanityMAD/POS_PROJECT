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

        // ---------- Add expense ----------
        case 'add_expense': {
            $category    = trim($_POST['expense_category'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $amount      = (float)($_POST['amount'] ?? 0);
            $date        = trim($_POST['expense_date'] ?? '');
            $userId      = (int)$_SESSION['user_id'];

            if ($category === '' || $amount <= 0 || $date === '') {
                respond(false, 'Category, amount, and date are required.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO expenses (user_id, expense_category, description, amount, expense_date)
                 VALUES (?,?,?,?,?)"
            );
            $stmt->execute([$userId, $category, $description, $amount, $date]);
            $newId = (int)$pdo->lastInsertId();

            logAction($pdo, $userId, 'CREATE', 'Expenses', $newId, "Added expense \"$category\" - ₱" . number_format($amount, 2));

            respond(true, 'Expense added successfully.');
        }

        // ---------- Edit expense ----------
        case 'edit_expense': {
            $expenseId   = (int)($_POST['expense_id'] ?? 0);
            $category    = trim($_POST['expense_category'] ?? '');
            $description = trim($_POST['description'] ?? '') ?: null;
            $amount      = (float)($_POST['amount'] ?? 0);
            $date        = trim($_POST['expense_date'] ?? '');
            $userId      = (int)$_SESSION['user_id'];

            if ($expenseId <= 0 || $category === '' || $amount <= 0 || $date === '') {
                respond(false, 'Category, amount, and date are required.');
            }

            $stmt = $pdo->prepare(
                "UPDATE expenses SET expense_category=?, description=?, amount=?, expense_date=? WHERE expense_id = ?"
            );
            $stmt->execute([$category, $description, $amount, $date, $expenseId]);

            logAction($pdo, $userId, 'UPDATE', 'Expenses', $expenseId, "Updated expense \"$category\" - ₱" . number_format($amount, 2));

            respond(true, 'Expense updated successfully.');
        }

        // ---------- Delete expense ----------
        case 'delete_expense': {
            $expenseId = (int)($_POST['expense_id'] ?? 0);
            $userId    = (int)$_SESSION['user_id'];

            if ($expenseId <= 0) respond(false, 'Invalid expense.');

            $lookup = $pdo->prepare("SELECT expense_category, amount FROM expenses WHERE expense_id = ?");
            $lookup->execute([$expenseId]);
            $exp = $lookup->fetch();
            if (!$exp) respond(false, 'Expense not found.');

            $pdo->prepare("DELETE FROM expenses WHERE expense_id = ?")->execute([$expenseId]);

            logAction($pdo, $userId, 'DEACTIVATE', 'Expenses', $expenseId, "Deleted expense \"{$exp['expense_category']}\" - ₱" . number_format((float)$exp['amount'], 2));

            respond(true, 'Expense deleted.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    error_log('expense_actions error: ' . $e->getMessage());
    respond(false, 'Something went wrong. Please try again.');
}
