
<?php
session_start();
require_once 'auth.php';

// Preserve the user ID, then invalidate the session before database work.
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
end_session();

require_once 'db.php';
require_once 'audit.php';

if ($userId !== null) {
    try {
        log_audit(
            $pdo,
            $userId,
            'LOGOUT',
            'User logged out'
        );
    } catch (Throwable $e) {
        // Logout should still work if audit logging fails.
        error_log('Logout audit logging failed: ' . $e->getMessage());
    }
}

header('Location: login.php');
exit;
