
<?php
// ============================================
// WDF Practical 13 - Students JSON API
// PHP + MySQL + Fetch API
// ============================================

header('Content-Type: application/json; charset=UTF-8');

// Send a standardized JSON response and stop.
function sendResponse($statusCode, $status, $message, $data = null) {
    http_response_code($statusCode);

    $response = [
        'status' => $status,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

// Read and validate a JSON request body.
function readJsonBody() {
    $raw = file_get_contents('php://input');

    if ($raw === false || trim($raw) === '') {
        sendResponse(400, 'error', 'A JSON request body is required.');
    }

    $input = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
        sendResponse(400, 'error', 'Invalid JSON request body.');
    }

    return $input;
}

// Validate student fields before database operations.
function validateStudent($input, $requireId = false) {
    $id = null;

    if ($requireId) {
        $id = filter_var(
            $input['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($id === false || $id === null) {
            sendResponse(400, 'error', 'A valid student ID is required.');
        }
    }

    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $course = trim($input['course'] ?? '');
    $semester = filter_var(
        $input['semester'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($name === '' || $email === '' || $course === '') {
        sendResponse(
            400,
            'error',
            'Name, email, and course are required.'
        );
    }

    if (strlen($name) > 100 || strlen($course) > 100) {
        sendResponse(
            400,
            'error',
            'Name and course must not exceed 100 characters.'
        );
    }

    if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, 'error', 'Enter a valid email address.');
    }

    if (
        $semester === false ||
        $semester === null ||
        $semester < 1 ||
        $semester > 8
    ) {
        sendResponse(
            400,
            'error',
            'Semester must be an integer between 1 and 8.'
        );
    }

    $student = [
        'name' => $name,
        'email' => $email,
        'course' => $course,
        'semester' => $semester
    ];

    if ($requireId) {
        $student['id'] = $id;
    }

    return $student;
}

// Select the API action and HTTP method.
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$allowedMethods = [
    'list' => ['GET'],
    'get' => ['GET'],
    'search' => ['GET'],
    'create' => ['POST'],
    'update' => ['POST'],
    'delete' => ['POST']
];

if ($action === '' || !isset($allowedMethods[$action])) {
    sendResponse(404, 'error', 'Unknown API action.');
}

if (!in_array($method, $allowedMethods[$action], true)) {
    header('Allow: ' . implode(', ', $allowedMethods[$action]));
    sendResponse(405, 'error', 'HTTP method not allowed for this action.');
}

// Connect to the database.
require_once __DIR__ . '/db.php';

try {
    switch ($action) {

        // READ: List all students
        case 'list':
            $stmt = $pdo->query(
                'SELECT id, name, email, course, semester, created_at
                 FROM students
                 ORDER BY id DESC'
            );

            sendResponse(
                200,
                'success',
                'Students retrieved successfully.',
                $stmt->fetchAll()
            );
            break;
        
        // SEARCH: Find students by name, email, or course
        case 'search':
            $query = trim($_GET['q'] ?? '');

            if (strlen($query) > 100) {
                sendResponse(400, 'error', 'Search query must not exceed 100 characters.');
            }

            $stmt = $pdo->prepare(
                'SELECT id, name, email, course, semester, created_at
                 FROM students
                 WHERE name LIKE ?
                    OR email LIKE ?
                    OR course LIKE ?
                 ORDER BY id DESC'
            );

            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

            sendResponse(
                200,
                'success',
                'Search completed successfully.',
                $stmt->fetchAll()
            );
            break;    

        // READ: Get one student
        case 'get':
            $id = filter_var(
                $_GET['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($id === false || $id === null) {
                sendResponse(400, 'error', 'A valid student ID is required.');
            }

            $stmt = $pdo->prepare(
                'SELECT id, name, email, course, semester, created_at
                 FROM students WHERE id = ?'
            );

            $stmt->execute([$id]);
            $student = $stmt->fetch();

            if (!$student) {
                sendResponse(404, 'error', 'Student not found.');
            }

            sendResponse(200, 'success', 'Student retrieved successfully.', $student);
            break;

        // CREATE: Add a student
        case 'create':
            $student = validateStudent(readJsonBody());

            $stmt = $pdo->prepare(
                'INSERT INTO students (name, email, course, semester)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->execute([
                $student['name'],
                $student['email'],
                $student['course'],
                $student['semester']
            ]);

            $id = (int) $pdo->lastInsertId();

            sendResponse(
                201,
                'success',
                'Student created successfully.',
                ['id' => $id]
            );
            break;

        // UPDATE: Edit an existing student
        case 'update':
            $student = validateStudent(readJsonBody(), true);

            $stmt = $pdo->prepare(
                'UPDATE students
                 SET name = ?, email = ?, course = ?, semester = ?
                 WHERE id = ?'
            );

            $stmt->execute([
                $student['name'],
                $student['email'],
                $student['course'],
                $student['semester'],
                $student['id']
            ]);

            if ($stmt->rowCount() === 0) {
                $check = $pdo->prepare(
                    'SELECT id FROM students WHERE id = ?'
                );
                $check->execute([$student['id']]);

                if (!$check->fetch()) {
                    sendResponse(404, 'error', 'Student not found.');
                }
            }

            sendResponse(200, 'success', 'Student updated successfully.');
            break;

        // DELETE: Remove a student
        case 'delete':
            $input = readJsonBody();

            $id = filter_var(
                $input['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($id === false || $id === null) {
                sendResponse(400, 'error', 'A valid student ID is required.');
            }

            $stmt = $pdo->prepare(
                'DELETE FROM students WHERE id = ?'
            );
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                sendResponse(404, 'error', 'Student not found.');
            }

            sendResponse(200, 'success', 'Student deleted successfully.');
            break;
    }

} catch (PDOException $e) {
    error_log('Students API database error: ' . $e->getMessage());

    // MySQL duplicate entry error (for example, duplicate email).
    if ($e->getCode() === '23000') {
        sendResponse(409, 'error', 'This email may already be registered.');
    }

    sendResponse(500, 'error', 'A database error occurred.');
} catch (Throwable $e) {
    error_log('Students API error: ' . $e->getMessage());
    sendResponse(500, 'error', 'An internal server error occurred.');
}
?>
