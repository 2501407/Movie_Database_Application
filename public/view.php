<?php
session_start();

define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', '../assets/');

require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Get Movie ID 
$movieId = (int)($_GET['id'] ?? 0);
if ($movieId <= 0) {
    http_response_code(404);
    exit('Movie not found.');
}

// Fetch Movie Details 
$db   = getDB();
$stmt = $db->prepare("SELECT * FROM movies WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    http_response_code(404);
    exit('Movie not found.');
}

// Parse additional fields (cast, synopsis) from JSON if stored, or use defaults
$cast     = $movie['cast']     ?? 'Cast information not available';
$synopsis = $movie['synopsis'] ?? 'Synopsis not available';

$pageTitle = h($movie['title']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= h(ASSETS_PATH) ?>css/style.css" />
</head>
<body class="view-body">

<!-- Modal Overlay -->
<div class="modal-overlay" onclick="window.history.back()"></div>

<!-- Movie Detail Modal -->
<div class="movie-modal">
    
    <!-- Close button (X) -->
    <button class="modal-close" onclick="window.history.back()">✕</button>

    <!-- Gradient Header -->
    <div class="modal-header">
        <h1 class="modal-title"><?= h($movie['title']) ?></h1>
        <div class="modal-meta">
            <span class="meta-item">📅 <?= h((string)$movie['year']) ?></span>
            <span class="meta-item">🕐 <?= h($movie['duration']) ?></span>
            <span class="meta-rating">⭐ <?= h((string)$movie['rating']) ?>/10</span>
        </div>
    </div>

    <!-- Body Content -->
    <div class="modal-body">

        <!-- Genre & Director Row -->
        <div class="info-row">
            <div class="info-col">
                <div class="info-label">🎬 GENRE</div>
                <div class="info-value"><?= h($movie['genre']) ?></div>
            </div>
            <div class="info-col">
                <div class="info-label">👤 DIRECTOR</div>
                <div class="info-value"><?= h($movie['director']) ?></div>
            </div>
        </div>

        <!-- Cast -->
        <div class="section">
            <div class="section-label">CAST</div>
            <div class="section-value"><?= h($cast) ?></div>
        </div>

        <!-- Synopsis -->
        <div class="section">
            <div class="section-label">SYNOPSIS</div>
            <div class="section-value"><?= h($synopsis) ?></div>
        </div>

        <!-- Rating Details with Bar -->
        <div class="section">
            <div class="section-label">RATING DETAILS</div>
            <div class="rating-display">
                <span class="rating-number"><?= h((string)$movie['rating']) ?></span>
                <span class="rating-total">out of 10</span>
            </div>
            <div class="rating-bar-wrap">
                <div class="rating-bar-fill" style="width: <?= h((string)($movie['rating'] * 10)) ?>%"></div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="modal-footer">
        <button class="btn btn-modal-close" onclick="window.history.back()">Close</button>
    </div>

</div>

</body>
</html>
