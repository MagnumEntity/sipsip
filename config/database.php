<?php
// config/database.php

// Explicitly configure timezone for the Philippines
date_default_timezone_set('Asia/Manila');

$db_host = 'localhost';
$db_user = 'root'; // default XAMPP user
$db_pass = '';     // default XAMPP password
$db_name = 'sipsip';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
