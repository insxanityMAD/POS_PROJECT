<?php
// login.php
// Serves the MR. DIY staff sign-in page. Logic lives in js/login.js,
// which calls auth.php to check credentials against the pos_system database.
session_start();

// Generate a CSRF token for this session if one doesn't exist yet.
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>MR. DIY — Sign In</title>
<style>
  :root {
    --yellow: #FFCC00;
    --red: #CC0000;
    --ink: #1A1A1A;
    --warm-white: #FFFBF2;
    --steel: #6B6B6B;
    --border: #E3DED0;
    --green: #1E7E34;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    font-family: 'Helvetica Neue', Arial, -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--ink);
    background: var(--warm-white);
  }

  .split {
    display: flex;
    min-height: 100vh;
    width: 100%;
  }

  /* ---------- Brand panel ---------- */
  .brand-panel {
    position: relative;
    flex: 0 0 42%;
    background: var(--yellow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 48px 56px;
    min-height: 320px;
  }

  .pegboard {
    position: absolute;
    inset: 0;
    background-image: radial-gradient(rgba(26,26,26,0.13) 1.5px, transparent 1.5px);
    background-size: 22px 22px;
    opacity: 0.5;
  }

  .stripe {
    position: absolute;
    top: -20%;
    right: -15%;
    width: 70%;
    height: 160%;
    background: var(--red);
    transform: rotate(18deg);
  }

  .badge {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
    background: var(--ink);
    color: var(--yellow);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 8px 14px;
    border-radius: 999px;
  }

  .badge .icon {
    font-size: 14px;
  }

  .brand-content {
    position: relative;
    z-index: 1;
  }

  .brand-title {
    font-family: 'Arial Black', Arial, sans-serif;
    font-weight: 900;
    font-size: 64px;
    line-height: 0.95;
    letter-spacing: -1px;
    color: var(--ink);
    margin: 0;
  }

  .brand-underline {
    width: 64px;
    height: 6px;
    background: var(--red);
    margin: 18px 0 22px;
    border-radius: 3px;
  }

  .brand-footer {
    position: relative;
    z-index: 1;
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    max-width: 260px;
    line-height: 1.4;
  }

  /* ---------- Form panel ---------- */
  .form-panel {
    flex: 1 1 58%;
    display: flex;
    flex-direction: column;
    background: var(--warm-white);
  }

  .top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 28px 48px 0;
    font-size: 13px;
  }

  .top-bar a {
    color: var(--red);
    font-weight: 700;
    text-decoration: none;
  }

  .top-bar .need-help {
    color: var(--steel);
  }

  .form-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 24px 60px;
  }

  .card {
    width: 100%;
    max-width: 380px;
    background: #FFFFFF;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 36px 32px;
    box-shadow: 0 1px 2px rgba(26,26,26,0.04);
  }

  .member-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F0EEE6;
    color: var(--ink);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 5px 10px;
    border-radius: 999px;
    margin-bottom: 16px;
  }

  .member-badge::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--red);
    display: inline-block;
  }

  .role-group {
    display: flex;
    gap: 8px;
    margin-bottom: 22px;
    background: #F0EEE6;
    padding: 4px;
    border-radius: 8px;
  }

  .role-btn {
    flex: 1;
    padding: 10px 8px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--steel);
    cursor: pointer;
    font-family: inherit;
  }

  .role-btn.active {
    background: var(--ink);
    color: var(--yellow);
  }

  h1 {
    font-size: 26px;
    font-weight: 800;
    margin: 0 0 8px 0;
  }

  .subheading {
    font-size: 14px;
    line-height: 1.5;
    color: var(--steel);
    margin: 0 0 26px 0;
  }

  .field {
    margin-bottom: 18px;
  }

  label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
  }

  .input-wrap {
    position: relative;
    display: flex;
    align-items: center;
  }

  .input-wrap .icon {
    position: absolute;
    left: 12px;
    font-size: 15px;
    color: var(--steel);
    pointer-events: none;
  }

  input {
    width: 100%;
    padding: 12px 14px 12px 36px;
    font-size: 15px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: #FFFFFF;
    color: var(--ink);
    font-family: inherit;
  }

  input.error {
    border-color: var(--red);
  }

  .toggle-visibility {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 15px;
    color: var(--steel);
    padding: 4px;
  }

  .error-text {
    margin-top: 6px;
    font-size: 13px;
    color: var(--red);
    font-weight: 600;
    display: none;
  }

  .error-text.show { display: block; }

  .form-error-box {
    background: #FCEBEB;
    border: 1px solid var(--red);
    color: var(--red);
    font-size: 13px;
    font-weight: 600;
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 18px;
    display: none;
  }

  .form-error-box.show { display: block; }

  .form-error-box.success {
    background: #E9F7EF;
    border: 1px solid var(--green);
    color: var(--green);
  }

  .submit {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-weight: 800;
    color: var(--warm-white);
    background: var(--red);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }

  .legal-footer {
    text-align: center;
    font-size: 12px;
    color: var(--steel);
    padding-bottom: 28px;
  }

  .legal-footer a {
    color: var(--steel);
    text-decoration: none;
  }

  .legal-footer a:hover {
    text-decoration: underline;
  }

  input:focus-visible,
  .submit:focus-visible {
    outline: 3px solid var(--ink);
    outline-offset: 2px;
  }

  @media (max-width: 760px) {
    .split { flex-direction: column; }
    .brand-panel { min-height: 220px; padding: 28px 24px 32px; }
    .brand-title { font-size: 42px; }
    .top-bar { padding: 20px 24px 0; }
    .form-center { padding: 24px 16px 40px; }
  }
