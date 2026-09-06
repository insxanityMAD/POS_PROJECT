# MR. DIY Login System - Setup

## Files
- `config.php` — PDO database connection. Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- `login.php` — the sign-in page (HTML/CSS matching your Figma design).
- `assets/js/login.js` — front-end validation only (email format, password length, show/hide password). It never talks to the database directly.
- `login_process.php` — PHP script the form submits to. This is what actually connects to the database, checks the credentials, and does the role-based redirect.
- `admin_dashboard.php` — landing page for `role_name = 'Admin'`.
- `staff_panel.php` — landing page for everyone else (Manager / Cashier / Supplier).
- `logout.php` — destroys the session.
- `create_test_user.php` — one-time script to insert a test Admin with a correctly hashed password.

## Why passwords weren't logging in from your SQL dump
Your `pos_computer_accounting.sql` has no seed rows in the `users` table, and even if it did, storing plain-text passwords won't work with this system. PHP's `password_verify()` requires the password to have been created with `password_hash()`. Steps:

1. Import `pos_computer_accounting.sql` into MySQL/MariaDB.
2. Put all these files in your web root (e.g. `htdocs/mr_diy_login/` for XAMPP).
3. Update the DB credentials in `config.php`.
4. Visit `create_test_user.php` once in your browser to create a working Admin login (`admin` / `Admin123!`), then delete that file.
5. Go to `login.php` and sign in.

## How the role check works
`login_process.php` joins `users` to `roles` on `role_id`, reads `role_name`, and redirects:
- `Admin` → `admin_dashboard.php`
- anything else (`Manager`, `Cashier`, `Supplier`) → `staff_panel.php`

You can split those further into their own pages later by adding more `case` branches in the `switch` statement.
