<?php
declare(strict_types=1);
$pageTitle = 'Users';
require_once __DIR__ . '/includes/admin_header.php';

try {
    $users = $pdo->query(
        "SELECT u.user_id, u.username, u.full_name, u.email, u.status, r.role_name
         FROM users u JOIN roles r ON r.role_id = u.role_id
         ORDER BY u.user_id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('User list query failed: ' . $e->getMessage());
    $users = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Users</h1>
        <p>Manage who can access the system and what they can do.</p>
    </div>
</div>

<div class="panel">
    <?php if (empty($users)): ?>
        <p style="color:var(--text-gray); font-size:13.5px;">No user accounts found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Username</th><th>Full name</th><th>Email</th><th>Role</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="pill <?= $u['status'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
