// js/login.js
// Handles the MR. DIY staff sign-in form: role selection, password visibility,
// client-side validation, CSRF token handling, and the fetch() call to auth.php.

document.addEventListener("DOMContentLoaded", () => {
  let selectedRole = "Admin";

  const roleButtons = document.querySelectorAll(".role-btn");
  const submitBtn = document.getElementById("submitBtn");
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");
  const usernameError = document.getElementById("username-error");
  const passwordError = document.getElementById("password-error");
  const formError = document.getElementById("formError");
  const form = document.getElementById("loginForm");
  const toggleBtn = document.getElementById("toggleVisibility");
  const csrfInput = document.getElementById("csrfToken");

  const USERNAME_PATTERN = /^[A-Za-z0-9._-]{3,50}$/;

  // --- Role selector ---
  roleButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      roleButtons.forEach((b) => {
        b.classList.remove("active");
        b.setAttribute("aria-checked", "false");
      });
      btn.classList.add("active");
      btn.setAttribute("aria-checked", "true");
      selectedRole = btn.dataset.role;
      submitBtn.textContent = `Sign in as ${selectedRole} →`;
    });
  });

  // --- Show/hide password ---
  toggleBtn.addEventListener("click", () => {
    const isPassword = passwordInput.type === "password";
    passwordInput.type = isPassword ? "text" : "password";
    toggleBtn.textContent = isPassword ? "🙈" : "👁️";
    toggleBtn.setAttribute("aria-label", isPassword ? "Hide password" : "Show password");
  });

  // --- Clear field errors as the user types ---
  function clearFieldError(input, errorEl) {
    input.classList.remove("error");
    errorEl.classList.remove("show");
  }

  usernameInput.addEventListener("input", () => {
    clearFieldError(usernameInput, usernameError);
    formError.classList.remove("show");
  });

  passwordInput.addEventListener("input", () => {
    clearFieldError(passwordInput, passwordError);
    formError.classList.remove("show");
  });

  // --- Submit ---
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    let hasError = false;

    const usernameVal = usernameInput.value.trim();

    if (!usernameVal) {
      usernameInput.classList.add("error");
      usernameError.textContent = "Enter your username.";
      usernameError.classList.add("show");
      hasError = true;
    } else if (!USERNAME_PATTERN.test(usernameVal)) {
      usernameInput.classList.add("error");
      usernameError.textContent = "Username must be 3–50 characters: letters, numbers, ., _, or - only.";
      usernameError.classList.add("show");
      hasError = true;
    }

    if (!passwordInput.value) {
      passwordInput.classList.add("error");
      passwordError.textContent = "Enter your password.";
      passwordError.classList.add("show");
      hasError = true;
    } else if (passwordInput.value.length > 255) {
      passwordInput.classList.add("error");
      passwordError.textContent = "Password is too long.";
      passwordError.classList.add("show");
      hasError = true;
    }

    if (hasError) {
      formError.textContent = "Fix the fields below and try again.";
      formError.classList.remove("success");
      formError.classList.add("show");
      return;
    }

    formError.classList.remove("show");
    submitBtn.disabled = true;
    submitBtn.textContent = "Signing in…";

    fetch("auth.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        csrf_token: csrfInput.value,
        role: selectedRole,
        username: usernameVal,
        password: passwordInput.value
      })
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          // For now: just confirm access. No redirect yet.
          formError.textContent = `✅ Access granted. Welcome, ${data.user.full_name} (${data.user.role}).`;
          formError.classList.add("show", "success");
          // Password cleared after a successful login for good hygiene.
          passwordInput.value = "";
        } else {
          formError.textContent = data.message || "Sign in failed.";
          formError.classList.remove("success");
          formError.classList.add("show");
        }
      })
      .catch(() => {
        formError.textContent = "Could not reach the server. Check that XAMPP/Apache is running.";
        formError.classList.remove("success");
        formError.classList.add("show");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = `Sign in as ${selectedRole} →`;
      });
  });
});
