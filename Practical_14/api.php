<?php
session_start();

require_once 'db.php';
require_once 'audit.php';
require_once 'auth.php';

header('Content-Type: application/json; charset=utf-8');

function send_json(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    send_json(401, [
        'success' => false,
        'message' => 'Please log in first.'
    ]);
}

$identityStmt = $pdo->prepare(
    'SELECT role, status FROM users WHERE id = ? LIMIT 1'
);
$identityStmt->execute([(int) $_SESSION['user_id']]);
$identity = $identityStmt->fetch();

if (!$identity || $identity['status'] !== 'active') {
    end_session();
    send_json(401, [
        'success' => false,
        'message' => 'Your session is no longer active. Please log in again.'
    ]);
}

$_SESSION['role'] = $identity['role'];

if ($identity['role'] !== 'admin') {
    send_json(403, [
        'success' => false,
        'message' => 'Forbidden: administrator access required.'
    ]);
}

$method = $_SERVER['REQUEST_METHOD'];

if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    send_json(405, [
        'success' => false,
        'message' => 'This HTTP method is not supported.'
    ]);
}

if ($method === 'POST' && !csrf_token_is_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    send_json(403, [
        'success' => false,
        'message' => 'The request could not be verified. Reload the page and try again.'
    ]);
}

$rawBody = file_get_contents('php://input');
$decodedBody = $rawBody === '' ? (object) [] : json_decode($rawBody);

if ($rawBody !== '' && json_last_error() !== JSON_ERROR_NONE) {
    send_json(400, [
        'success' => false,
        'message' => 'The request body must contain valid JSON.'
    ]);
}

if (!is_object($decodedBody)) {
    send_json(400, [
        'success' => false,
        'message' => 'The JSON request body must be an object.'
    ]);
}

$input = get_object_vars($decodedBody);
$action = $_GET['action'] ?? $input['action'] ?? '';

function request_string(array $input, string $key): ?string
{
    $value = $input[$key] ?? null;

    return is_string($value) ? trim($value) : null;
}

function valid_event_title(string $title): bool
{
    return preg_match('/\A.{1,150}\z/us', $title) === 1;
}

function record_api_action(PDO $pdo, string $action, string $details): void
{
    log_audit(
        $pdo,
        (int) $_SESSION['user_id'],
        $action,
        $details
    );
}

try {
    if ($method === 'GET' && $action === 'events') {
        $stmt = $pdo->query(
            'SELECT id, title, event_date, created_at
             FROM events
             ORDER BY event_date DESC, id DESC'
        );

        send_json(200, [
            'success' => true,
            'events' => $stmt->fetchAll()
        ]);
    }

    if ($method === 'GET' && $action === 'stats') {
        $stats = [
            'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'students' => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
            'events' => (int) $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'registrations' => (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn()
        ];

        send_json(200, [
            'success' => true,
            'stats' => $stats
        ]);
    }

    if ($method === 'POST' && $action === 'create_event') {
        $title = request_string($input, 'title');
        $eventDate = request_string($input, 'event_date');
        $date = $eventDate === null
            ? false
            : DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate);

        if (
            $title === null ||
            $eventDate === null ||
            !valid_event_title($title) ||
            !$date ||
            $date->format('Y-m-d') !== $eventDate
        ) {
            send_json(422, [
                'success' => false,
                'message' => 'Provide a title of 1-150 characters and a valid date (YYYY-MM-DD).'
            ]);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO events (title, event_date)
             VALUES (:title, :event_date)'
        );
        $pdo->beginTransaction();
        $stmt->execute([
            ':title' => $title,
            ':event_date' => $eventDate
        ]);

        $id = (int) $pdo->lastInsertId();
        record_api_action($pdo, 'CREATE_EVENT', 'Created event ID ' . $id . ': ' . $title);
        $pdo->commit();

        send_json(201, [
            'success' => true,
            'message' => 'Event created successfully.',
            'id' => $id
        ]);
    }

    if ($method === 'POST' && $action === 'update_event') {
        $id = filter_var(
            $input['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $title = request_string($input, 'title');
        $eventDate = request_string($input, 'event_date');
        $date = $eventDate === null
            ? false
            : DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate);

        if (
            !$id ||
            $title === null ||
            $eventDate === null ||
            !valid_event_title($title) ||
            !$date ||
            $date->format('Y-m-d') !== $eventDate
        ) {
            send_json(422, [
                'success' => false,
                'message' => 'Provide a valid event ID, a title of 1-150 characters, and a valid date.'
            ]);
        }

        $stmt = $pdo->prepare(
            'UPDATE events
             SET title = :title, event_date = :event_date
             WHERE id = :id'
        );
        $pdo->beginTransaction();
        $stmt->execute([
            ':title' => $title,
            ':event_date' => $eventDate,
            ':id' => $id
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM events WHERE id = ?');
            $check->execute([$id]);

            if (!$check->fetch()) {
                $pdo->rollBack();
                send_json(404, [
                    'success' => false,
                    'message' => 'Event not found.'
                ]);
            }
        }

        record_api_action($pdo, 'UPDATE_EVENT', 'Updated event ID ' . $id);
        $pdo->commit();

        send_json(200, [
            'success' => true,
            'message' => 'Event updated successfully.'
        ]);
    }

    if ($method === 'POST' && $action === 'delete_event') {
        $id = filter_var(
            $input['id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if (!$id) {
            send_json(422, [
                'success' => false,
                'message' => 'A valid event ID is required.'
            ]);
        }

        $stmt = $pdo->prepare('SELECT title FROM events WHERE id = ?');
        $stmt->execute([$id]);
        $event = $stmt->fetch();

        if (!$event) {
            send_json(404, [
                'success' => false,
                'message' => 'Event not found.'
            ]);
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                send_json(409, [
                    'success' => false,
                    'message' => 'This event has registrations and cannot be deleted.'
                ]);
            }

            throw $e;
        }

        record_api_action(
            $pdo,
            'DELETE_EVENT',
            'Deleted event ID ' . $id . ': ' . $event['title']
        );
        $pdo->commit();

        send_json(200, [
            'success' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }

    send_json(400, [
        'success' => false,
        'message' => 'Unknown action.'
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('API database error: ' . $e->getMessage());

    send_json(500, [
        'success' => false,
        'message' => 'A database error occurred. Check server logs.'
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('API error: ' . $e->getMessage());

    send_json(500, [
        'success' => false,
        'message' => 'An unexpected server error occurred.'
    ]);
}
