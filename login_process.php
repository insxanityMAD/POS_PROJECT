<?php
declare(strict_types=1);
session_start();
require_once 'config.php';
require_once 'includes/log_helper.php';

function backToLogin(string $message, string $username = ''): void
{
    $_SESSION['login_error']  = $message;
    $_SESSION['old_username'] = $username;
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

// ---------- Server-side validation (never trust the client) ----------
if ($username === '' || $password === '') {
    backToLogin('Please fill in both your username and password.', $username);
}

if (strlen($password) < 6) {
    backToLogin('Password must be at least 6 characters.', $username);
}

// ---------- Look up the user + their role ----------
try {
    $stmt = $pdo->prepare(
        "SELECT u.user_id, u.username, u.password, u.full_name, u.email,
                u.status, r.role_name
         FROM users u
         JOIN roles r ON r.role_id = u.role_id
         WHERE u.username = :username
         LIMIT 1"
    );
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Login query failed: ' . $e->getMessage());
    backToLogin('Something went wrong. Please try again.', $username);
}

// ---------- Validate credentials ----------
// Plain-text password comparison (no hashing).
if (!$user || $password !== $user['password']) {
    backToLogin('Invalid username or password.', $username);
}

if ($user['status'] !== 'Active') {
    backToLogin('This account is inactive. Please contact an administrator.', $username);
}

// ---------- Success: start the session ----------
session_regenerate_id(true);

$_SESSION['user_id']   = $user['user_id'];
$_SESSION['username']  = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role_name'] = $user['role_name'];

logAction($pdo, $user['user_id'], 'LOGIN', 'Auth', $user['user_id'], $user['full_name'] . ' logged in');

// Optional "remember me" cookie (30 days) - stores only a token, never the password
if ($remember) {
    setcookie('remember_username', $username, time() + 60 * 60 * 24 * 30, '/', '', false, true);
}

// ---------- Role-based redirect ----------
switch ($user['role_name']) {
    case 'Admin':
        header('Location: admin_dashboard.php');
        break;
    case 'Cashier':
        header('Location: cashier_dashboard.php');
        break;
    case 'Manager':
    case 'Supplier':
    default:
        header('Location: staff_panel.php');
        break;
}
exit;
