<?php
// header.php — shared top of every page
// $pageTitle should be set BEFORE including this file.
if (!isset($pageTitle)) $pageTitle = 'Movie Database';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= h(ASSETS_PATH) ?>css/style.css" />
</head>
<body>
<!-- Navigation Bar -->
<header class="navbar">
    <div class="navbar-brand">
        <a href="index.php" class="brand-link">
            <span class="brand-icon">🎬</span>
            <div class="brand-text">
                <span class="brand-title">Movie Database</span>
                <span class="brand-sub">Manage your movie collection with ease</span>
            </div>
        </a>
    </div>
    <nav class="navbar-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="editModeBtn" class="btn btn-edit-mode" onclick="toggleEditMode()">
                ✎ Edit Mode
            </button>
            <span class="navbar-user-info">
                Logged in as <strong><?= h($_SESSION['username']) ?></strong>
            </span>
            <a href="logout.php" class="btn btn-logout">➾ Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">⟶ Login</a>
        <?php endif; ?>
    </nav>
</header>
<!-- Main Content -->
<main class="main-content">
