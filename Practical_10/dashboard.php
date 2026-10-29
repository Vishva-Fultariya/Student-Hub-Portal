
<?php

require_once "timeout.php";

if ($_SESSION["role"] === "admin") {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SESSION["role"] === "student") {
    header("Location: student_dashboard.php");
    exit;
}

// Reject unsupported roles.
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;

?>
