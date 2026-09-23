<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../includes/base_url.php';

// If already logged in, skip straight to the right panel
if (!empty($_SESSION['user_id'])) {
    switch ($_SESSION['role_name'] ?? '') {
        case 'Admin':
        case 'Manager':
            header('Location: ' . BASE_URL . '/admin_dashboard.php');
            break;
        case 'Cashier':
            header('Location: ' . BASE_URL . '/modules/pos/pos_sales.php');
            break;
        default:
            header('Location: staff_panel.php'); // same folder as this script
            break;
    }
    exit;
}

// Flash error/old input coming back from login_process.php
$error       = $_SESSION['login_error'] ?? '';
$oldUsername = $_SESSION['old_username'] ?? '';
unset($_SESSION['login_error'], $_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In - MR. DIY</title>
<style>
    :root{
        --mrdiy-yellow:#FFD400;
        --mrdiy-red:#E4002B;
        --mrdiy-cream:#FFF9E8;
        --text-dark:#1A1A1A;
        --text-gray:#6B6B6B;
        --border-gray:#E0E0E0;
    }
    *{ box-sizing:border-box; margin:0; padding:0; }
    body{
        font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
        min-height:100vh;
        display:flex;
        background:#fff;
    }
    .auth-wrapper{
        display:flex;
        width:100%;
        min-height:100vh;
    }

    /* ---------- LEFT / BRAND PANEL ---------- */
    .brand-panel{
        position:relative;
        flex:1.1;
        background:var(--mrdiy-yellow);
        overflow:hidden;
        display:flex;
        flex-direction:column;
        justify-content:space-between;
        padding:40px 50px;
    }
    .brand-panel::before{
        content:"";
        position:absolute;
        top:0; right:-10%;
        width:70%;
        height:140%;
        background:var(--mrdiy-red);
        transform:rotate(20deg);
        z-index:0;
    }
    .brand-panel::after{
        content:"";
        position:absolute;
        inset:0;
        background-image:radial-gradient(rgba(0,0,0,0.08) 1.5px, transparent 1.5px);
        background-size:26px 26px;
        z-index:0;
    }
    .brand-top, .brand-bottom{ position:relative; z-index:1; }
    .brand-top{
        display:flex;
        align-items:center;
        gap:12px;
        background:var(--mrdiy-cream);
        padding:10px 18px;
        border-radius:999px;
        width:fit-content;
        font-weight:700;
        letter-spacing:.5px;
        font-size:13px;
        color:var(--text-dark);
    }
    .brand-top .icon{ font-size:18px; }
    .brand-bottom .logo{
        font-size:64px;
        font-weight:900;
        line-height:0.95;
        color:var(--text-dark);
        letter-spacing:-1px;
    }
    .brand-bottom .underline{
        width:90px;
        height:6px;
        background:var(--mrdiy-red);
        margin:14px 0 18px;
    }
    .brand-bottom .tagline{
        font-size:16px;
        font-weight:600;
        color:var(--text-dark);
        max-width:320px;
        line-height:1.4;
    }

    /* ---------- RIGHT / FORM PANEL ---------- */
    .form-panel{
        flex:1;
        display:flex;
        flex-direction:column;
        justify-content:center;
        align-items:center;
        padding:40px;
        position:relative;
    }
    .help-link{
        position:absolute;
        top:28px; right:40px;
        font-size:13px;
        color:var(--text-gray);
        text-decoration:none;
    }
    .help-link span{ color:var(--mrdiy-red); font-weight:600; }

    .auth-card{
        width:100%;
        max-width:420px;
    }
    .badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        background:#FDECEC;
        color:var(--mrdiy-red);
        font-size:12px;
        font-weight:700;
        padding:5px 12px;
        border-radius:999px;
        margin-bottom:18px;
    }
    .badge::before{
        content:"";
        width:6px; height:6px;
        border-radius:50%;
        background:var(--mrdiy-red);
        display:inline-block;
    }
    .auth-card h1{
        font-size:30px;
        font-weight:800;
        color:var(--text-dark);
        margin-bottom:8px;
    }
    .auth-card p.subtitle{
        color:var(--text-gray);
        font-size:14.5px;
        margin-bottom:28px;
        line-height:1.5;
    }

    .alert{
        background:#FDECEC;
        border:1px solid #F5B5B5;
        color:#A3131A;
        padding:10px 14px;
        border-radius:8px;
        font-size:13.5px;
        margin-bottom:18px;
    }

    .field-group{ margin-bottom:18px; }
    .field-group label{
        display:block;
        font-size:13.5px;
        font-weight:600;
        color:var(--text-dark);
        margin-bottom:6px;
    }
    .input-wrap{
        position:relative;
        display:flex;
        align-items:center;
        border:1px solid var(--border-gray);
        border-radius:8px;
        padding:0 14px;
        transition:border-color .15s;
    }
    .input-wrap:focus-within{
        border-color:var(--mrdiy-red);
    }
    .input-wrap .icon{
        font-size:16px;
        color:var(--text-gray);
        margin-right:8px;
    }
    .input-wrap input{
        border:none;
        outline:none;
        flex:1;
        padding:12px 0;
        font-size:14.5px;
        color:var(--text-dark);
        background:transparent;
    }
    .toggle-pass{
        cursor:pointer;
        color:var(--text-gray);
        background:none;
        border:none;
        display:flex;
        align-items:center;
        padding:0;
    }
    .toggle-pass:hover{ color:var(--text-dark); }

    .field-error{
        color:#A3131A;
        font-size:12.5px;
        margin-top:5px;
        display:none;
    }

    .row-between{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:22px;
        font-size:13.5px;
    }
    .remember{ display:flex; align-items:center; gap:8px; color:var(--text-dark); }
    .row-between a{ color:var(--mrdiy-red); text-decoration:none; font-weight:600; }

    .btn-signin{
        width:100%;
        background:var(--mrdiy-red);
        color:#fff;
        border:none;
        padding:14px;
        border-radius:8px;
        font-size:16px;
        font-weight:700;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        transition:background .15s, opacity .15s;
    }
    .btn-signin:hover{ background:#c40025; }
    .btn-signin:disabled{ opacity:.7; cursor:not-allowed; }

    .signup-link{
        text-align:center;
        margin-top:22px;
        font-size:14px;
        color:var(--text-gray);
    }
    .signup-link a{ color:var(--mrdiy-red); font-weight:700; text-decoration:none; }

    .footer-links{
        text-align:center;
        margin-top:36px;
        font-size:12px;
        color:var(--text-gray);
    }
    .footer-links a{ color:var(--text-gray); text-decoration:underline; margin:0 4px; }

    @media (max-width:900px){
        .brand-panel{ display:none; }
        .form-panel{ padding:24px; }
    }

    /* ---------- MOTION ---------- */
    @keyframes loginFadeUp{ from{ opacity:0; transform:translateY(16px); } to{ opacity:1; transform:translateY(0); } }
    @keyframes loginFadeIn{ from{ opacity:0; } to{ opacity:1; } }
    @keyframes loginDrift{ from{ transform:rotate(20deg) scale(1); } to{ transform:rotate(24deg) scale(1.04); } }

    @media (prefers-reduced-motion: reduce){
        *, *::before, *::after{ animation-duration:.001ms !important; transition-duration:.001ms !important; }
    }

    .brand-panel::before{ animation:loginDrift 9s ease-in-out infinite alternate; }
    .brand-top{ animation:loginFadeUp .5s cubic-bezier(.2,.8,.2,1) both; }
    .brand-bottom{ animation:loginFadeUp .6s cubic-bezier(.2,.8,.2,1) .08s both; }
    .auth-card{ animation:loginFadeIn .5s ease .1s both; }
    .input-wrap{ transition:border-color .15s ease, box-shadow .15s ease; }
    .input-wrap:focus-within{ box-shadow:0 0 0 3px rgba(228,0,43,.1); }
    .btn-signin{ transition:background .15s ease, opacity .15s ease, transform .12s ease, box-shadow .15s ease; }
    .btn-signin:hover:not(:disabled){ transform:translateY(-1px); box-shadow:0 8px 20px rgba(228,0,43,.3); }
    .btn-signin:active:not(:disabled){ transform:scale(.98); }
    .toggle-pass{ transition:color .15s ease; }
</style>
</head>
<body>

<div class="auth-wrapper">

    <!-- LEFT BRAND PANEL -->
    <div class="brand-panel">
        <div class="brand-top"><span class="icon">🛠️</span> EVERYTHING YOU NEED</div>
        <div class="brand-bottom">
            <div class="logo">MR.<br>DIY</div>
            <div class="underline"></div>
            <p class="tagline">Always low prices. Always ready for your next project.</p>
        </div>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="form-panel">
        <a href="#" class="help-link">Need help? <span>Contact support</span></a>

        <div class="auth-card">
            <div class="badge">STAFF LOGIN</div>
            <h1>Welcome back</h1>
            <p class="subtitle">Sign in with your staff account to access the POS system.</p>

            <?php if ($error): ?>
                <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form id="loginForm" action="login_process.php" method="POST" novalidate>

                <div class="field-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <span class="icon">👤</span>
                        <input type="text" id="username" name="username" placeholder="Enter your username"
                               value="<?= htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="username">
                    </div>
                    <div class="field-error" id="usernameError">Please enter your username.</div>
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
                            <svg id="eyeOpen" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eyeClosed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="field-error" id="passwordError">Password must be at least 6 characters.</div>
                </div>

                <div class="row-between">
                    <label class="remember">
                        <input type="checkbox" name="remember" id="remember"> Remember me
                    </label>
                </div>

                <button type="submit" class="btn-signin" id="submitBtn">Sign in <span>→</span></button>
            </form>

            <div class="signup-link">Forgot your password, or need an account? Contact your administrator.</div>

            <div class="footer-links">
                Secure sign in
            </div>
        </div>
    </div>

</div>

<script src="<?= BASE_URL ?>/assets/js/login.js"></script>
</body>
</html>
