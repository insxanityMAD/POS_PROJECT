<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$fullName = $_SESSION['full_name'] ?? 'there';
$role     = $_SESSION['role_name'] ?? 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?> Panel - MR. DIY</title>
<style>
    :root{ --mrdiy-yellow:#FFD400; --mrdiy-red:#E4002B; --text-gray:#7A7A7A; }
    *{ box-sizing:border-box; }
    body{
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
        background:#F5F3EE; margin:0; min-height:100vh;
        display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .card{
        background:#fff; border-radius:14px; padding:32px; max-width:440px; width:100%;
        box-shadow:0 4px 24px rgba(0,0,0,.08); text-align:center;
    }
    .logo-icon{
        width:56px; height:56px; border-radius:50%; background:var(--mrdiy-yellow);
        display:flex; align-items:center; justify-content:center; font-size:26px; margin:0 auto 16px;
    }
    .badge{
        display:inline-block; background:#FFF4CC; color:#8a6d00; font-size:11px; font-weight:700;
        letter-spacing:.06em; padding:4px 10px; border-radius:20px; margin-bottom:12px;
    }
    h1{ color:#1A1A1A; font-size:22px; margin:0 0 8px; }
    p{ color:var(--text-gray); font-size:14px; line-height:1.5; margin:0 0 20px; }
    p strong{ color:#1A1A1A; }
    a.logout{
        display:inline-block; color:#fff; background:var(--mrdiy-red); font-weight:700;
        text-decoration:none; padding:10px 22px; border-radius:8px; font-size:14px;
    }
    a.logout:hover{ opacity:.9; }
</style>
</head>
<body>
    <div class="card">
        <div class="logo-icon">🛠️</div>
        <div class="badge"><?= htmlspecialchars(strtoupper($role), ENT_QUOTES, 'UTF-8') ?> ACCOUNT</div>
        <h1>Welcome, <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>Your account doesn't have a dashboard in this system yet. If you believe this is a mistake, contact an administrator to review your account's role.</p>
        <a class="logout" href="logout.php">Log out</a>
    </div>
</body>
</html>
