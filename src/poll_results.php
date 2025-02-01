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
    // Fetch choices and their vote counts
    $stmt = $pdo->prepare("
        SELECT c.id, c.choice_text, COUNT(v.id) AS vote_count
        FROM choices c
        LEFT JOIN votes v ON c.id = v.choice_id
        WHERE c.poll_id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$pollId]);
    $choices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total votes
    $totalVotes = array_sum(array_column($choices, 'vote_count'));

    // Fetch detailed voter information for admins
    $voterDetails = [];
    if ($_SESSION['user']['is_admin'] == 1) {
        $stmt = $pdo->prepare("
            SELECT u.username, c.choice_text
            FROM votes v
            JOIN users u ON v.user_id = u.id
            JOIN choices c ON v.choice_id = c.id
            WHERE c.poll_id = ?
        ");
        $stmt->execute([$pollId]);
        $voterDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll Results - Polling Website</title>
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
            <h2>Poll Results: <?php echo htmlspecialchars($poll['question']); ?></h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <h3>Vote Distribution</h3>
                <ul>
                    <?php foreach ($choices as $choice): ?>
                        <li>
                            <?php echo htmlspecialchars($choice['choice_text']); ?>:
                            <?php if ($totalVotes > 0): ?>
                                <?php echo round(($choice['vote_count'] / $totalVotes) * 100, 2); ?>%
                            <?php else: ?>
                                0%
                            <?php endif; ?>
                            (<?php echo $choice['vote_count']; ?> votes)
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($_SESSION['user']['is_admin'] == 1): ?>
                    <h3>Advanced Statistics</h3>
                    <p>Total Votes: <?php echo $totalVotes; ?></p>
                    <h4>Voter Details</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Voted For</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voterDetails as $detail): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detail['username']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['choice_text']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
            <a href="index.php">Back to Polls</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Polling Website</p>
    </footer>
</body>
</html>
