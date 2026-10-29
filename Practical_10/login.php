<?php
session_start();

// If already logged in, redirect to the dashboard.
if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_SESSION["login_error"] ?? "";
unset($_SESSION["login_error"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login - Practical 10</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>User Login</h2>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <form action="authenticate.php" method="POST">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
