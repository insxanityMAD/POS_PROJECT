// ============================================================
// login.js
// Client-side UX only: field validation + show/hide password.
// The actual database check happens in login_process.php on the
// server - JavaScript never connects to MySQL directly.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const form          = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passInput     = document.getElementById('password');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');
    const togglePass    = document.getElementById('togglePass');
    const submitBtn     = document.getElementById('submitBtn');

    // Show / hide password
    const eyeOpen   = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    togglePass.addEventListener('click', function () {
        const isHidden = passInput.type === 'password';
        passInput.type = isHidden ? 'text' : 'password';
        eyeOpen.style.display   = isHidden ? 'none' : 'block';
        eyeClosed.style.display = isHidden ? 'block' : 'none';
        togglePass.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });

    function validateUsername() {
        const valid = usernameInput.value.trim().length > 0;
        usernameError.style.display = valid ? 'none' : 'block';
        return valid;
    }

    function validatePassword() {
        const valid = passInput.value.length >= 6;
        passwordError.style.display = valid ? 'none' : 'block';
        return valid;
    }

    usernameInput.addEventListener('input', validateUsername);
    passInput.addEventListener('input', validatePassword);

    form.addEventListener('submit', function (e) {
        const usernameOk = validateUsername();
        const passOk     = validatePassword();

        if (!usernameOk || !passOk) {
            e.preventDefault();
            return;
        }

        // Prevent double-submit while the server processes the request
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';
    });
});
