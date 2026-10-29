
<?php

function end_session(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function require_login(PDO $pdo): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT full_name, email, role, status
         FROM users
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        end_session();
        header('Location: login.php');
        exit;
    }

    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_token_is_valid($token): bool
{
    return isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_admin(PDO $pdo): void
{
    require_login($pdo);

    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        require __DIR__ . '/403.php';
        exit;
    }
}
