<?php
declare(strict_types=1);
require_once __DIR__ . '/admin_guard.php';

$currentPage = basename($_SERVER['PHP_SELF']);

function navClass(string $file, string $current): string
{
    return $file === $current ? 'nav-item active' : 'nav-item';
}

// Low stock count for the sidebar badge
$lowStockCount = 0;
try {
    $lowStockCount = (int)$pdo->query(
        "SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status = 'Active'"
    )->fetchColumn();
} catch (PDOException $e) {
    error_log('Low stock count failed: ' . $e->getMessage());
}

$fullName = $_SESSION['full_name'] ?? 'Admin';
$initials = '';
foreach (explode(' ', trim($fullName)) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
$initials = mb_substr($initials, 0, 2) ?: 'AD';

$role = $_SESSION['role_name'] ?? 'Admin';
$roleLabels = ['Admin' => 'Administrator', 'Manager' => 'Manager', 'Cashier' => 'Cashier', 'Supplier' => 'Supplier'];
$roleLabel  = $roleLabels[$role] ?? $role;
$consolePill = $role === 'Admin' ? 'ADMIN CONSOLE' : strtoupper($role) . ' CONSOLE';

$canManage   = in_array($role, ['Admin', 'Manager'], true); // inventory, suppliers, payroll, expenses, reports
$canSell     = in_array($role, ['Admin', 'Manager', 'Cashier'], true); // POS
$isAdminOnly = $role === 'Admin'; // settings, users, logs
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' - ' : '' ?>MR. DIY Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= time() ?>">
<script src="<?= BASE_URL ?>/assets/js/toast.js?v=<?= time() ?>"></script>
<script>
(function () {
    // Every POST made with fetch() carries the CSRF token automatically,
    // so individual pages don't need to remember to attach it themselves.
    var token = document.querySelector('meta[name="csrf-token"]').content;
    var originalFetch = window.fetch;
    window.fetch = function (input, init) {
        init = init || {};
        var method = (init.method || 'GET').toUpperCase();
        if (method === 'POST') {
            if (init.headers instanceof Headers) {
                init.headers.set('X-CSRF-Token', token);
            } else {
                init.headers = Object.assign({}, init.headers, { 'X-CSRF-Token': token });
            }
        }
        return originalFetch(input, init);
    };
})();
</script>
</head>
<body>
<div class="app-shell">

    <!-- SIDEBAR -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-label">MANAGEMENT</div>

        <?php if ($canManage): ?>
        <a href="<?= BASE_URL ?>/admin_dashboard.php" class="<?= navClass('admin_dashboard.php', $currentPage) ?>">📊 Dashboard</a>
        <?php endif; ?>
        <?php if ($canSell): ?>
        <a href="<?= BASE_URL ?>/modules/pos/pos_sales.php" class="<?= navClass('pos_sales.php', $currentPage) ?>">🛒 POS / Sales</a>
        <?php endif; ?>
        <?php if ($canManage): ?>
        <a href="<?= BASE_URL ?>/modules/inventory/inventory.php" class="<?= navClass('inventory.php', $currentPage) ?>">
            📦 Inventory
            <?php if ($lowStockCount > 0): ?><span class="badge"><?= $lowStockCount ?></span><?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/modules/suppliers/manage_suppliers.php" class="<?= navClass('manage_suppliers.php', $currentPage) ?>">🚚 Suppliers</a>
        <a href="<?= BASE_URL ?>/modules/payroll/payroll.php" class="<?= navClass('payroll.php', $currentPage) ?>">💵 Payroll</a>
        <a href="<?= BASE_URL ?>/modules/expenses/expenses.php" class="<?= navClass('expenses.php', $currentPage) ?>">🧾 Expenses</a>
        <a href="<?= BASE_URL ?>/modules/reports/reports.php" class="<?= navClass('reports.php', $currentPage) ?>">📈 Reports</a>
        <?php endif; ?>
        <?php if ($isAdminOnly): ?>
        <a href="<?= BASE_URL ?>/modules/settings/settings.php" class="<?= navClass('settings.php', $currentPage) ?>">⚙️ Settings</a>
        <a href="<?= BASE_URL ?>/modules/users/manage_users.php" class="<?= navClass('manage_users.php', $currentPage) ?>">👥 Users</a>
        <a href="<?= BASE_URL ?>/modules/logs/logs.php" class="<?= navClass('logs.php', $currentPage) ?>">🗂️ Logs</a>
        <?php endif; ?>

        <div class="sidebar-help">
            <h4>Need help?</h4>
            <p>Contact support for account and system assistance.</p>
            <a href="#">CONTACT SUPPORT →</a>
        </div>
    </aside>

    <!-- MAIN COLUMN -->
    <div class="main-col">
        <header class="topbar">
            <div class="topbar-brand">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">☰</button>
                <div class="logo-icon">🛠️</div>
                <span class="logo-text">MR. DIY</span>
                <span class="console-pill"><?= htmlspecialchars($consolePill, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="topbar-user">
                <div class="avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="user-meta">
                    <div class="name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <a href="<?= BASE_URL ?>/modules/auth/logout.php" class="btn-logout">⇥ Logout</a>
            </div>
        </header>

        <div class="page-content">
