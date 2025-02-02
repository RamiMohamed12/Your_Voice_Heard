<?php
session_start();
require 'db.php';
require 'auth.php';

// Redirect non-admin users
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question']);
    $choices = array_filter(array_map('trim', $_POST['choices'])); // Remove empty choices
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0; // Duration in hours (optional)

    if (!empty($question) && count($choices) >= 2) {
        try {
            // Calculate end time if duration is provided
            $endTime = null;
            if ($duration > 0) {
                $endTime = date('Y-m-d H:i:s', strtotime("+$duration hours"));
            }

            // Insert the poll question with optional end time
            $stmt = $pdo->prepare("INSERT INTO polls (question, end_time) VALUES (?, ?)");
            $stmt->execute([$question, $endTime]);
            $pollId = $pdo->lastInsertId();

            // Insert the choices
            $stmt = $pdo->prepare("INSERT INTO choices (poll_id, choice_text) VALUES (?, ?)");
            foreach ($choices as $choice) {
                $stmt->execute([$pollId, $choice]);
            }

            $_SESSION['success'] = "Poll created successfully!";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "An error occurred while creating the poll.";
        }
    } else {
        $error = "Please provide a question and at least two choices.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll - Polling Website</title>
    <link rel="stylesheet" href="styles.css">
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
            <h2>Create a New Poll</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Poll Question:</label>
                    <input type="text" name="question" required>
                </div>
                <div class="form-group">
                    <label>Choices (at least 2):</label>
                    <input type="text" name="choices[]" required>
                    <input type="text" name="choices[]" required>
                    <input type="text" name="choices[]">
                    <input type="text" name="choices[]">
                    <input type="text" name="choices[]">
                </div>
                <div class="form-group">
                    <label>Poll Duration (in hours, optional):</label>
                    <input type="number" name="duration" min="0" placeholder="Leave blank for no time limit">
                </div>
                <button type="submit">Create Poll</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Polling Website</p>
    </footer>
</body>
</html>
