
<?php
// Database configuration
$host = 'localhost';
$dbname = 'wdf_practical_13';
$username = 'root';
$password = ''; // Default XAMPP password is usually empty

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log technical details on the server, not in the API response.
    error_log('Database connection failed: ' . $e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);

    exit;
}
?>
