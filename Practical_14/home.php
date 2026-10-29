<?php
session_start();

require_once 'auth.php';
require_once 'db.php';
require_once 'menu.php';

require_login($pdo);
$menu = get_role_menu($pdo, $_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home | StudentHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="home.php">StudentHub</a>
        <div class="navbar-nav me-auto">
            <?php foreach ($menu as $item): ?>
                <a class="nav-link" href="<?= htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            (<?= htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8') ?>)
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<main class="container py-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h3">Welcome to StudentHub</h1>
            <p class="text-secondary mb-0">
                You are signed in as
                <strong><?= htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8') ?></strong>.
                Use the navigation to view available sections. Administrative pages remain
                restricted to administrators.
            </p>
        </div>
    </div>
</main>
</body>
</html>
