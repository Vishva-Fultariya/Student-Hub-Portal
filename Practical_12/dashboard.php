
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php?auth=required");
    exit;
}

// End the session after 15 minutes of inactivity.
$timeout = 15 * 60;

if (
    isset($_SESSION["last_activity"]) &&
    (time() - $_SESSION["last_activity"]) > $timeout
) {
    $_SESSION = [];

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

    session_destroy();

    header("Location: login.php?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

require_once "db.php";

$result = $conn->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'open'), 0) AS open_events,
        COALESCE(SUM(status = 'closed'), 0) AS closed_events
     FROM events"
);

$stats = $result->fetch_assoc();

$username = htmlspecialchars(
    $_SESSION["admin_username"] ?? "Admin",
    ENT_QUOTES,
    "UTF-8"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Event Management</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        header {
            padding: 20px 6%;
            background: #172554;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        header h1 { margin: 0; font-size: 23px; }
        main { max-width: 1050px; margin: 35px auto; padding: 0 20px; }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 18px;
            margin: 25px 0;
        }
        .card {
            background: white;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }
        .number { font-size: 32px; font-weight: bold; margin-top: 10px; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 25px; }
        a.button {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 6px;
            background: #2457d6;
            color: white;
            text-decoration: none;
        }
        a.logout { background: #b91c1c; }
    </style>
</head>
<body>
    <header>
        <h1>Event Management System</h1>
        <span>Welcome, <?= $username ?></span>
    </header>

    <main>
        <h2>Admin Dashboard</h2>
        <p>Manage your events from one place.</p>

        <section class="cards">
            <div class="card">
                Total Events
                <div class="number"><?= (int)$stats["total"] ?></div>
            </div>

            <div class="card">
                Open Events
                <div class="number"><?= (int)$stats["open_events"] ?></div>
            </div>

            <div class="card">
                Closed Events
                <div class="number"><?= (int)$stats["closed_events"] ?></div>
            </div>
        </section>

        <div class="actions">
            <a class="button" href="index.php">Manage Events</a>
            <a class="button logout" href="logout.php">Logout</a>
        </div>
    </main>
</body>
</html>
