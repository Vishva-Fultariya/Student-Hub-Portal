
<?php
session_start();

require_once 'auth.php';
require_once 'db.php';
require_once 'menu.php';

require_admin($pdo);

$sql = "SELECT
            a.id,
            a.action,
            a.details,
            a.ip_address,
            a.created_at,
            u.full_name,
            u.email
        FROM audit_logs a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC, a.id DESC
        LIMIT 100";

$stmt = $pdo->query($sql);
$logs = $stmt->fetchAll();

$menu = get_role_menu($pdo, $_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | StudentHub Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">StudentHub Admin</a>

        <div class="navbar-nav me-auto">
            <?php foreach ($menu as $item): ?>
                <a class="nav-link"
                   href="<?= htmlspecialchars($item['link']) ?>">
                    <?= htmlspecialchars($item['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['full_name']) ?>
        </span>

        <a href="logout.php" class="btn btn-outline-light btn-sm">
            Logout
        </a>
    </div>
</nav>

<main class="container py-4">
    <h1 class="h3 mb-2">Audit Logs</h1>
    <p class="text-muted">
        Records of important activities performed in the system.
    </p>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <strong>Recent Activity</strong>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center">
                                No audit records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= (int) $log['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        $log['full_name'] ?? 'Unknown user'
                                    ) ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars(
                                            $log['email'] ?? ''
                                        ) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge text-bg-primary">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($log['details'] ?? '') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($log['created_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-muted small mb-0">
                Showing up to the 100 most recent audit records.
            </p>
        </div>
    </div>
</main>

</body>
</html>
