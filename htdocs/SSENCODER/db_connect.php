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
    // This block runs when you are on the same PC or accessing via 192.168.x.x
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';           // Default XAMPP password is empty
    $db   = 'inventory_db'; // Your LOCAL database name

} else {

    // --- ONLINE INFINITYFREE SETTINGS ---
    // This block runs only when uploaded to the real website
    $host = 'sql310.infinityfree.com';
    $db   = 'if0_39889135_inventory_db';
    $user = 'if0_39889135';
    $pass = '20iMPoj25601'; // Your Online Password
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
} catch (\PDOException $e) {
     http_response_code(500);
     echo json_encode([
       'success' => false, 
       'message' => 'Database connection failed: ' . $e->getMessage() . '. (Host: ' . $host . ')'
     ]);
     exit;
}
?>