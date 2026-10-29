
<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "student_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed. Please check your database settings.");
}

$conn->set_charset("utf8mb4");

?>
