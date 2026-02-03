<?php
session_start();

define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', '../assets/');

require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Already logged in? Redirect to dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Get signup success message if it exists
$successMsg = '';
if (isset($_SESSION['signup_success'])) {
    $successMsg = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}

$error = '';

// Handle Login POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!validateCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        // Server-side validation
        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Look up user — PREPARED STATEMENT (SQL Injection prevention)
            $db   = getDB();
            $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            // Verify password hash
            if ($user && password_verify($password, $user['password'])) {
                // ── Success: start session ──
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['email']    = $user['email'];

                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

// Render 
$pageTitle = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= h(ASSETS_PATH) ?>css/style.css" />
</head>
<body class="auth-body">

<div class="auth-container">

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your account to continue</p>

    <!-- Success banner (signup success) -->
    <?php if ($successMsg): ?>
        <div class="auth-success"><?= h($successMsg) ?></div>
    <?php endif; ?>

    <!-- Error banner -->
    <?php if ($error): ?>
        <div class="auth-error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Login Form — CSRF token included (mandatory for POST) -->
    <form method="POST" action="login.php" class="auth-form" id="loginForm">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                value="<?= h($_POST['email'] ?? '') ?>"
                autocomplete="email"
                required
            />
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="current-password"
                required
            />
        </div>

        <button type="submit" class="btn btn-auth-submit">Sign in</button>
    </form>

    <p class="auth-footer-link">
        Don't have an account? <a href="signup.php">Sign up</a>
    </p>

</div>

</script>
</body>
</html>
