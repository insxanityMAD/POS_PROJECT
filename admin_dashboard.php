<?php
declare(strict_types=1);
$pageTitle = 'Dashboard';
$allowedRoles = ['Admin', 'Manager'];
require_once __DIR__ . '/includes/admin_header.php';

// ---------- Live stats ----------
try {
    $todaySales = (float)$pdo->query(
        "SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_status='Completed' AND DATE(sale_date) = CURDATE()"
    )->fetchColumn();

    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    $productsAddedThisMonth = (int)$pdo->query(
        "SELECT COUNT(*) FROM products WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"
    )->fetchColumn();

    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status='Active'")->fetchColumn();

    $lowStock = (int)$pdo->query(
        "SELECT COUNT(*) FROM products WHERE stock_quantity <= reorder_level AND status='Active'"
    )->fetchColumn();

    $recentActivity = $pdo->query(
        "SELECT l.action, l.module, l.description, l.log_date, u.full_name
         FROM system_logs l
         LEFT JOIN users u ON u.user_id = l.user_id
         ORDER BY l.log_date DESC
         LIMIT 8"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Dashboard query failed: ' . $e->getMessage());
    $todaySales = 0; $totalProducts = 0; $productsAddedThisMonth = 0;
    $totalUsers = 0; $activeUsers = 0; $lowStock = 0;
    $recentActivity = [];
}

$activityIcons = [
    'CREATE'     => ['🆕', 'green'],
    'UPDATE'     => ['✏️', 'yellow'],
    'DEACTIVATE' => ['🚫', 'red'],
    'REACTIVATE' => ['✅', 'green'],
    'SALE'       => ['🧾', 'green'],
    'RESTOCK'    => ['📦', 'yellow'],
    'VOID'       => ['⚠️', 'red'],
    'LOGIN'      => ['🔑', 'green'],
    'LOGOUT'     => ['🚪', 'yellow'],
    'LINK'       => ['🔗', 'green'],
    'UNLINK'     => ['✂️', 'red'],
];

function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 0) return 'just now';
    if ($diff < 60)   return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' d ago'; // up to 7 days
    return date('M j, Y', strtotime($datetime)); // older than a week: show the actual date
}
$currentHour = (int)date('G');
if ($currentHour < 12) {
    $greeting = 'Good morning';
} elseif ($currentHour < 18) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

$isAdmin = ($_SESSION['role_name'] ?? '') === 'Admin';

$quickActions = $isAdmin
    ? [
        ['manage_users.php', '👥', 'Users', 'Manage access', true],
        ['manage_products.php', '📦', 'Products', 'Update catalog', false],
        ['manage_suppliers.php', '🚚', 'Suppliers', 'Vendor directory', false],
        ['manage_tax.php', '%', 'Tax', 'Configure rates', false],
        ['manage_discounts.php', '🏷️', 'Discounts', 'Create promotion', false],
        ['manage_qr_payments.php', '📱', 'QR payments', 'Payment settings', false],
    ]
    : [
        ['pos_sales.php', '🛒', 'POS', 'Start a sale', true],
        ['inventory.php', '📦', 'Inventory', 'View & restock', false],
        ['manage_suppliers.php', '🚚', 'Suppliers', 'Vendor directory', false],
        ['payroll.php', '💵', 'Payroll', 'Process pay', false],
        ['reports.php', '📈', 'Reports', 'View reports', false],
    ];
?>
<div class="page-eyebrow">OPERATIONS OVERVIEW</div>
<div class="page-header">
    <div>
        <h1><?= $greeting ?>, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0], ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Here's what's happening across your store today.</p>
    </div>
    <div class="date-chip">📅 <?= date('F j, Y') ?></div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="label">Today's sales</span>
            <div class="stat-icon">₱</div>
        </div>
        <div class="stat-value">₱<?= number_format($todaySales, 2) ?></div>
        <div class="stat-sub">Total completed sales today</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-top">
            <span class="label">Total products</span>
            <div class="stat-icon blue">📦</div>
        </div>
        <div class="stat-value"><?= number_format($totalProducts) ?></div>
        <div class="stat-sub up"><?= $productsAddedThisMonth ?> added this month</div>
    </div>

    <?php if ($isAdmin): ?>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="label">User accounts</span>
            <div class="stat-icon purple">👥</div>
        </div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-sub"><?= $activeUsers ?> currently active</div>
    </div>
    <?php endif; ?>

    <div class="stat-card">
        <div class="stat-card-top">
            <span class="label">Low stock</span>
            <div class="stat-icon red">⚠️</div>
        </div>
        <div class="stat-value"><?= $lowStock ?></div>
        <div class="stat-sub warn"><?= $lowStock > 0 ? 'Requires attention' : 'All good' ?></div>
    </div>
</div>

<div class="content-grid">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Quick actions</h2>
                <p>Jump into frequent admin tasks.</p>
            </div>
        </div>
        <div class="quick-grid">
            <?php foreach ($quickActions as [$href, $icon, $title, $sub, $highlight]): ?>
            <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="quick-action<?= $highlight ? ' highlight' : '' ?>">
                <div class="quick-action-top"><div class="quick-action-icon"><?= $icon ?></div>↗</div>
                <div><div class="qa-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div><div class="qa-sub"><?= htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') ?></div></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>System activity</h2>
                <p>Latest updates from your team.</p>
            </div>
            <?php if ($isAdmin): ?><a href="logs.php" class="view-all">View all logs</a><?php endif; ?>
        </div>

        <?php if (empty($recentActivity) && $lowStock === 0): ?>
            <p style="color:var(--text-gray); font-size:13.5px;">No activity yet.</p>
        <?php else: ?>
            <?php foreach ($recentActivity as $log): [$icon, $color] = $activityIcons[$log['action']] ?? ['•', 'yellow']; ?>
                <div class="activity-item">
                    <div class="activity-icon <?= $color ?>"><?= $icon ?></div>
                    <div class="activity-body">
                        <div class="title"><?= htmlspecialchars($log['full_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string)($log['description'] ?? $log['action']), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="sub"><?= htmlspecialchars((string)$log['module'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="activity-time"><?= timeAgo($log['log_date']) ?></div>
                </div>
            <?php endforeach; ?>

            <?php if ($lowStock > 0): ?>
                <div class="activity-item">
                    <div class="activity-icon red">⚠️</div>
                    <div class="activity-body">
                        <div class="title">Stock alert triggered</div>
                        <div class="sub"><?= $lowStock ?> product(s) at or below reorder level</div>
                    </div>
                    <div class="activity-time">now</div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
