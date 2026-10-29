<?php

include "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";

$errors = [];

// Backend validation

if ($username === "" || strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = "Username must be between 3 and 50 characters.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }

    echo '<p><a href="index.php">Go Back</a></p>';
    exit;
}

// Check duplicate username/email

$check = $conn->prepare(
    "SELECT id FROM users WHERE username = ? OR email = ?"
);

$check->bind_param("ss", $username, $email);
$check->execute();

$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<h3>Registration Failed</h3>";
    echo "<p>Username or email already exists.</p>";
    echo '<p><a href="index.php">Go Back</a></p>';

    $check->close();
    $conn->close();
    exit;
}

$check->close();

// Hash password

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user

$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
);

$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {

    echo "<h2>Registration Successful!</h2>";
    echo "<p>Your account has been created successfully.</p>";
    echo '<p><a href="index.php">Register Another User</a></p>';

} else {

    echo "<h2>Registration Failed</h2>";
    echo "<p>Something went wrong. Please try again.</p>";
}

$stmt->close();
$conn->close();

?>