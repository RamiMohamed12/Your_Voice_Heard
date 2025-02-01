<?php
session_start();
require 'db.php';
require 'auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pollId = $_POST['poll_id'] ?? 0;

    if ($pollId) {
        try {
            // Delete the poll and its associated choices and votes
            $pdo->beginTransaction();

            // Delete votes
            $stmt = $pdo->prepare("DELETE FROM votes WHERE choice_id IN (SELECT id FROM choices WHERE poll_id = ?)");
            $stmt->execute([$pollId]);

            // Delete choices
            $stmt = $pdo->prepare("DELETE FROM choices WHERE poll_id = ?");
            $stmt->execute([$pollId]);

            // Delete the poll
            $stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
            $stmt->execute([$pollId]);

            $pdo->commit();
            $_SESSION['success'] = "Poll deleted successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "An error occurred while deleting the poll.";
        }
    }
}

header("Location: index.php");
exit;
?>
