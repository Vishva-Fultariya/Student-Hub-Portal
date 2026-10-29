<?php

session_start();

require_once "db.php";

// Accept only POST requests.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Validate required fields.
if ($email === "" || $password === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION["login_error"] = "Invalid email or password.";
    header("Location: login.php");
    exit;
}

// Find the user by email.
$stmt = $conn->prepare(
    "SELECT id, username, email, password, role
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Verify the password hash.
if (!$user || !password_verify($password, $user["password"])) {
    $_SESSION["login_error"] = "Invalid email or password.";
    header("Location: login.php");
    exit;
}

// Accept only the supported roles.
$role = strtolower($user["role"]);

if (!in_array($role, ["admin", "student"], true)) {
    $_SESSION["login_error"] = "Account role is invalid.";
    header("Location: login.php");
    exit;
}

// Regenerate the session ID after authentication.
session_regenerate_id(true);

// Store authenticated user details.
$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $role;

// Track activity for session timeout.
$_SESSION["last_activity"] = time();

// Redirect according to role.
if ($role === "admin") {
    header("Location: admin_dashboard.php");
} else {
    header("Location: student_dashboard.php");
}

exit;
?>
