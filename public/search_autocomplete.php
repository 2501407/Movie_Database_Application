<?php
// search_autocomplete.php — Ajax endpoint 
// Returns JSON array of movie suggestions for autocomplete.

session_start();

define('ROOT_PATH',  realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
require_once ROOT_PATH . 'config/db.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

// Return empty array if query is too short
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Search in title, genre, director — PREPARED STATEMENT (SQL Injection safe)
$db = getDB();
$stmt = $db->prepare(
    "SELECT id, title, year, director
     FROM movies
     WHERE title LIKE :query
        OR genre LIKE :query
        OR director LIKE :query
     ORDER BY title ASC
     LIMIT 10"
);

$likeQuery = '%' . $query . '%';
$stmt->execute([':query' => $likeQuery]);

$results = $stmt->fetchAll();

// Format results as simple array with id, label
$suggestions = array_map(function($row) {
    return [
        'id'    => $row['id'],
        'label' => $row['title'] . ' (' . $row['year'] . ') — ' . $row['director']
    ];
}, $results);

echo json_encode($suggestions);
