<?php
require 'db.php';

function registerUser($pdo, $username, $password, $is_admin = 0) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    return $stmt->execute([$username, $hashedPassword, $is_admin]);
}

function loginUser($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Ensure the admin account exists
$adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
if ($adminExists == 0) {
    registerUser($pdo, 'admin', 'RxkORJDkzl1234ZKK', 1);
}
?>

