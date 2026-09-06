<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role_name'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}
