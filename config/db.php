<?php

define('DB_HOST',     'localhost');
define('DB_NAME',     'np03cs4a240033');
define('DB_USER',     'np03cs4a240033');           
define('DB_PASS',     'hYZPp57rUU');               
define('DB_CHARSET',  'utf8mb4');
define('DB_PORT',     '50222');
/**
 * Creates and returns a PDO connection instance (singleton pattern).
 * Uses prepared-statement-ready PDO so every query is protected against SQL injection.
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // Force real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
?>