</style>
</head>
<body>

  <div class="split">
    <!-- Brand panel -->
    <div class="brand-panel">
      <div class="pegboard" aria-hidden="true"></div>
      <div class="stripe" aria-hidden="true"></div>

      <div class="badge">
        <span class="icon">🔒</span> Staff Portal — Internal Use Only
      </div>

      <div class="brand-content">
        <div class="brand-title">MR.<br />DIY</div>
        <div class="brand-underline"></div>
      </div>

      <div class="brand-footer">
        For employees only. Unauthorized access is prohibited.
      </div>
    </div>

    <!-- Form panel -->
    <div class="form-panel">
      <div class="top-bar">
        <span class="need-help">Need help?</span>
        <a href="#">Contact IT support</a>
      </div>

      <div class="form-center">
        <div class="card">
          <div class="member-badge">Employee sign in</div>
          <h1>Welcome back</h1>
          <p class="subheading">Select your role and sign in to access the POS system.</p>

          <div class="role-group" role="radiogroup" aria-label="Select role" id="roleGroup">
            <button type="button" class="role-btn active" role="radio" aria-checked="true" data-role="Admin">Admin</button>
            <button type="button" class="role-btn" role="radio" aria-checked="false" data-role="Manager">Manager</button>
            <button type="button" class="role-btn" role="radio" aria-checked="false" data-role="Cashier">Cashier</button>
          </div>

          <form id="loginForm" novalidate>
            <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />

            <div class="field">
              <label for="username">Username</label>
              <div class="input-wrap">
                <span class="icon">👤</span>
                <input
                  id="username"
                  name="username"
                  type="text"
                  autocomplete="username"
                  placeholder="e.g. j.delacruz"
                  minlength="3"
                  maxlength="50"
                  pattern="[A-Za-z0-9._-]+"
                  required
                />
              </div>
              <p class="error-text" id="username-error">Enter your username.</p>
            </div>

            <div class="field">
              <label for="password">Password</label>
              <div class="input-wrap">
                <span class="icon">🔒</span>
                <input
                  id="password"
                  name="password"
                  type="password"
                  autocomplete="current-password"
                  placeholder="••••••••"
                  maxlength="255"
                  required
                />
                <button type="button" class="toggle-visibility" id="toggleVisibility" aria-label="Show password">👁️</button>
              </div>
              <p class="error-text" id="password-error">Enter your password.</p>
            </div>

            <div class="form-error-box" id="formError" role="alert"></div>

            <button type="submit" class="submit" id="submitBtn">Sign in as Admin →</button>
          </form>
        </div>
      </div>

      <div class="legal-footer">
        Secure sign in · <a href="#">Privacy</a> · <a href="#">Terms</a>
      </div>
    </div>
  </div>

  <script src="js/login.js"></script>

</body>
</html>
