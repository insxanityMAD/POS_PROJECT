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

const OVERTIME_MULTIPLIER = 1.25; // overtime paid at 1.25x the hourly rate

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {

        // ---------- Add pay period ----------
        case 'add_pay_period': {
            $name  = trim($_POST['period_name'] ?? '');
            $start = trim($_POST['start_date'] ?? '');
            $end   = trim($_POST['end_date'] ?? '');
            $payDate = trim($_POST['pay_date'] ?? '') ?: null;

            if ($name === '' || $start === '' || $end === '') {
                respond(false, 'Period name, start date, and end date are required.');
            }
            if ($end < $start) {
                respond(false, 'End date must be after the start date.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO pay_periods (period_name, start_date, end_date, pay_date, status) VALUES (?,?,?,?, 'Open')"
            );
            $stmt->execute([$name, $start, $end, $payDate]);
            logAction($pdo, $_SESSION['user_id'], 'CREATE', 'Payroll', (int)$pdo->lastInsertId(), "Created pay period \"$name\"");
            respond(true, 'Pay period created.');
        }

        // ---------- Close a pay period ----------
        case 'close_pay_period': {
            $periodId = (int)($_POST['period_id'] ?? 0);
            if ($periodId <= 0) respond(false, 'Invalid period.');

            $stmt = $pdo->prepare("UPDATE pay_periods SET status = 'Closed' WHERE period_id = ?");
            $stmt->execute([$periodId]);
            logAction($pdo, $_SESSION['user_id'], 'UPDATE', 'Payroll', $periodId, 'Closed pay period');
            respond(true, 'Pay period closed.');
        }

        // ---------- Process (save) a payroll record ----------
        case 'process_payroll': {
            $payload = json_decode(file_get_contents('php://input'), true);
            if (!is_array($payload)) respond(false, 'Invalid request.');

            $periodId      = (int)($payload['period_id'] ?? 0);
            $userId        = (int)($payload['user_id'] ?? 0);
            $hourlyRate    = (float)($payload['hourly_rate'] ?? 0);
            $regularHours  = (float)($payload['regular_hours'] ?? 0);
            $overtimeHours = (float)($payload['overtime_hours'] ?? 0);
            $deductions    = is_array($payload['deductions'] ?? null) ? $payload['deductions'] : [];

            if ($periodId <= 0 || $userId <= 0) {
                respond(false, 'Please select a pay period and an employee.');
            }
            if ($hourlyRate <= 0 || ($regularHours <= 0 && $overtimeHours <= 0)) {
                respond(false, 'Enter a valid hourly rate and work hours.');
            }

            // ---------- Server-side computation (authoritative) ----------
            $grossPay = round(($hourlyRate * $regularHours) + ($hourlyRate * OVERTIME_MULTIPLIER * $overtimeHours), 2);

            $totalDeductions = 0.0;
            $cleanDeductions = [];
            foreach ($deductions as $d) {
                $dName = trim($d['name'] ?? '');
                $dAmount = (float)($d['amount'] ?? 0);
                if ($dName === '' || $dAmount <= 0) continue;
                $totalDeductions += $dAmount;
                $cleanDeductions[] = ['name' => $dName, 'amount' => $dAmount];
            }
            $totalDeductions = round($totalDeductions, 2);
            $netPay = round($grossPay - $totalDeductions, 2);

            $pdo->beginTransaction();

            // One payroll record per employee per period - update if it already exists
            $existing = $pdo->prepare("SELECT payroll_id FROM payroll WHERE period_id = ? AND user_id = ?");
            $existing->execute([$periodId, $userId]);
            $existingId = $existing->fetchColumn();

            if ($existingId) {
                $payrollId = (int)$existingId;
                $upd = $pdo->prepare(
                    "UPDATE payroll SET hourly_rate=?, regular_hours=?, overtime_hours=?, gross_pay=?, total_deductions=?, net_pay=? WHERE payroll_id = ?"
                );
                $upd->execute([$hourlyRate, $regularHours, $overtimeHours, $grossPay, $totalDeductions, $netPay, $payrollId]);

                $pdo->prepare("DELETE FROM payroll_deductions WHERE payroll_id = ?")->execute([$payrollId]);
            } else {
                $ins = $pdo->prepare(
                    "INSERT INTO payroll (period_id, user_id, hourly_rate, regular_hours, overtime_hours, gross_pay, total_deductions, net_pay)
                     VALUES (?,?,?,?,?,?,?,?)"
                );
                $ins->execute([$periodId, $userId, $hourlyRate, $regularHours, $overtimeHours, $grossPay, $totalDeductions, $netPay]);
                $payrollId = (int)$pdo->lastInsertId();
            }

            $dedStmt = $pdo->prepare("INSERT INTO payroll_deductions (payroll_id, deduction_name, amount) VALUES (?,?,?)");
            foreach ($cleanDeductions as $d) {
                $dedStmt->execute([$payrollId, $d['name'], $d['amount']]);
            }

            $pdo->commit();

            $empLookup = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
            $empLookup->execute([$userId]);
            $empName = $empLookup->fetchColumn() ?: "user #$userId";
            logAction($pdo, $_SESSION['user_id'], $existingId ? 'UPDATE' : 'CREATE', 'Payroll', $payrollId, "Processed payroll for $empName - Net Pay: " . number_format($netPay, 2));

            respond(true, 'Payroll record saved.', [
                'summary' => [
                    'gross_pay' => $grossPay,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                ],
            ]);
        }

        // ---------- View a payroll record's deduction breakdown ----------
        case 'get_payroll_details': {
            $payrollId = (int)($_GET['payroll_id'] ?? 0);
            if ($payrollId <= 0) respond(false, 'Invalid record.');

            $stmt = $pdo->prepare(
                "SELECT p.*, u.full_name, pp.period_name
                 FROM payroll p
                 JOIN users u ON u.user_id = p.user_id
                 JOIN pay_periods pp ON pp.period_id = p.period_id
                 WHERE p.payroll_id = ?"
            );
            $stmt->execute([$payrollId]);
            $payroll = $stmt->fetch();
            if (!$payroll) respond(false, 'Record not found.');

            $dedStmt = $pdo->prepare("SELECT deduction_name, amount FROM payroll_deductions WHERE payroll_id = ?");
            $dedStmt->execute([$payrollId]);
            $payroll['deductions'] = $dedStmt->fetchAll();

            respond(true, '', ['payroll' => $payroll]);
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('payroll_actions error: ' . $e->getMessage());
    respond(false, 'Something went wrong. Please try again.');
}
