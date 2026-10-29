
<?php

require_once "timeout.php";

if ($_SESSION["role"] !== "admin") {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Unauthorized Access</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <h2>Unauthorized Access</h2>
            <p>You are not authorized to access the admin dashboard.</p>
            <a href="dashboard.php">Go to your dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?= htmlspecialchars($_SESSION["username"], ENT_QUOTES, "UTF-8") ?>!</p>
        <p>You are logged in as an Administrator.</p>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
