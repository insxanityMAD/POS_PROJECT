<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

// A page/action file may set $allowedRoles = [...] before requiring this
// guard to open itself up to Manager/Cashier. Default stays Admin-only.
$allowedRoles = $allowedRoles ?? ['Admin'];
$currentRole  = $_SESSION['role_name'] ?? '';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!in_array($currentRole, $allowedRoles, true)) {
    // Logged in, just not allowed on this page - send them somewhere valid
    // instead of bouncing them back to the login screen.
    header('Location: ' . ($currentRole === 'Cashier' ? 'pos_sales.php' : 'staff_panel.php'));
    exit;
}

// ---------- CSRF protection ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sentToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (!is_string($sentToken) || !hash_equals($_SESSION['csrf_token'], $sentToken)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Your session has expired. Please refresh the page and try again.']);
        exit;
    }
}
