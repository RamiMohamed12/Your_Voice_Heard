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
    <title>Your Voice Heard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Your Voice Heard</h1>
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
                <?php $counter = 1; ?>
                <?php foreach ($polls as $poll): ?>
                    <li>
                        <span class="poll-counter"><?php echo $counter; ?>.</span>
                        <a href="poll.php?id=<?php echo $poll['id']; ?>">
                            <?php echo htmlspecialchars($poll['question']); ?>
                        </a>
                        <?php if (!empty($poll['end_time'])): ?>
                            <?php
                            $endTime = strtotime($poll['end_time']);
                            $currentTime = time();
                            $remainingTime = $endTime - $currentTime;

                            if ($remainingTime > 0) {
                                $hours = floor($remainingTime / 3600);
                                $minutes = floor(($remainingTime % 3600) / 60);
                                $seconds = $remainingTime % 60;
                                echo "<span class='blinking'> (Time remaining: $hours hours, $minutes minutes, $seconds seconds)</span>";
                            } else {
                                echo "<span> (Poll ended)</span>";
                            }
                            ?>
                        <?php endif; ?>
                        (<a href="poll_results.php?id=<?php echo $poll['id']; ?>">View Results</a>)
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin'] == 1): ?>
                            <form action="delete_poll.php" method="POST" style="display:inline;">
                                <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this poll?');">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                    <?php $counter++; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No polls available at the moment.</li>
            <?php endif; ?>
        </ul>
    </main>

    <footer>
        <p>&copy; 2025 Your Voice Heard</p>
    </footer>
</body>
</html>
