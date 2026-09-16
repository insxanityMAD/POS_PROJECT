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

        // ---------- Add user ----------
        case 'add_user': {
            $roleId    = (int)($_POST['role_id'] ?? 0);
            $username  = trim($_POST['username'] ?? '');
            $password  = (string)($_POST['password'] ?? '');
            $fullName  = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '') ?: null;

            if ($roleId <= 0 || $username === '' || $password === '' || $fullName === '') {
                respond(false, 'Role, username, password, and full name are required.');
            }
            if (strlen($password) < 6) {
                respond(false, 'Password must be at least 6 characters.');
            }

            $stmt = $pdo->prepare(
                "INSERT INTO users (role_id, username, password, full_name, email, status)
                 VALUES (?,?,?,?,?, 'Active')"
            );
            $stmt->execute([$roleId, $username, password_hash($password, PASSWORD_DEFAULT), $fullName, $email]);
            $newId = (int)$pdo->lastInsertId();

            logAction($pdo, $_SESSION['user_id'], 'CREATE', 'Users', $newId, "Created user account \"$username\" ($fullName)");

            respond(true, 'User account created successfully.');
        }

        // ---------- Edit user ----------
        case 'edit_user': {
            $userId    = (int)($_POST['user_id'] ?? 0);
            $roleId    = (int)($_POST['role_id'] ?? 0);
            $username  = trim($_POST['username'] ?? '');
            $password  = (string)($_POST['password'] ?? ''); // optional - only change if provided
            $fullName  = trim($_POST['full_name'] ?? '');
            $email     = trim($_POST['email'] ?? '') ?: null;

            if ($userId <= 0 || $roleId <= 0 || $username === '' || $fullName === '') {
                respond(false, 'Role, username, and full name are required.');
            }

            if ($password !== '') {
                if (strlen($password) < 6) {
                    respond(false, 'Password must be at least 6 characters.');
                }
                $stmt = $pdo->prepare(
                    "UPDATE users SET role_id=?, username=?, full_name=?, email=?, password=? WHERE user_id = ?"
                );
                $stmt->execute([$roleId, $username, $fullName, $email, password_hash($password, PASSWORD_DEFAULT), $userId]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE users SET role_id=?, username=?, full_name=?, email=? WHERE user_id = ?"
                );
                $stmt->execute([$roleId, $username, $fullName, $email, $userId]);
            }

            logAction($pdo, $_SESSION['user_id'], 'UPDATE', 'Users', $userId, "Updated user account \"$username\" ($fullName)");

            respond(true, 'User account updated successfully.');
        }

        // ---------- Deactivate / Reactivate user ----------
        case 'toggle_user_status': {
            $userId    = (int)($_POST['user_id'] ?? 0);
            $newStatus = $_POST['new_status'] ?? '';

            if ($userId <= 0 || !in_array($newStatus, ['Active', 'Inactive'], true)) {
                respond(false, 'Invalid request.');
            }
            if ($userId === (int)$_SESSION['user_id']) {
                respond(false, 'You cannot deactivate your own account while logged in.');
            }

            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
            $stmt->execute([$newStatus, $userId]);

            logAction($pdo, $_SESSION['user_id'], $newStatus === 'Inactive' ? 'DEACTIVATE' : 'REACTIVATE', 'Users', $userId, "User account status changed to $newStatus");

            respond(true, $newStatus === 'Inactive' ? 'User deactivated.' : 'User reactivated.');
        }

        default:
            respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    error_log('user_actions error: ' . $e->getMessage());
    if ($e->getCode() === '23000') {
        respond(false, 'That username is already in use.');
    }
    respond(false, 'Something went wrong. Please try again.');
}
