# MR. DIY POS / Computer Accounting System — Setup

## Project structure
```
config.php                  DB connection + BASE_URL setup (required by nearly everything)
index.php                   Redirects to the login page
includes/                   Shared code: header/footer shell, role+CSRF guard, activity logger
assets/                     CSS, JS, images (shared across all pages)
modules/
  auth/                     login.php, login_process.php, logout.php, staff_panel.php
  pos/                      pos_sales.php, pos_actions.php
  inventory/                inventory.php, inventory_actions.php
  suppliers/                manage_suppliers.php, supplier_actions.php
  payroll/                  payroll.php, payroll_actions.php
  expenses/                 expenses.php, expense_actions.php
  reports/                  reports.php, report_actions.php
  settings/                 settings.php, settings_actions.php, and the manage_tax/
                             manage_discounts/manage_qr_payments/manage_products
                             redirect shortcuts used by the dashboard's quick actions
  users/                    manage_users.php, user_actions.php
  logs/                     logs.php
```
Every page under `modules/` is a paired view + JSON action-handler file living in the same folder (e.g. `pos_sales.php` calls `pos_actions.php` as a sibling), so most internal links are plain relative paths. Cross-folder links (sidebar nav, login redirects, the dashboard's quick actions) go through the `BASE_URL` constant defined in `includes/base_url.php` so the app keeps working regardless of what the project folder is named or how deep a page lives.

## Setup
1. Import `MySQL DB/pos_computer_accounting (1).sql` into MySQL/MariaDB.
2. Put this whole folder in your web root (e.g. `htdocs/POS-PROJECT/` for XAMPP).
3. Update the DB credentials in `config.php`.
4. Visit `index.php` (or `modules/auth/login.php` directly) and sign in.

## Passwords
Passwords are stored with `password_hash()`/`password_verify()`. Any account still carrying a plain-text password from the original SQL dump is transparently upgraded to a hash the next time it logs in successfully — no migration step needed. New/edited accounts (via the Users page) are always hashed on save.

## How the role check works
`login_process.php` joins `users` to `roles` on `role_id`, reads `role_name`, and redirects:
- `Admin` / `Manager` → `admin_dashboard.php` (Manager's sidebar hides Settings/Users/Logs)
- `Cashier` → `modules/pos/pos_sales.php` directly — POS is the only thing a cashier account can reach
- `Supplier` (or anything else) → `modules/auth/staff_panel.php`, a placeholder landing page

Every page under `modules/` declares `$allowedRoles = [...]` before requiring `includes/admin_guard.php`, which enforces both the role check and CSRF verification on POST requests. Pages that don't set `$allowedRoles` default to Admin-only.
