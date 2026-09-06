<?php
declare(strict_types=1);
$pageTitle = 'Logs';
require_once __DIR__ . '/includes/admin_header.php';

try {
    $logs = $pdo->query(
        "SELECT l.*, u.full_name
         FROM system_logs l
         LEFT JOIN users u ON u.user_id = l.user_id
         ORDER BY l.log_date DESC
         LIMIT 300"
    )->fetchAll();

    $modules = $pdo->query("SELECT DISTINCT module FROM system_logs WHERE module IS NOT NULL ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('Logs page query failed: ' . $e->getMessage());
    $logs = []; $modules = [];
}

$actionColors = [
    'CREATE'     => 'active',
    'UPDATE'     => 'active',
    'LOGIN'      => 'active',
    'SALE'       => 'active',
    'RESTOCK'    => 'active',
    'LINK'       => 'active',
    'DEACTIVATE' => 'inactive',
    'UNLINK'     => 'inactive',
    'LOGOUT'     => 'inactive',
];

function timeAgoLog(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 0) return 'just now'; // clock/timezone drift safety net
    if ($diff < 60)    return $diff . 's ago';
    if ($diff < 3600)  return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' d ago'; // up to 7 days
    return date('M j, Y g:i A', strtotime($datetime)); // older than a week: show the actual date + time
}
?>
<div class="page-eyebrow">SYSTEM</div>
<div class="page-header">
    <div>
        <h1>Logs</h1>
        <p>Full system activity history — who did what, and when.</p>
    </div>
</div>

<div class="section-actions">
    <input type="text" id="logTableSearch" placeholder="🔍 Search by user, action, or description..." style="max-width:340px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
    <select id="moduleFilter" style="border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
        <option value="">All modules</option>
        <?php foreach ($modules as $m): ?>
            <option value="<?= htmlspecialchars(mb_strtolower($m), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="panel">
    <?php if (empty($logs)): ?>
        <p class="empty-note">No activity has been logged yet.</p>
    <?php else: ?>
    <p class="empty-note" id="noLogResults" style="display:none;">No log entries match your search.</p>
    <div style="overflow-x:auto;">
    <table class="data-table" id="logsTable">
        <thead>
            <tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Description</th></tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr data-search="<?= htmlspecialchars(mb_strtolower(($log['full_name'] ?? 'system') . ' ' . $log['action'] . ' ' . $log['description']), ENT_QUOTES, 'UTF-8') ?>"
                data-module="<?= htmlspecialchars(mb_strtolower((string)$log['module']), ENT_QUOTES, 'UTF-8') ?>">
                <td title="<?= htmlspecialchars($log['log_date'], ENT_QUOTES, 'UTF-8') ?>"><?= timeAgoLog($log['log_date']) ?></td>
                <td><?= htmlspecialchars($log['full_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="pill <?= $actionColors[$log['action']] ?? 'inactive' ?>"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars((string)($log['module'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($log['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p style="color:var(--text-gray); font-size:12.5px; margin-top:14px;">Showing the most recent 300 entries.</p>
    <?php endif; ?>
</div>

<script>
function applyLogFilters() {
    const q = document.getElementById('logTableSearch').value.trim().toLowerCase();
    const mod = document.getElementById('moduleFilter').value;
    const rows = document.querySelectorAll('#logsTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const matchesSearch = (row.dataset.search || '').includes(q);
        const matchesModule = !mod || row.dataset.module === mod;
        const show = matchesSearch && matchesModule;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const noResults = document.getElementById('noLogResults');
    if (noResults) noResults.style.display = visible === 0 ? 'block' : 'none';
}
document.getElementById('logTableSearch')?.addEventListener('input', applyLogFilters);
document.getElementById('moduleFilter')?.addEventListener('change', applyLogFilters);
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
