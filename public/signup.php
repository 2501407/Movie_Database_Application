<?php
session_start();

define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', '../assets/');

require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Already logged in? Redirect.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle Sign-Up POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!validateCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name     = trim($_POST['name']             ?? '');
        $email    = trim($_POST['email']            ?? '');
        $password =      $_POST['password']         ?? '';
        $confirm  =      $_POST['confirm_password'] ?? '';

        // Server-side validation
        if ($name === '' || $email === '' || $password === '' || $confirm === '') {
            $error = 'All fields are required.';
        } elseif (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $db = getDB();

            // Check for duplicate email — PREPARED STATEMENT
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // Insert new user — password_hash for bcrypt
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $db->prepare(
                    "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)"
                );
                $ins->execute([
                    ':name'     => $name,
                    ':email'    => $email,
                    ':password' => $hashed,
                ]);

                // Redirect to login page after successful registration
                $_SESSION['signup_success'] = 'Account created successfully! Please log in.';
                header('Location: login.php');
                exit;
            }
        }
    }
}

// Render 
$pageTitle = 'Create Account';
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

    <h1 class="auth-title">Create account</h1>
    <p class="auth-subtitle">Sign up to get started</p>

    <!-- Error banner -->
    <?php if ($error): ?>
        <div class="auth-error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Sign-Up Form -->
    <form method="POST" action="signup.php" class="auth-form" id="signupForm">
        <?= csrfField() ?>

        <div class="form-group">
            <label for="name">Full name</label>
            <input
                type="text"
                id="name"
                name="name"
                placeholder="John Doe"
                value="<?= h($_POST['name'] ?? '') ?>"
                autocomplete="name"
                required
            />
        </div>

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
            <!-- Live validation feedback (Ajax) -->
            <span class="field-hint" id="emailHint"></span>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                autocomplete="new-password"
                required
            />
            <span class="field-hint" id="passwordHint"></span>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="••••••••"
                autocomplete="new-password"
                required
            />
            <span class="field-hint" id="confirmHint"></span>
        </div>

        <button type="submit" class="btn btn-auth-submit">Create account</button>
    </form>

    <p class="auth-footer-link">
        Already have an account? <a href="login.php">Sign in</a>
    </p>

</div>

<script src="<?= h(ASSETS_PATH) ?>js/main.js"></script>
<script>
// Live email-duplicate check (Ajax) 
(function() {
    const emailInput = document.getElementById('email');
    const emailHint  = document.getElementById('emailHint');
    let   debounceTimer = null;

    emailInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const val = emailInput.value.trim();
        emailHint.textContent = '';
        emailHint.className   = 'field-hint';

        if (val === '' || !val.includes('@')) return;   // wait for something meaningful

        debounceTimer = setTimeout(function() {
            fetch('check_email.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    'email=' + encodeURIComponent(val)
            })
            .then(r => r.json())
            .then(data => {
                if (data.exists) {
                    emailHint.textContent = 'This email is already taken.';
                    emailHint.className   = 'field-hint field-hint--error';
                } else {
                    emailHint.textContent = 'Email is available ✓';
                    emailHint.className   = 'field-hint field-hint--ok';
                }
            })
            .catch(() => {});   // silently ignore network issues
        }, 400);   // 400 ms debounce
    });
})();

// Live password-match check 
(function() {
    const pw      = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const hint    = document.getElementById('confirmHint');
    const pwHint  = document.getElementById('passwordHint');

    function checkPassword() {
        const v = pw.value;
        if (v.length === 0) { pwHint.textContent = ''; return; }
        if (v.length < 6) {
            pwHint.textContent = 'At least 6 characters required.';
            pwHint.className   = 'field-hint field-hint--error';
        } else {
            pwHint.textContent = 'Password length is good ✓';
            pwHint.className   = 'field-hint field-hint--ok';
        }
    }

    function checkConfirm() {
        if (confirm.value.length === 0) { hint.textContent = ''; return; }
        if (pw.value !== confirm.value) {
            hint.textContent = 'Passwords do not match.';
            hint.className   = 'field-hint field-hint--error';
        } else {
            hint.textContent = 'Passwords match ✓';
            hint.className   = 'field-hint field-hint--ok';
        }
    }

    pw.addEventListener('input',      checkPassword);
    confirm.addEventListener('input', checkConfirm);
    pw.addEventListener('input',      checkConfirm);   // re-check on password change too
})();
</script>
</body>
</html>
