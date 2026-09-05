<?php
// auth.php
// Handles POST requests from login.php / js/login.js.
// Expects JSON body: { "csrf_token": "...", "role": "Admin"|"Manager"|"Cashier", "username": "...", "password": "..." }

session_start();
header("Content-Type: application/json");

require "db_connect.php";

// ---------------------------------------------------------
// 1. Brute-force protection: lock out after too many fails
// ---------------------------------------------------------
const MAX_ATTEMPTS = 5;
const LOCKOUT_SECONDS = 60;

if (!isset($_SESSION["login_attempts"])) {
    $_SESSION["login_attempts"] = 0;
    $_SESSION["lockout_until"] = 0;
}

if ($_SESSION["lockout_until"] > time()) {
    $waitSeconds = $_SESSION["lockout_until"] - time();
    echo json_encode([
        "success" => false,
        "message" => "Too many failed attempts. Try again in {$waitSeconds}s."
    ]);
    exit;
}

// ---------------------------------------------------------
// 2. CSRF check
// ---------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

$csrfToken = isset($data["csrf_token"]) ? $data["csrf_token"] : "";
if (!isset($_SESSION["csrf_token"]) || !hash_equals($_SESSION["csrf_token"], $csrfToken)) {
    echo json_encode(["success" => false, "message" => "Invalid session. Please refresh the page and try again."]);
    exit;
}

// ---------------------------------------------------------
// 3. Read + validate input format
// ---------------------------------------------------------
$role     = isset($data["role"]) ? trim($data["role"]) : "";
$username = isset($data["username"]) ? trim($data["username"]) : "";
$password = isset($data["password"]) ? $data["password"] : "";

$allowedRoles = ["Admin", "Manager", "Cashier"];

if ($username === "" || $password === "" || $role === "") {
    echo json_encode(["success" => false, "message" => "Missing username, password, or role."]);
    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    echo json_encode(["success" => false, "message" => "Invalid role selected."]);
    exit;
}

// Username: letters, numbers, dot, underscore, hyphen only, 3-50 chars
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    echo json_encode(["success" => false, "message" => "Username format is invalid."]);
    exit;
}

// Password: reasonable length bounds (avoid abuse, no upper hard requirement beyond safety)
if (strlen($password) < 1 || strlen($password) > 255) {
    echo json_encode(["success" => false, "message" => "Password format is invalid."]);
    exit;
}

// ---------------------------------------------------------
// 4. Look up the user by username (prepared statement -- prevents SQL injection)
// ---------------------------------------------------------
$stmt = $conn->prepare(
    "SELECT user_id, username, password, full_name, role, status
     FROM users
     WHERE username = ?
     LIMIT 1"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Use one generic message for "no such user" and "wrong password"
// so failed logins don't reveal which part was wrong (avoids account enumeration).
$genericFail = ["success" => false, "message" => "Invalid username or password."];

if ($result->num_rows === 0) {
    registerFailedAttempt();
    echo json_encode($genericFail);
    exit;
}

$user = $result->fetch_assoc();

// --- Check password ---
// NOTE: This compares plain text for now, matching the current `users` table.
// Before real deployment, store hashed passwords with password_hash() on signup,
// and replace the line below with: password_verify($password, $user["password"])
$passwordMatches = ($password === $user["password"]);

if (!$passwordMatches) {
    registerFailedAttempt();
    echo json_encode($genericFail);
    exit;
}

// --- Check account status ---
if (strtolower($user["status"]) !== "active") {
    // Deliberately still generic here to avoid confirming the account exists,
    // but distinct enough to be useful for a legitimate employee.
    echo json_encode(["success" => false, "message" => "This account is not active. Contact an administrator."]);
    exit;
}

// --- Check the selected role matches the account's actual role ---
if (strtolower($user["role"]) !== strtolower($role)) {
    registerFailedAttempt();
    echo json_encode($genericFail);
    exit;
}

// ---------------------------------------------------------
// 5. Success: reset attempt counter, regenerate session ID
//    (prevents session fixation), start the session.
// ---------------------------------------------------------
$_SESSION["login_attempts"] = 0;
$_SESSION["lockout_until"] = 0;
session_regenerate_id(true);

$_SESSION["user_id"]   = $user["user_id"];
$_SESSION["username"]  = $user["username"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["role"]      = $user["role"];

// Issue a fresh CSRF token for the next request
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));

// For now, just confirm access was granted. No redirect yet.
echo json_encode([
    "success" => true,
    "message" => "Access granted.",
    "user" => [
        "id" => $user["user_id"],
        "username" => htmlspecialchars($user["username"], ENT_QUOTES, 'UTF-8'),
        "full_name" => htmlspecialchars($user["full_name"], ENT_QUOTES, 'UTF-8'),
        "role" => $user["role"]
    ]
]);

// ---------------------------------------------------------
// Helper: increments the failed-attempt counter and locks out
// the session once MAX_ATTEMPTS is reached.
// ---------------------------------------------------------
function registerFailedAttempt() {
    $_SESSION["login_attempts"]++;
    if ($_SESSION["login_attempts"] >= MAX_ATTEMPTS) {
        $_SESSION["lockout_until"] = time() + LOCKOUT_SECONDS;
        $_SESSION["login_attempts"] = 0;
    }
}
