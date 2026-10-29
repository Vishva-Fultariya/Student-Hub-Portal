
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
require_once "upload.php";

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$allowedCategories = [
    "Workshop", "Seminar", "Conference",
    "Cultural", "Sports", "Other"
];

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid event ID.");
}

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    http_response_code(404);
    exit("Event not found.");
}

$error = "";

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
        $title === "" || $eventDate === "" ||
        $location === "" || $category === "" || $status === ""
    ) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($title) > 150 || strlen($location) > 150) {
        $error = "Title and location must not exceed 150 characters.";
    } elseif (!in_array($category, $allowedCategories, true)) {
        $error = "Please select a valid category.";
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
        }
    }

    if ($error === "") {
        $upload = upload_poster($_FILES["poster"] ?? []);

        if (!$upload["success"]) {
            $error = $upload["error"];
        } else {
            $oldPoster = $event["poster"];
            $newPoster = $upload["filename"] ?? $oldPoster;

            try {
                $stmt = $conn->prepare(
                    "UPDATE events
                     SET title = ?, event_date = ?, location = ?,
                         category = ?, status = ?, poster = ?
                     WHERE id = ?"
                );

                $stmt->bind_param(
                    "ssssssi",
                    $title,
                    $eventDate,
                    $location,
                    $category,
                    $status,
                    $newPoster,
                    $id
                );

                $stmt->execute();

                // Remove the old image only after the database update succeeds.
                if (
                    $upload["filename"] !== null &&
                    !empty($oldPoster) &&
                    $oldPoster !== $newPoster
                ) {
                    $oldPath = __DIR__ . "/uploads/" . basename($oldPoster);

                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                header("Location: index.php?msg=" .
                    urlencode("Event updated successfully."));
                exit;
            } catch (mysqli_sql_exception $e) {
                // Keep the old poster if the database update fails.
                if ($upload["filename"] !== null) {
                    $newPath = __DIR__ . "/uploads/" .
                        basename($upload["filename"]);

                    if (is_file($newPath)) {
                        unlink($newPath);
                    }
                }

                error_log("Event update failed: " . $e->getMessage());
                $error = "Could not update the event. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 25px;
            font-family: Arial, sans-serif;
            background: #f1f5f9; color: #1e293b;
        }
        main {
            max-width: 720px; margin: 20px auto;
            padding: 25px; background: white;
            border-radius: 10px;
        }
        label { display: block; margin: 15px 0 6px; font-weight: bold; }
        input, select {
            width: 100%; padding: 11px;
            border: 1px solid #cbd5e1; border-radius: 6px;
        }
        button, .button {
            display: inline-block; margin-top: 18px;
            padding: 11px 16px; border: 0; border-radius: 6px;
            background: #2457d6; color: white; text-decoration: none;
            cursor: pointer;
        }
        .error {
            padding: 12px; border-radius: 6px;
            color: #991b1b; background: #fee2e2;
        }
        .poster { display: block; max-width: 180px; max-height: 160px; margin-top: 10px; }
    </style>
</head>
<body>
<main>
    <h1>Edit Event</h1>

    <?php if ($error !== ""): ?>
        <p class="error" role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>">
        <label for="title">Event Title</label>
        <input id="title" name="title" maxlength="150"
               value="<?= h($_POST["title"] ?? $event["title"]) ?>" required>

        <label for="event_date">Event Date</label>
        <input id="event_date" type="date" name="event_date"
               value="<?= h($_POST["event_date"] ?? $event["event_date"]) ?>" required>

        <label for="location">Location</label>
        <input id="location" name="location" maxlength="150"
               value="<?= h($_POST["location"] ?? $event["location"]) ?>" required>

        <label for="category">Category</label>
        <select id="category" name="category" required>
            <?php foreach ($allowedCategories as $cat): ?>
                <option value="<?= h($cat) ?>"
                    <?= ($_POST["category"] ?? $event["category"]) === $cat ? "selected" : "" ?>>
                    <?= h($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="open"
                <?= ($_POST["status"] ?? $event["status"]) === "open" ? "selected" : "" ?>>
                Open
            </option>
            <option value="closed"
                <?= ($_POST["status"] ?? $event["status"]) === "closed" ? "selected" : "" ?>>
                Closed
            </option>
        </select>

        <label>Current Poster</label>
        <?php if (!empty($event["poster"])): ?>
            <img class="poster"
                 src="uploads/<?= h(basename($event["poster"])) ?>"
                 alt="Current event poster">
        <?php else: ?>
            <p>No poster uploaded.</p>
        <?php endif; ?>

        <label for="poster">Replace Poster (optional)</label>
        <input id="poster" type="file" name="poster"
               accept=".jpg,.jpeg,.png,.webp">
        <small>Allowed formats: JPG, JPEG, PNG, WebP. Maximum size: 2 MB.</small>

        <br>
        <button type="submit">Update Event</button>
        <a class="button" href="index.php">Cancel</a>
    </form>
</main>
</body>
</html>
