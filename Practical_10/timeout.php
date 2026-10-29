
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$timeout_duration = 300; // 5 minutes

// Case 1: User is not logged in.
if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    $_SESSION["login_error"] =
        "Please log in first to access the dashboard.";

    header("Location: login.php");
    exit;
}

// Case 2: User's session has expired.
if (
    !isset($_SESSION["last_activity"]) ||
    (time() - $_SESSION["last_activity"]) > $timeout_duration
) {
    // Clear all session data.
    $_SESSION = [];

    // Expire the existing session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the expired session.
    session_destroy();

    // Start a fresh session for the timeout message.
    session_start();
    session_regenerate_id(true);

    $_SESSION["login_error"] =
        "Your session has expired due to inactivity. Please log in again.";

    header("Location: login.php");
    exit;
}

// Update the last activity time.
$_SESSION["last_activity"] = time();

?>
