<?php
declare(strict_types=1);
session_start();

if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/../../includes/log_helper.php';
    logAction($pdo, $_SESSION['user_id'], 'LOGOUT', 'Auth', $_SESSION['user_id'], ($_SESSION['full_name'] ?? 'User') . ' logged out');
}

$_SESSION = [];
session_destroy();
setcookie('remember_username', '', time() - 3600, '/');
header('Location: login.php');
exit;
