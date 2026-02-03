<?php
session_start();

define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', '../assets/');

require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error   = '';
$success = '';

// ─── Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. CSRF validation
    if (!validateCsrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        // 2. Get and sanitize inputs
        $title    = trim($_POST['title']    ?? '');
        $year     = trim($_POST['year']     ?? '');
        $genre    = trim($_POST['genre']    ?? '');
        $rating   = trim($_POST['rating']   ?? '');
        $director = trim($_POST['director'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $cast     = trim($_POST['cast']     ?? '');
        $synopsis = trim($_POST['synopsis'] ?? '');
        
        // 3. Server-side validation
        if (empty($title)) {
            $error = 'Title is required.';
        } elseif (empty($year) || !is_numeric($year) || $year < 1800 || $year > 2100) {
            $error = 'Please enter a valid year (1800-2100).';
        } elseif (empty($genre)) {
            $error = 'Genre is required.';
        } elseif (empty($rating) || !is_numeric($rating) || $rating < 0 || $rating > 10) {
            $error = 'Rating must be between 0 and 10.';
        } elseif (empty($director)) {
            $error = 'Director is required.';
        } elseif (empty($duration)) {
            $error = 'Duration is required.';
        } else {
            // 4. Insert into database — PREPARED STATEMENT
            try {
                $db = getDB();
                $stmt = $db->prepare(
                    "INSERT INTO movies (title, year, genre, rating, director, duration, cast, synopsis)
                     VALUES (:title, :year, :genre, :rating, :director, :duration, :cast, :synopsis)"
                );
                
                $stmt->execute([
                    ':title'    => $title,
                    ':year'     => (int)$year,
                    ':genre'    => $genre,
                    ':rating'   => (float)$rating,
                    ':director' => $director,
                    ':duration' => $duration,
                    ':cast'     => $cast,
                    ':synopsis' => $synopsis,
                ]);
                
                // Success - redirect to dashboard
                $_SESSION['movie_added'] = 'Movie "' . $title . '" added successfully!';
                header('Location: index.php');
                exit;
                
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ─── Render
$pageTitle = 'Add New Movie';
require_once ROOT_PATH . 'includes/header.php';
?>

<div class="form-container">
    
    <div class="form-header">
        <h1 class="form-title">➕ Add New Movie</h1>
        <p class="form-subtitle">Fill in the details below to add a new movie to your collection</p>
    </div>

    <!-- Error message -->
    <?php if ($error): ?>
        <div class="form-error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- Add Movie Form -->
    <form method="POST" action="add.php" class="movie-form">
        
        <?= csrfField() ?>

        <div class="form-row">
            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" 
                       value="<?= h($_POST['title'] ?? '') ?>" 
                       placeholder="e.g. The Shawshank Redemption" required>
            </div>

            <div class="form-group">
                <label for="year">Year <span class="required">*</span></label>
                <input type="number" id="year" name="year" 
                       value="<?= h($_POST['year'] ?? '') ?>" 
                       placeholder="e.g. 1994" min="1800" max="2100" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="genre">Genre <span class="required">*</span></label>
                <input type="text" id="genre" name="genre" 
                       value="<?= h($_POST['genre'] ?? '') ?>" 
                       placeholder="e.g. Drama, Thriller" required>
                <small class="field-hint">Separate multiple genres with commas</small>
            </div>

            <div class="form-group">
                <label for="rating">Rating <span class="required">*</span></label>
                <input type="number" id="rating" name="rating" 
                       value="<?= h($_POST['rating'] ?? '') ?>" 
                       placeholder="e.g. 8.5" min="0" max="10" step="0.1" required>
                <small class="field-hint">Rate from 0 to 10</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="director">Director <span class="required">*</span></label>
                <input type="text" id="director" name="director" 
                       value="<?= h($_POST['director'] ?? '') ?>" 
                       placeholder="e.g. Christopher Nolan" required>
            </div>

            <div class="form-group">
                <label for="duration">Duration <span class="required">*</span></label>
                <input type="text" id="duration" name="duration" 
                       value="<?= h($_POST['duration'] ?? '') ?>" 
                       placeholder="e.g. 2h 22m" required>
                <small class="field-hint">Format: Xh Ym</small>
            </div>
        </div>

        <div class="form-group">
            <label for="cast">Cast</label>
            <input type="text" id="cast" name="cast" 
                   value="<?= h($_POST['cast'] ?? '') ?>" 
                   placeholder="e.g. Tim Robbins, Morgan Freeman">
            <small class="field-hint">Separate actors with commas (optional)</small>
        </div>

        <div class="form-group">
            <label for="synopsis">Synopsis</label>
            <textarea id="synopsis" name="synopsis" rows="4" 
                      placeholder="Brief description of the movie..."><?= h($_POST['synopsis'] ?? '') ?></textarea>
            <small class="field-hint">Optional</small>
        </div>

        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">💾 Add Movie</button>
        </div>

    </form>

</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
