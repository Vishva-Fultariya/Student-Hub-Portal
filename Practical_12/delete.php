
<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php?auth=required");
    exit;
}

// Enforce the same inactivity timeout as the other admin pages.
$timeout = 15 * 60;

if (
    !isset($_SESSION["last_activity"]) ||
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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Verify the CSRF token.
$token = $_POST["csrf_token"] ?? "";
$sessionToken = $_SESSION["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !is_string($sessionToken) ||
    $sessionToken === "" ||
    !hash_equals($sessionToken, $token)
) {
    http_response_code(403);
    exit("Invalid security token. Please return to the event list and try again.");
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php?type=error&msg=" . urlencode("Invalid event ID."));
    exit;
}

require_once "db.php";

try {
    $stmt = $conn->prepare(
        "SELECT poster FROM events WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) {
        header("Location: index.php?type=error&msg=" . urlencode("Event not found."));
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM events WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Remove the poster only after the database deletion succeeds.
    if (!empty($event["poster"])) {
        $posterPath = __DIR__ . "/uploads/" .
            basename($event["poster"]);

        if (is_file($posterPath)) {
            if (!unlink($posterPath)) {
                error_log("Could not remove poster for deleted event ID " . $id);
            }
        }
    }

    header("Location: index.php?msg=" .
        urlencode("Event deleted successfully."));
    exit;

} catch (mysqli_sql_exception $e) {
    error_log("Event deletion failed: " . $e->getMessage());

    header("Location: index.php?type=error&msg=" . urlencode("Could not delete the event. Please try again."));
    exit;
}
?>
