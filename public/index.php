<?php
session_start();

// Resolve paths relative to public/
define('ROOT_PATH',   realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', '../assets/');                       // relative for <link>/<script>

require_once ROOT_PATH . 'config/db.php';
require_once ROOT_PATH . 'includes/functions.php';

// Handle Ajax Requests
// Autocomplete search
if (isset($_GET['action']) && $_GET['action'] === 'autocomplete') {
    header('Content-Type: application/json');
    
    $query = trim($_GET['q'] ?? '');
    
    if (strlen($query) < 2) {
        echo json_encode([]);
        exit;
    }
    
    try {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT id, title, year, genre, director
             FROM movies
             WHERE title LIKE :query1
                OR genre LIKE :query2
                OR director LIKE :query3
                OR CAST(year AS CHAR) LIKE :query4
             ORDER BY title ASC
             LIMIT 15"
        );
        
        $likeQuery = '%' . $query . '%';
        $stmt->execute([
            ':query1' => $likeQuery,
            ':query2' => $likeQuery,
            ':query3' => $likeQuery,
            ':query4' => $likeQuery
        ]);
        $results = $stmt->fetchAll();
        
        $suggestions = array_map(function($row) {
            // Format: "The Godfather (1972) — Crime, Drama — Francis Ford Coppola"
            return [
                'id'       => $row['id'],
                'title'    => $row['title'],
                'year'     => $row['year'],
                'genre'    => $row['genre'],
                'director' => $row['director'],
                'label'    => $row['title'] . ' (' . $row['year'] . ') — ' . $row['director']
            ];
        }, $results);
        
        echo json_encode($suggestions);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}

// Page Logic 
$pageTitle = 'Movie Database';

// Check if this is first login (show welcome banner)
$isFirstLogin = false;
if (isset($_SESSION['user_id']) && !isset($_SESSION['has_visited'])) {
    $isFirstLogin = true;
    $_SESSION['has_visited'] = true;
}

try {
    $db = getDB();
    
    // Summary stats (top cards)
    $stats = [
        'total_movies'  => (int) $db->query("SELECT COUNT(*) FROM movies")->fetchColumn(),
        'unique_genres' => (int) $db->query("SELECT COUNT(DISTINCT genre) FROM movies")->fetchColumn(),
    ];
    
    // Average rating
    $avgRow = $db->query("SELECT ROUND(AVG(rating), 1) AS avg_rating FROM movies")->fetch();
    $stats['avg_rating'] = $avgRow['avg_rating'] ?? 0;
    
    // Movie list with pagination
    $perPage = 10;
    
    // Count total for pagination
    $totalMovies = (int) $db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
    $pag = paginate($totalMovies, $perPage);
    
    // Fetch current page of movies (ordered by title)
    $stmt = $db->prepare(
        "SELECT id, title, year, genre, rating, director, duration
         FROM movies
         ORDER BY title ASC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->execute([':limit' => $pag['limit'], ':offset' => $pag['offset']]);
    $movies = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "<br><br>Make sure you've run setup.sql first!");
}

// Render 
require_once ROOT_PATH . 'includes/header.php';

// Success/Error Messages 
$successMsg = '';
$errorMsg   = '';

if (isset($_SESSION['movie_added'])) {
    $successMsg = $_SESSION['movie_added'];
    unset($_SESSION['movie_added']);
}
if (isset($_SESSION['movie_updated'])) {
    $successMsg = $_SESSION['movie_updated'];
    unset($_SESSION['movie_updated']);
}
if (isset($_SESSION['movie_deleted'])) {
    $successMsg = $_SESSION['movie_deleted'];
    unset($_SESSION['movie_deleted']);
}
if (isset($_SESSION['movie_error'])) {
    $errorMsg = $_SESSION['movie_error'];
    unset($_SESSION['movie_error']);
}
?>

<!-- Success/Error Notifications -->
<?php if ($successMsg): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        <?= h($successMsg) ?>
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <div class="alert alert-error">
        <span class="alert-icon">✗</span>
        <?= h($errorMsg) ?>
    </div>
<?php endif; ?>


<!-- 
     SUMMARY CARDS
     -->
<section class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon stat-icon--movies">🎞️</div>
        <div class="stat-body">
            <span class="stat-label">Total Movies</span>
            <span class="stat-value"><?= h((string)$stats['total_movies']) ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--genres">📓</div>
        <div class="stat-body">
            <span class="stat-label">Unique Genres</span>
            <span class="stat-value"><?= h((string)$stats['unique_genres']) ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon stat-icon--rating">⭐</div>
        <div class="stat-body">
            <span class="stat-label">Average Rating</span>
            <span class="stat-value"><?= h($stats['avg_rating'] . '/10') ?></span>
        </div>
    </div>

</section>

<!-- 
     MOVIE TABLE
     -->
<section class="table-section">

    <!-- Welcome Banner (on first login) -->
    <?php if ($isFirstLogin): ?>
    <div class="welcome-banner">
        <span class="banner-icon">👋</span>
        <div>
            <strong>Welcome, <?= h($_SESSION['username']) ?>!</strong>
            <span>Edit Mode is now active. You can add, edit, and delete movies from your collection.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Mode Banner (only shows when logged in and edit mode is active) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div id="editModeBanner" class="edit-mode-banner" style="display: <?= $isFirstLogin ? 'flex' : 'none' ?>;">
        <span class="banner-icon">✎</span>
        <strong>Edit Mode Active:</strong> You can now edit and delete movies from the list.
    </div>
    <?php endif; ?>

    <div class="table-header">
        <div>
            <h2 class="table-title">All Movies</h2>
            <p class="table-subtitle">Browse, search, and view detailed information about movies</p>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="add.php" class="btn btn-primary">+ Add Movie</a>
        <?php endif; ?>
    </div>

    <!-- Search bar -->
    <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input
            type="text"
            id="searchInput"
            class="search-input"
            placeholder="Search movies by title, genre, director, or year..."
            autocomplete="off"
        />
    </div>

    <!-- Result count -->
    <p class="result-count" id="resultCount">
        Showing <?= h((string)count($movies)) ?> of <?= h((string)$pag['total']) ?> movies
    </p>

    <!-- Table -->
    <div class="table-wrap">
        <table class="movie-table" id="movieTable">
            <thead>
                <tr>
                    <th>Title ⇅</th>
                    <th>Year</th>
                    <th>Genre</th>
                    <th>Rating</th>
                    <th>Director</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movies as $m): ?>
                <tr>
                    <td class="col-title"><?= h($m['title']) ?></td>
                    <td><?= h((string)$m['year']) ?></td>
                    <td class="col-genre"><?= h($m['genre']) ?></td>
                    <td class="col-rating">
                        <span class="star">★</span> <?= h((string)$m['rating']) ?>
                    </td>
                    <td><?= h($m['director']) ?></td>
                    <td><?= h($m['duration']) ?></td>
                    <td class="col-actions">
                        <!-- View button - always visible -->
                        <a href="view.php?id=<?= h((string)$m['id']) ?>" class="btn btn-view">👁 View</a>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Edit and Delete buttons (only visible in edit mode) -->
                            <a href="edit.php?id=<?= h((string)$m['id']) ?>" 
                               class="btn btn-edit edit-mode-only" 
                               style="display: <?= $isFirstLogin ? 'inline-flex' : 'none' ?>;">
                                ✎ Edit
                            </a>
                            <a href="delete.php?id=<?= h((string)$m['id']) ?>" 
                               class="btn btn-delete edit-mode-only" 
                               style="display: <?= $isFirstLogin ? 'inline-flex' : 'none' ?>;">
                                🗑 Delete
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movies)): ?>
                <tr><td colspan="7" class="empty-msg">No movies found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Page Slider Pagination ── -->
    <?php if ($pag['total_pages'] > 1): ?>
    <div class="pagination-slider">
        <div class="slider-info">
            <span>Page <?= h((string)$pag['current_page']) ?> of <?= h((string)$pag['total_pages']) ?></span>
            <span class="slider-count">
                Showing <?= h((string)(($pag['current_page']-1)*$pag['limit']+1)) ?>
                - <?= h((string)min($pag['current_page']*$pag['limit'], $pag['total'])) ?>
                of <?= h((string)$pag['total']) ?> movies
            </span>
        </div>
        
        <!-- Slider control -->
        <div class="slider-control">
            <a href="?page=1" class="slider-btn slider-first <?= ($pag['current_page'] <= 1 ? 'disabled' : '') ?>">«</a>
            <a href="?page=<?= h((string)max(1, $pag['current_page']-1)) ?>" 
               class="slider-btn slider-prev <?= ($pag['current_page'] <= 1 ? 'disabled' : '') ?>">‹</a>
            
            <div class="slider-track">
                <input type="range" 
                       id="pageSlider" 
                       class="page-slider" 
                       min="1" 
                       max="<?= h((string)$pag['total_pages']) ?>" 
                       value="<?= h((string)$pag['current_page']) ?>"
                       step="1">
                <div class="slider-fill" style="width: <?= h((string)(($pag['current_page']-1)/max(1,$pag['total_pages']-1)*100)) ?>%"></div>
            </div>
            
            <a href="?page=<?= h((string)min($pag['total_pages'], $pag['current_page']+1)) ?>" 
               class="slider-btn slider-next <?= ($pag['current_page'] >= $pag['total_pages'] ? 'disabled' : '') ?>">›</a>
            <a href="?page=<?= h((string)$pag['total_pages']) ?>" 
               class="slider-btn slider-last <?= ($pag['current_page'] >= $pag['total_pages'] ? 'disabled' : '') ?>">»</a>
        </div>
    </div>
    <?php endif; ?>

</section>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>

