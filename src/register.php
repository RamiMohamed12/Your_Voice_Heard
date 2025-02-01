<?php
session_start();
require 'db.php';
require 'auth.php';

if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            try {
                $success = registerUser($pdo, $username, $password);
                if ($success) {
                    $_SESSION['success'] = "Registration successful! Please login.";
                    header("Location: login.php");
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Username already exists!";
            }
        } else {
            $error = "Passwords do not match!";
        }
    } else {
        $error = "Please fill in all fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Polling Website</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Polling Website</h1>
        <nav>
            <a href="index.php">Home</a>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Polling Website</p>
    </footer>
</body>
</html>
