<?php

session_start();

require_once "app/config/database.php";
require_once "app/models/User.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        $userModel = new User($conn);
        $user = $userModel->login($email, $password);

        if ($user) {
            if ($user['is_active'] == 0) {
                $error = "Your account has been suspended.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'seeker') {
                    header("Location: app/views/seeker/dashboard.php");
                } elseif ($user['role'] === 'employer') {
                    header("Location: app/views/employer/dashboard.php");
                } elseif ($user['role'] === 'recruiter') {
                    header("Location: app/views/recruiter/dashboard.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: app/views/admin/dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - CareerBridge</title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body class="auth-page">

<nav class="navbar">
    <a href="index.php" class="brand">
        <span class="brand-mark">CB</span>
        <span>CareerBridge</span>
    </a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="register.php" class="nav-btn">Register</a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1>Welcome Back</h1>
        <p>Login to access your CareerBridge dashboard.</p>

        <?php
        $displayError = $error ?? $loginError ?? "";
        $displaySuccess = $success ?? "";
        ?>

        <?php if (!empty($displayError)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($displayError); ?></div>
        <?php } ?>

        <?php if (!empty($displaySuccess)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($displaySuccess); ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="auth-submit">Login</button>
        </form>

        <div class="auth-footer">
            New to CareerBridge? <a href="register.php">Create an account</a>
        </div>
    </div>
</div>

<footer class="footer">
    © <?php echo date('Y'); ?> CareerBridge Job Portal.
</footer>

</body>
</html>