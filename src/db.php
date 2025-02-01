<?php
$host = 'localhost';
$dbname = 'polling_db';
$user = 'root';
$pass = 'rami2004'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

