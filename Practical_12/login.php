
<?php
session_start();

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

require_once "db.php";

$error = "";
$notice = "";
$authError = "";

if (isset($_GET["auth"]) && $_GET["auth"] === "required") {
    $authError = "Please log in first to access the dashboard.";
} elseif (isset($_GET["logout"])) {
    $notice = "You have been logged out successfully.";
} elseif (isset($_GET["timeout"])) {
    $notice = "Your session expired. Please log in again.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, password_hash
             FROM admins
             WHERE username = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify(
            $password,
            $admin["password_hash"]
        )) {
            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];
            $_SESSION["last_activity"] = time();

            header("Location: dashboard.php");
            exit;
        }

        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Event Management</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: #eef2f7;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(0,0,0,.08);
        }
        h1 { margin-top: 0; text-align: center; }
        label { display: block; margin: 16px 0 6px; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd3dd;
            border-radius: 6px;
        }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            background: #2457d6;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            padding: 10px;
            color: #9b1c1c;
            background: #fee2e2;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <main class="login-box">
        <h1>Admin Login</h1>
        <p>Sign in to manage events.</p>

        <?php if ($notice !== ""): ?>
            <p style="padding:10px; color:#166534; background:#dcfce7; border-radius:6px;"
            role="status">
                <?= htmlspecialchars($notice, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <?php if ($authError !== ""): ?>
            <p class="error" role="alert">
                <?= htmlspecialchars($authError, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                maxlength="50"
                autocomplete="username"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login</button>
        </form>
    </main>
</body>
</html>
