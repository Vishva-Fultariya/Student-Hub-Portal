
<?php

session_start();
require_once "db.php";

// Accept deletion requests only through POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION["message"] = "Invalid student ID. No record was changed.";
    $_SESSION["message_type"] = "error";
    header("Location: index.php");
    exit;
}

// Mark the student as inactive instead of permanently deleting them.
$stmt = $conn->prepare(
    "UPDATE students
     SET status = 'inactive'
     WHERE id = ? AND status = 'active'"
);

$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows === 1) {
    $_SESSION["message"] = "Student marked as inactive successfully.";
    $_SESSION["message_type"] = "success";
} else {
    $_SESSION["message"] = "Unable to delete student. The record may not exist or may already be inactive.";
    $_SESSION["message_type"] = "error";
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit;
?>
