<?php
/**
 * Universal Database Connection Script
 * Automatically switches between Local (XAMPP) and Online (InfinityFree) settings.
 */

// 1. Detect if we are running on Localhost (XAMPP) or Online
// We check for 'localhost', '127.0.0.1', OR any IP starting with '192.168.'
if ($_SERVER['SERVER_NAME'] == 'localhost' || 
    $_SERVER['SERVER_NAME'] == '127.0.0.1' || 
    strpos($_SERVER['SERVER_NAME'], '192.168.') === 0) {

    // --- LOCAL XAMPP SETTINGS ---
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';           // Default XAMPP password is empty
    $db   = 'inventory_db'; // Your LOCAL database name

} else {

    // --- ONLINE INFINITYFREE SETTINGS ---
    $host = 'localhost';
    $db   = 'inventory_db';
    $user = 'root';
    $pass = 'JJPoj256'; // Your Online Password
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->exec("SET time_zone = '+08:00';");
} catch (\PDOException $e) {
     http_response_code(500);
     echo json_encode([
       'success' => false, 
       'message' => 'Database connection failed: ' . $e->getMessage()
     ]);
     exit;
}
?>
