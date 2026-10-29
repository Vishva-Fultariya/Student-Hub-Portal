<?php
session_start();

require_once 'auth.php';
require_once 'db.php';
require_once 'menu.php';
require_once 'audit.php';

require_admin($pdo);
$notice = '';
$noticeType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $notice = 'The request could not be verified. Reload the page and try again.';
        $noticeType = 'danger';
    } else {
        $targetId = filter_var(
            $_POST['user_id'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $newStatus = $_POST['status'] ?? '';

        if (!$targetId || !in_array($newStatus, ['active', 'inactive'], true)) {
            http_response_code(422);
            $notice = 'A valid user and account status are required.';
            $noticeType = 'danger';
        } elseif ((int) $targetId === (int) $_SESSION['user_id'] && $newStatus === 'inactive') {
            http_response_code(422);
            $notice = 'You cannot deactivate your own account while signed in.';
            $noticeType = 'danger';
        } else {
            $stmt = $pdo->prepare(
                'SELECT id, full_name, role, status
                 FROM users
                 WHERE id = ?'
            );
            $stmt->execute([$targetId]);
            $target = $stmt->fetch();

            if (!$target) {
                http_response_code(404);
                $notice = 'User not found.';
                $noticeType = 'danger';
            } elseif ($target['status'] === $newStatus) {
                $notice = 'The account already has that status.';
            } else {
                $mayChange = true;
                if ($target['role'] === 'admin' && $target['status'] === 'active' && $newStatus === 'inactive') {
                    $activeAdmins = (int) $pdo->query(
                        "SELECT COUNT(*) FROM users
                         WHERE role = 'admin' AND status = 'active'"
                    )->fetchColumn();

                    if ($activeAdmins <= 1) {
                        $mayChange = false;
                        http_response_code(409);
                        $notice = 'The last active administrator cannot be deactivated.';
                        $noticeType = 'danger';
                    }
                }

                if ($mayChange) {
                    try {
                        $pdo->beginTransaction();
                        $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
                        $stmt->execute([$newStatus, $targetId]);
                        log_audit(
                            $pdo,
                            (int) $_SESSION['user_id'],
                            'USER_STATUS_CHANGED',
                            'Changed ' . $target['full_name'] . ' (user ID ' . $targetId .
                                ') from ' . $target['status'] . ' to ' . $newStatus
                        );
                        $pdo->commit();
                        $notice = 'Account status updated.';
                    } catch (Throwable $e) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        error_log('User status audit logging failed: ' . $e->getMessage());
                        http_response_code(500);
                        $notice = 'The status change could not be completed. Check the server log.';
                        $noticeType = 'danger';
                    }
                }
            }
        }
    }
}

$users = $pdo->query(
    'SELECT id, full_name, email, role, status, created_at
     FROM users
     ORDER BY id'
)->fetchAll();
$menu = get_role_menu($pdo, $_SESSION['role']);
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Management | StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">StudentHub Admin</a>
        <div class="navbar-nav me-auto">
            <?php foreach ($menu as $item): ?>
                <a class="nav-link" href="<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h3 mb-3">User Management</h1>
    <?php if ($notice !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($noticeType, ENT_QUOTES, 'UTF-8') ?>"
             role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="alert alert-info">
        Deactivating an account blocks future logins. You cannot deactivate yourself or the last active administrator.
    </div>
    <section class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="6" class="text-center py-4">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php $isSelf = (int) $user['id'] === (int) $_SESSION['user_id']; ?>
                        <tr>
                            <td><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($isSelf): ?>
                                    <span class="text-muted">Current account</span>
                                <?php else: ?>
                                    <form method="post" class="d-inline"
                                          onsubmit="return confirm('Change this account status?');">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                        <input type="hidden" name="status"
                                               value="<?= $user['status'] === 'active' ? 'inactive' : 'active' ?>">
                                        <button class="btn btn-sm <?= $user['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                type="submit">
                                            <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
