
<?php
session_start();

require_once 'auth.php';
require_once 'db.php';
require_once 'menu.php';

require_admin($pdo);
require_once 'audit.php';

$auditWarning = false;
try {
    log_audit(
        $pdo,
        (int) $_SESSION['user_id'],
        'VIEW_DASHBOARD',
        'Opened admin dashboard'
    );
} catch (Throwable $e) {
    error_log('Dashboard audit logging failed: ' . $e->getMessage());
    $auditWarning = true;
}

// Fetch dashboard statistics from MySQL.
$userCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

$studentCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM students"
)->fetchColumn();

$eventCount = (int) $pdo->query(
    "SELECT COUNT(*) FROM events"
)->fetchColumn();

// Fetch the latest registrations with student and event details.
$stmt = $pdo->query(
    "SELECT
        r.id,
        s.student_name,
        s.enrollment_no,
        e.title AS event_title,
        r.registered_at
     FROM registrations r
     INNER JOIN students s ON r.student_id = s.id
     INNER JOIN events e ON r.event_id = e.id
     ORDER BY r.registered_at DESC, r.id DESC
     LIMIT 10"
);

$registrations = $stmt->fetchAll();

$menu = get_role_menu($pdo, $_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Practical 14</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            StudentHub Admin
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menu as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <span class="navbar-text me-3">
                <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>
                (Admin)
            </span>

            <a href="logout.php" class="btn btn-outline-light btn-sm">
                Logout
            </a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if ($auditWarning): ?>
        <div class="alert alert-warning" role="status">
            Dashboard access could not be added to the audit log. Check the server log.
        </div>
    <?php endif; ?>
    <div class="mb-4">
        <h1 class="h2">Admin Dashboard</h1>
        <p class="text-secondary">
            Monitor users, students, events and recent registrations.
        </p>
    </div>

    <!-- Dynamic dashboard statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Total Users</p>
                    <h2 class="display-6 fw-bold">
                        <?= $userCount ?>
                    </h2>
                    <span class="badge text-bg-primary">User Statistics</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Total Students</p>
                    <h2 class="display-6 fw-bold">
                        <?= $studentCount ?>
                    </h2>
                    <span class="badge text-bg-success">Student Records</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-secondary mb-2">Total Events</p>
                    <h2 class="display-6 fw-bold">
                        <?= $eventCount ?>
                    </h2>
                    <span class="badge text-bg-warning">Events Created</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest registrations -->
    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Latest Registrations</h2>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Student</th>
                            <th scope="col">Enrollment No.</th>
                            <th scope="col">Event</th>
                            <th scope="col">Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    No registrations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $registration): ?>
                                <tr>
                                    <td><?= (int) $registration['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($registration['student_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($registration['enrollment_no'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($registration['event_title'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($registration['registered_at'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <footer class="text-center text-secondary mt-4">
        Practical 14 — Admin Dashboard with RBAC and Audit Logging
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
