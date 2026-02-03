<?php
// check_email.php — Ajax endpoint 
// Returns JSON: { "exists": true|false }
// Used by the sign-up page for live email validation.

session_start();

define('ROOT_PATH',  realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
require_once ROOT_PATH . 'config/db.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');

// Basic sanity check
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Prepared statement — SQL Injection safe
$db   = getDB();
$stmt = $db->prepare("SELECT 1 FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);

echo json_encode(['exists' => (bool)$stmt->fetch()]);
?>
