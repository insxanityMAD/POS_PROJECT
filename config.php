<?php
// ============================================================
// config.php
// Database connection for pos_computer_accounting
// ============================================================

declare(strict_types=1);

require_once __DIR__ . '/includes/base_url.php';

// Match PHP's clock to local time (Philippines) so time-ago calculations
// line up correctly with timestamps MySQL stores using the server's local time.
date_default_timezone_set('Asia/Manila');

// ---- Update these to match your MySQL/MariaDB setup ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'pos_computer_accounting');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Never leak real DB errors to the browser in production.
    error_log('DB connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}
