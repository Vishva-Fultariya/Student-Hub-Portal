
<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header(
        'Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : 'home.php')
    );
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = $_POST['email'] ?? '';
    $passwordInput = $_POST['password'] ?? '';
    $email = is_string($emailInput) ? trim($emailInput) : '';
    $password = is_string($passwordInput) ? $passwordInput : '';

    $user = null;
    if ($email !== '' && $password !== '') {
        $stmt = $pdo->prepare(
            "SELECT id, full_name, email, password_hash, role, status
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    }

    if (
        $user &&
        $user['status'] === 'active' &&
        password_verify($password, $user['password_hash'])
    ) {
        session_regenerate_id(true);

        require_once 'audit.php';

        try {
            log_audit(
                $pdo,
                (int) $user['id'],
                'LOGIN',
                'Successful login'
            );
        } catch (Throwable $e) {
            error_log('Login audit logging failed: ' . $e->getMessage());
            require_once 'auth.php';
            end_session();
            $error = 'Sign-in could not be completed. Please try again later.';
        }

        if ($error === '') {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header(
                'Location: ' . ($user['role'] === 'admin' ? 'dashboard.php' : 'home.php')
            );
            exit;
        }
    }

    if ($error === '') {
        $error = 'Invalid email or password, or account is inactive.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Practical 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 430px;">
        <div class="card-body p-4">
            <h1 class="h3 mb-3">StudentHub Admin Portal</h1>
            <p class="text-secondary">Sign in to continue.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input class="form-control" type="email"
                           id="email" name="email" required
                           autocomplete="username">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input class="form-control" type="password"
                           id="password" name="password" required
                           autocomplete="current-password">
                </div>

                <button class="btn btn-primary w-100" type="submit">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
