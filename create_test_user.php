<?php
// ============================================================
// create_test_user.php
// Run this ONCE from the browser (or CLI) to insert one test
// account per role (Admin, Manager, Cashier) with a properly
// hashed password, then DELETE this file. Note: as of the
// current login_process.php, plain-text passwords in the
// original SQL dump are auto-upgraded to a hash on first
// successful login anyway - this script just gives you a known
// set of credentials to test each role's dashboard with.
// ============================================================

declare(strict_types=1);
require_once 'config.php';

// role_id per the seed data in pos_computer_accounting.sql: 1=Admin, 2=Manager, 3=Cashier
$testAccounts = [
    ['role_id' => 1, 'username' => 'admin_test',   'password' => 'Admin123!',   'full_name' => 'Test Admin'],
    ['role_id' => 2, 'username' => 'manager_test',  'password' => 'Manager123!', 'full_name' => 'Test Manager'],
    ['role_id' => 3, 'username' => 'cashier_test',  'password' => 'Cashier123!', 'full_name' => 'Test Cashier'],
];

$stmt = $pdo->prepare(
    "INSERT INTO users (role_id, username, password, full_name, email, status)
     VALUES (:role_id, :username, :password, :full_name, :email, 'Active')
     ON DUPLICATE KEY UPDATE password = VALUES(password), status = 'Active'"
);

foreach ($testAccounts as $acct) {
    try {
        $stmt->execute([
            'role_id'   => $acct['role_id'],
            'username'  => $acct['username'],
            'password'  => password_hash($acct['password'], PASSWORD_DEFAULT),
            'full_name' => $acct['full_name'],
            'email'     => $acct['username'] . '@mrdiy.test',
        ]);
        echo "Created/updated: {$acct['username']} / {$acct['password']}<br>";
    } catch (PDOException $e) {
        echo 'Error for ' . htmlspecialchars($acct['username']) . ': ' . htmlspecialchars($e->getMessage()) . '<br>';
    }
}

echo '<br>Delete this file now.';
