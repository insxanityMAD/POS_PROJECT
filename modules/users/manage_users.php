<?php
declare(strict_types=1);
$pageTitle = 'Users';
require_once __DIR__ . '/../../includes/admin_header.php';

try {
    $users = $pdo->query(
        "SELECT u.user_id, u.role_id, u.username, u.full_name, u.email, u.status, r.role_name
         FROM users u JOIN roles r ON r.role_id = u.role_id
         ORDER BY u.user_id DESC"
    )->fetchAll();

    $roles = $pdo->query("SELECT role_id, role_name FROM roles ORDER BY role_name")->fetchAll();
} catch (PDOException $e) {
    error_log('Users page query failed: ' . $e->getMessage());
    $users = []; $roles = [];
}
?>
<div class="page-eyebrow">MANAGEMENT</div>
<div class="page-header">
    <div>
        <h1>Users</h1>
        <p>Manage who can access the system and what they can do.</p>
    </div>
</div>

<div class="section-actions">
    <input type="text" id="userTableSearch" placeholder="🔍 Search by name, username, or role..." style="max-width:320px; border:1px solid var(--border-gray); border-radius:8px; padding:9px 12px; font-size:13.5px;">
    <button class="btn btn-primary" id="openAddUserModal">+ Add User</button>
</div>

<div class="panel">
    <?php if (empty($users)): ?>
        <p class="empty-note">No user accounts found.</p>
    <?php else: ?>
    <p class="empty-note" id="noUserResults" style="display:none;">No users match your search.</p>
    <table class="data-table" id="usersTable">
        <thead>
            <tr><th>Username</th><th>Full name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr data-search="<?= htmlspecialchars(mb_strtolower($u['username'] . ' ' . $u['full_name'] . ' ' . $u['role_name']), ENT_QUOTES, 'UTF-8') ?>">
                <td><?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['role_name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="pill <?= $u['status'] === 'Active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn-outline btn-sm js-edit-user"
                            data-id="<?= $u['user_id'] ?>"
                            data-role="<?= $u['role_id'] ?>"
                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                            data-fullname="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars((string)($u['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >Edit</button>
                        <?php if ($u['user_id'] != $_SESSION['user_id']): ?>
                            <?php if ($u['status'] === 'Active'): ?>
                                <button class="btn-danger-text js-toggle-user" data-id="<?= $u['user_id'] ?>" data-status="Inactive">Deactivate</button>
                            <?php else: ?>
                                <button class="btn-danger-text js-toggle-user" data-id="<?= $u['user_id'] ?>" data-status="Active">Reactivate</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:var(--text-gray); font-size:12px;">(you)</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- ================= ADD/EDIT USER MODAL ================= -->
<div class="modal-overlay" id="userModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="userModalTitle">Add User</h2>
            <button class="modal-close js-close-modal" data-modal="userModal">&times;</button>
        </div>
        <div class="form-msg" id="userFormMsg"></div>
        <form id="userForm">
            <input type="hidden" id="u_action" value="add_user">
            <input type="hidden" id="u_user_id">

            <div class="form-row">
                <label>Role</label>
                <select id="u_role_id" required>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['role_id'] ?>"><?= htmlspecialchars($r['role_name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid-2">
                <div class="form-row"><label>Username</label><input type="text" id="u_username" required></div>
                <div class="form-row"><label>Full Name</label><input type="text" id="u_full_name" required></div>
            </div>

            <div class="form-row"><label>Email (optional)</label><input type="email" id="u_email"></div>

            <div class="form-row">
                <label id="u_password_label">Password</label>
                <input type="password" id="u_password" placeholder="At least 6 characters">
                <div class="field-error" id="u_password_hint" style="display:none; margin-top:5px;">Leave blank to keep the current password.</div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-outline js-close-modal" data-modal="userModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.js-close-modal').forEach(btn => btn.addEventListener('click', () => closeModal(btn.dataset.modal)));
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});

// ---------- Search ----------
document.getElementById('userTableSearch')?.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const match = (row.dataset.search || '').includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    const noResults = document.getElementById('noUserResults');
    if (noResults) noResults.style.display = visible === 0 && q !== '' ? 'block' : 'none';
});

// ---------- Add User ----------
document.getElementById('openAddUserModal').addEventListener('click', () => {
    document.getElementById('userForm').reset();
    document.getElementById('u_action').value = 'add_user';
    document.getElementById('u_user_id').value = '';
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('u_password').required = true;
    document.getElementById('u_password_label').textContent = 'Password';
    document.getElementById('u_password_hint').style.display = 'none';
    document.getElementById('userFormMsg').style.display = 'none';
    openModal('userModal');
});

// ---------- Edit User ----------
document.querySelectorAll('.js-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('userForm').reset();
        document.getElementById('u_action').value = 'edit_user';
        document.getElementById('u_user_id').value = btn.dataset.id;
        document.getElementById('u_role_id').value = btn.dataset.role;
        document.getElementById('u_username').value = btn.dataset.username;
        document.getElementById('u_full_name').value = btn.dataset.fullname;
        document.getElementById('u_email').value = btn.dataset.email;
        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('u_password').required = false;
        document.getElementById('u_password_label').textContent = 'New Password (optional)';
        document.getElementById('u_password_hint').style.display = 'block';
        document.getElementById('userFormMsg').style.display = 'none';
        openModal('userModal');
    });
});

// ---------- Save User (add or edit) ----------
document.getElementById('userForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = document.getElementById('userFormMsg');
    const fd = new FormData();
    fd.append('action', document.getElementById('u_action').value);
    fd.append('user_id', document.getElementById('u_user_id').value);
    fd.append('role_id', document.getElementById('u_role_id').value);
    fd.append('username', document.getElementById('u_username').value);
    fd.append('full_name', document.getElementById('u_full_name').value);
    fd.append('email', document.getElementById('u_email').value);
    fd.append('password', document.getElementById('u_password').value);

    fetch('user_actions.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) { location.reload(); }
            else { msg.textContent = data.message; msg.className = 'form-msg error'; showToast(data.message); }
        });
});

// ---------- Deactivate/Reactivate User ----------
document.querySelectorAll('.js-toggle-user').forEach(btn => {
    btn.addEventListener('click', () => {
        const label = btn.dataset.status === 'Inactive' ? 'deactivate' : 'reactivate';
        if (!confirm('Are you sure you want to ' + label + ' this user account?')) return;
        const fd = new FormData();
        fd.append('action', 'toggle_user_status');
        fd.append('user_id', btn.dataset.id);
        fd.append('new_status', btn.dataset.status);
        fetch('user_actions.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else alert(data.message); });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
