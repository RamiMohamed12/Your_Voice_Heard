<?php
session_start();
require 'db.php';
require 'auth.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pollId = $_GET['id'] ?? 0;
$error = '';

// Fetch the poll and its choices
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$pollId]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$poll) {
    $error = "Poll not found.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM choices WHERE poll_id = ?");
    $stmt->execute([$pollId]);
    $choices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate remaining time if the poll has an end time
    if ($poll['end_time']) {
        $endTime = strtotime($poll['end_time']);
        $currentTime = time();
        $remainingTime = $endTime - $currentTime;

        if ($remainingTime <= 0) {
            $error = "This poll has ended. Voting is no longer allowed.";
        }
    }
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choiceId = $_POST['choice'] ?? 0;

    if ($choiceId) {
        try {
            // Check if the user has already voted
            $stmt = $pdo->prepare("SELECT * FROM votes WHERE user_id = ? AND choice_id IN (SELECT id FROM choices WHERE poll_id = ?)");
            $stmt->execute([$_SESSION['user']['id'], $pollId]);

            if ($stmt->fetch()) {
                $error = "You have already voted in this poll.";
            } else {
                // Insert the vote
                $stmt = $pdo->prepare("INSERT INTO votes (user_id, choice_id) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user']['id'], $choiceId]);
                $_SESSION['success'] = "Your vote has been recorded!";
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "An error occurred while recording your vote.";
        }
    } else {
        $error = "Please select a choice.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Polling Website</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add blinking animation for remaining time */
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }

        .blinking {
            animation: blink 1s infinite;
            color: red; /* Optional: Change color to make it more noticeable */
        }
    </style>
</head>
<body>
    <header>
        <h1>Polling Website</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2><?php echo htmlspecialchars($poll['question']); ?></h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Display remaining time if the poll has an end time -->
            <?php if ($poll['end_time']): ?>
                <?php
                $endTime = strtotime($poll['end_time']);
                $currentTime = time();
                $remainingTime = $endTime - $currentTime;

                if ($remainingTime > 0) {
                    $hours = floor($remainingTime / 3600);
                    $minutes = floor(($remainingTime % 3600) / 60);
                    $seconds = $remainingTime % 60;
                    echo "<p class='blinking'>Time remaining: $hours hours, $minutes minutes, $seconds seconds</p>";
                } else {
                    echo "<p>This poll has ended.</p>";
                }
                ?>
            <?php endif; ?>

            <!-- Voting form (only show if the poll is still active) -->
            <?php if (!isset($error) || $error !== "This poll has ended. Voting is no longer allowed."): ?>
                <form method="POST">
                    <?php foreach ($choices as $choice): ?>
                        <div class="form-group">
                            <label>
                                <input type="radio" name="choice" value="<?php echo $choice['id']; ?>" required>
                                <?php echo htmlspecialchars($choice['choice_text']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">Vote</button>
                </form>
            <?php endif; ?>

            <p><a href="poll_results.php?id=<?php echo $pollId; ?>">View Results</a></p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Polling Website</p>
    </footer>
</body>
</html>
