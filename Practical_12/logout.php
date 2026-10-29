
<?php
session_start();

// Clear all session data.
$_SESSION = [];

// Remove the session cookie from the browser.
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

// Destroy the server-side session.
session_destroy();

// Redirect to login with a logout message.
header("Location: login.php?logout=1");
exit;
?>
