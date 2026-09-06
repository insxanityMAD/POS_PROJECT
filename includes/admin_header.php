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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' - ' : '' ?>MR. DIY Admin</title>
<link rel="stylesheet" href="assets/css/admin.css?v=<?= time() ?>">
<script src="assets/js/toast.js?v=<?= time() ?>"></script>
</head>
<body>
<div class="app-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-label">MANAGEMENT</div>

        <a href="admin_dashboard.php" class="<?= navClass('admin_dashboard.php', $currentPage) ?>">📊 Dashboard</a>
        <a href="pos_sales.php" class="<?= navClass('pos_sales.php', $currentPage) ?>">🛒 POS / Sales</a>
        <a href="inventory.php" class="<?= navClass('inventory.php', $currentPage) ?>">
            📦 Inventory
            <?php if ($lowStockCount > 0): ?><span class="badge"><?= $lowStockCount ?></span><?php endif; ?>
        </a>
        <a href="manage_suppliers.php" class="<?= navClass('manage_suppliers.php', $currentPage) ?>">🚚 Suppliers</a>
        <a href="payroll.php" class="<?= navClass('payroll.php', $currentPage) ?>">💵 Payroll</a>
        <a href="expenses.php" class="<?= navClass('expenses.php', $currentPage) ?>">🧾 Expenses</a>
        <a href="reports.php" class="<?= navClass('reports.php', $currentPage) ?>">📈 Reports</a>
        <a href="settings.php" class="<?= navClass('settings.php', $currentPage) ?>">⚙️ Settings</a>
        <a href="manage_users.php" class="<?= navClass('manage_users.php', $currentPage) ?>">👥 Users</a>
        <a href="logs.php" class="<?= navClass('logs.php', $currentPage) ?>">🗂️ Logs</a>

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
                <div class="logo-icon">🛠️</div>
                <span class="logo-text">MR. DIY</span>
                <span class="console-pill">ADMIN CONSOLE</span>
            </div>
            <div class="topbar-user">
                <div class="avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="user-meta">
                    <div class="name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="role">Super admin</div>
                </div>
                <a href="logout.php" class="btn-logout">⇥ Logout</a>
            </div>
        </header>

        <div class="page-content">
