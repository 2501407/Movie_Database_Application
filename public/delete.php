<?php
session_start();

define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get Movie ID
$movieId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($movieId <= 0) {
    $_SESSION['movie_error'] = 'Invalid movie ID.';
    header('Location: index.php');
    exit;
}

// Fetch Movie Title (for success message) 
try {
    $db = getDB();
    $stmt = $db->prepare("SELECT title FROM movies WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $movieId]);
    $movie = $stmt->fetch();
    
    if (!$movie) {
        $_SESSION['movie_error'] = 'Movie not found.';
        header('Location: index.php');
        exit;
    }
    
    $movieTitle = $movie['title'];
    
} catch (PDOException $e) {
    $_SESSION['movie_error'] = 'Database error: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}

// ─── Handle Deletion (POST with CSRF) 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF validation
    if (!validateCsrf()) {
        $_SESSION['movie_error'] = 'Invalid request. Security check failed.';
        header('Location: index.php');
        exit;
    }
    
    // Delete the movie — PREPARED STATEMENT
    try {
        $stmt = $db->prepare("DELETE FROM movies WHERE id = :id");
        $stmt->execute([':id' => $movieId]);
        
        $_SESSION['movie_deleted'] = 'Movie "' . $movieTitle . '" deleted successfully!';
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['movie_error'] = 'Failed to delete movie: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
}

// Show Confirmation Page (GET request)
define('ASSETS_PATH', '../assets/');
$pageTitle = 'Delete Movie';
require_once ROOT_PATH . 'includes/header.php';
?>

<div class="form-container">
    
    <div class="form-header">
        <h1 class="form-title">🗑️ Delete Movie</h1>
        <p class="form-subtitle">Are you sure you want to delete this movie?</p>
    </div>

    <div class="delete-confirmation">
        <div class="delete-movie-info">
            <div class="delete-icon">⚠️</div>
            <div>
                <h2 class="delete-movie-title"><?= h($movieTitle) ?></h2>
                <p class="delete-warning">This action cannot be undone. The movie will be permanently removed from your collection.</p>
            </div>
        </div>

        <form method="POST" action="delete.php" class="delete-form">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= h((string)$movieId) ?>">
            
            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-danger">🗑 Yes, Delete Movie</button>
            </div>
        </form>
    </div>

</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
