<?php 

require 'db.php'; 

$pdo->exec("CREATE DATABASE IF NOT EXISTS polling_db");

$pdo->exec("USE polling_db");

$pdo->exec("CREATE TABLE IF NOT EXISTS users ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    username VARCHAR(50) UNIQUE NOT NULL, 
    password VARCHAR(255) NOT NULL, 
    is_admin BOOLEAN DEFAULT 0
);"); 

$pdo->exec("CREATE TABLE IF NOT EXISTS polls ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);");

$pdo-> exec("CREATE TABLE IF NOT EXISTS choices ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    poll_id INT NOT NULL, 
    choice_text VARCHAR(255) NOT NULL, 
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
);"); 

$pdo->exec("CREATE TABLE IF NOT EXISTS votes ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT NOT NULL, 
    choice_id INT NOT NULL, 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, 
    FOREIGN KEY (choice_id) REFERENCES choices(id) ON DELETE CASCADE
);");

?>
