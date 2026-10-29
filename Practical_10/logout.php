
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear all session variables.
$_SESSION = [];

// Expire the session cookie.
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

// Destroy the session.
session_destroy();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout Successful</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Logout Successful</h2>
        <p>You have been logged out successfully.</p>
        <a href="login.php">Login Again</a>
    </div>
</body>
</html>
