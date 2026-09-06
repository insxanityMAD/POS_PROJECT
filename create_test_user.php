<?php
// ============================================================
// create_test_user.php
// Run this ONCE from the browser (or CLI) to insert a test
// Admin account with a properly hashed password, then DELETE
// this file. Your original SQL dump has no password hashing
// logic, so accounts inserted via plain INSERT statements will
// never pass password_verify() in login_process.php.
// ============================================================

declare(strict_types=1);
require_once 'config.php';

$username = 'admin';
$plainPassword = 'Admin123!';   // change this
$hashed = password_hash($plainPassword, PASSWORD_DEFAULT);

try {
    // Admin role_id is 1 per the seed data in pos_computer_accounting.sql
    $stmt = $pdo->prepare(
        "INSERT INTO users (role_id, username, password, full_name, email, status)
         VALUES (1, :username, :password, 'Test Admin', 'admin@mrdiy.test', 'Active')"
    );
    $stmt->execute([
        'username' => $username,
        'password' => $hashed,
    ]);
    echo "Test admin created. Username: {$username} / Password: {$plainPassword}<br>";
    echo "Delete this file now.";
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
