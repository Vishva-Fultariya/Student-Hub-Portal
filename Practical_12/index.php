
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php?auth=required");
    exit;
}

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

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "db.php";

$message = trim($_GET["msg"] ?? "");
$messageType = $_GET["type"] ?? "success";
$error = "";
$allowedCategories = [
    "Workshop",
    "Seminar",
    "Conference",
    "Cultural",
    "Sports",
    "Other"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $sessionToken = $_SESSION["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !is_string($sessionToken) ||
        $sessionToken === "" ||
        !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);
        exit("Invalid security token. Please refresh the page and try again.");
    }
    $title = trim($_POST["title"] ?? "");
    $eventDate = trim($_POST["event_date"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = trim($_POST["status"] ?? "");

    if (
        $title === "" ||
        $eventDate === "" ||
        $location === "" ||
        $category === "" ||
        $status === ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (
        strlen($title) > 150 ||
        strlen($location) > 150
    ) {
        $error = "Title and location must not exceed 150 characters.";
    } elseif (
        !in_array($category, $allowedCategories, true)
    ) {
        $error = "Please select a valid event category.";
    } elseif (!in_array($status, ["open", "closed"], true)) {
        $error = "Please select a valid event status.";
    } else {
        $dateObject = DateTime::createFromFormat("!Y-m-d", $eventDate);
        $dateErrors = DateTime::getLastErrors();

        if (
            !$dateObject ||
            ($dateErrors !== false &&
             ($dateErrors["warning_count"] > 0 ||
              $dateErrors["error_count"] > 0)) ||
            $dateObject->format("Y-m-d") !== $eventDate
        ) {
            $error = "Please enter a valid event date.";
        } else {
            
require_once "upload.php";

$upload = upload_poster($_FILES["poster"] ?? []);

if (!$upload["success"]) {
    $error = $upload["error"];
} else {
    $poster = $upload["filename"];

    try {
        $stmt = $conn->prepare(
            "INSERT INTO events
                (title, event_date, location, category, status, poster)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssss",
            $title,
            $eventDate,
            $location,
            $category,
            $status,
            $poster
        );

        $stmt->execute();

        header("Location: index.php?msg=" .
            urlencode("Event created successfully."));
        exit;
    } catch (mysqli_sql_exception $e) {
        // Remove a newly uploaded poster if the database insert fails.
        if ($poster !== null) {
            $posterPath = __DIR__ . "/uploads/" . basename($poster);

            if (is_file($posterPath)) {
                unlink($posterPath);
            }
        }

        error_log("Event creation failed: " . $e->getMessage());
        $error = "Could not create the event. Please try again.";
    }
}

        }
    }
}

$search = trim($_GET["search"] ?? "");
$filterCategory = trim($_GET["category"] ?? "");
$filterStatus = trim($_GET["status"] ?? "");

$sql = "SELECT id, title, event_date, location, category, status, poster
        FROM events
        WHERE 1=1";
$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (title LIKE ? OR location LIKE ?)";
    $term = "%" . $search . "%";
    $types .= "ss";
    $params[] = $term;
    $params[] = $term;
}

if (in_array($filterCategory, $allowedCategories, true)) {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $filterCategory;
}

if (in_array($filterStatus, ["open", "closed"], true)) {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $filterStatus;
}

$sql .= " ORDER BY event_date ASC, id DESC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$events = $stmt->get_result();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Event Management</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }
        header {
            padding: 18px 5%;
            background: #172554;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        header a { color: white; }
        main { max-width: 1200px; margin: 28px auto; padding: 0 18px; }
        .panel {
            background: white;
            padding: 22px;
            margin-bottom: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,.05);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
        }
        label { display: block; margin-bottom: 6px; font-weight: bold; }
        input, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
        }
        button, .button {
            display: inline-block;
            padding: 11px 15px;
            border: 0;
            border-radius: 6px;
            background: #2457d6;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        .success {
            padding: 12px;
            border-radius: 6px;
            background: #dcfce7;
            color: #166534;
        }
        .error {
            padding: 12px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; }
        .poster { width: 75px; height: 60px; object-fit: cover; border-radius: 5px; }
        .badge { padding: 5px 8px; border-radius: 5px; background: #e2e8f0; }
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end; }
    </style>
</head>
<body>
<header>
    <strong>Event Management System</strong>
    <nav>
        <a href="dashboard.php">Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <h1>Manage Events</h1>

    <?php if ($message !== ""): ?>
        <div class="<?= $messageType === "error" ? "error" : "success" ?>">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error" role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <section class="panel">
        <h2>Add New Event</h2>

        <form method="POST" action="index.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
            <div class="form-grid">
                <div>
                    <label for="title">Event Title</label>
                    <input id="title" name="title" maxlength="150" required>
                </div>

                <div>
                    <label for="event_date">Event Date</label>
                    <input id="event_date" type="date" name="event_date" required>
                </div>

                <div>
                    <label for="location">Location</label>
                    <input id="location" name="location" maxlength="150" required>
                </div>

                <div>
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        <?php foreach ($allowedCategories as $cat): ?>
                            <option value="<?= h($cat) ?>"><?= h($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div>
                    <label for="poster">Event Poster</label>
                    <input id="poster" type="file" name="poster" accept=".jpg,.jpeg,.png,.webp">
                    <small>Poster upload will be enabled after we secure the upload handler.</small>
                </div>
            </div>

            <p><button type="submit">Validate Event</button></p>
        </form>
    </section>

    <section class="panel">
        <h2>Search and Filter Events</h2>

        <form method="GET" action="index.php" class="filters">
            <div>
                <label for="search">Search title or location</label>
                <input id="search" name="search" value="<?= h($search) ?>" placeholder="Enter search term">
            </div>

            <div>
                <label for="filter_category">Category</label>
                <select id="filter_category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($allowedCategories as $cat): ?>
                        <option value="<?= h($cat) ?>" <?= $filterCategory === $cat ? "selected" : "" ?>>
                            <?= h($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="filter_status">Status</label>
                <select id="filter_status" name="status">
                    <option value="">All statuses</option>
                    <option value="open" <?= $filterStatus === "open" ? "selected" : "" ?>>Open</option>
                    <option value="closed" <?= $filterStatus === "closed" ? "selected" : "" ?>>Closed</option>
                </select>
            </div>

            <div>
                <button type="submit">Apply Filters</button>
                <a class="button" href="index.php">Reset</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Event List</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($events->num_rows === 0): ?>
                    <tr><td colspan="8">No events found.</td></tr>
                <?php else: ?>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$event["id"] ?></td>
                            <td>
                                <?php if (!empty($event["poster"])): ?>
                                    <img class="poster"
                                         src="uploads/<?= h(basename($event["poster"])) ?>"
                                         alt="Poster for <?= h($event["title"]) ?>">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>
                            <td><?= h($event["title"]) ?></td>
                            <td><?= h($event["event_date"]) ?></td>
                            <td><?= h($event["location"]) ?></td>
                            <td><?= h($event["category"]) ?></td>
                            <td><span class="badge"><?= h(ucfirst($event["status"])) ?></span></td>
                            <td>
                                <a href="edit.php?id=<?= (int)$event["id"] ?>">Edit</a>
                                <form method="POST" action="delete.php"
                                    onsubmit="return confirm('Are you sure you want to permanently delete this event?');"
                                    style="display:inline">

                                    <input type="hidden" name="id"
                                        value="<?= (int)$event["id"] ?>">

                                    <input type="hidden" name="csrf_token"
                                        value="<?= h($_SESSION["csrf_token"]) ?>">

                                    <button type="submit"
                                            style="background:#b91c1c">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
