<?php
session_start();
require 'db.php';
require 'auth.php';

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch polls
$stmt = $pdo->query("SELECT * FROM polls ORDER BY created_at DESC");
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Voice Heard</title> <!-- Changed title -->
    <link rel="stylesheet" href="styles.css"> <!-- Updated CSS -->
</head>
<body>
    <header>
        <h1>Your Voice Heard</h1> <!-- Logo placed on the left -->
        <nav>
    <?php if (isset($_SESSION['user'])): ?>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</span>
        <?php if ($_SESSION['user']['is_admin'] == 1): ?>
            <a href="create_poll.php">Create Poll</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>       
    </header>

    <main>
    <h2>Current Polls</h2>
    <ul>
    <?php if (count($polls) > 0): ?>
        <?php foreach ($polls as $poll): ?>
            <li>
                <a href="poll.php?id=<?php echo $poll['id']; ?>">
                    <?php echo htmlspecialchars($poll['question']); ?>
                </a>
                (<a href="poll_results.php?id=<?php echo $poll['id']; ?>">View Results</a>)
            </li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No polls available at the moment.</li>
    <?php endif; ?>
</ul>
    </main>

    <footer>
        <p>&copy; 2025 Your Voice Heard or Not</p>
    </footer>
</body>
</html>

