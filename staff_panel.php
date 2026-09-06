<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Panel - MR. DIY</title>
<style>
    body{ font-family:Arial,sans-serif; background:#FFF9E8; margin:0; padding:40px; }
    h1{ color:#1A1A1A; }
    .card{ background:#fff; border-radius:10px; padding:24px; max-width:480px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    a.logout{ color:#E4002B; font-weight:700; text-decoration:none; }
</style>
</head>
<body>
    <div class="card">
        <h1><?= htmlspecialchars($_SESSION['role_name'], ENT_QUOTES, 'UTF-8') ?> Panel</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        <p>This is the non-admin landing page (Manager / Cashier / Supplier). Build the POS screen, restock forms, etc. here.</p>
        <p><a class="logout" href="logout.php">Log out</a></p>
    </div>
</body>
</html>
