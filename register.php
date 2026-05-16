<?php

require_once "app/config/database.php";
require_once "app/models/User.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!in_array($role, ['seeker', 'employer', 'recruiter'])) {
        $error = "Invalid role selected.";
    } else {
        $userModel = new User($conn);

        if ($userModel->emailExists($email)) {
            $error = "Email already exists.";
        } else {
            if ($userModel->register($name, $email, $phone, $password, $role)) {
                $success = "Registration successful. You can now login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - CareerBridge</title>
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
        <a href="login.php" class="nav-btn">Login</a>
    </div>
</nav>

<div class="auth-wrapper">
    <div class="auth-card large">
        <h1>Create Account</h1>
        <p>Join CareerBridge as a job seeker, employer, or recruiter.</p>

        <?php
        $displayError = $error ?? $registerError ?? "";
        $displaySuccess = $success ?? $message ?? "";
        ?>

        <?php if (!empty($displayError)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($displayError); ?></div>
        <?php } ?>

        <?php if (!empty($displaySuccess)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($displaySuccess); ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="Enter your phone number">
            </div>

            <div class="form-group">
                <label>Select Role</label>
                <select name="role" required>
                    <option value="">Choose your role</option>
                    <option value="seeker">Job Seeker</option>
                    <option value="employer">Employer</option>
                    <option value="recruiter">Recruiter</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
            </div>

            <button type="submit" class="auth-submit">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</div>

<footer class="footer">
    © <?php echo date('Y'); ?> CareerBridge Job Portal.
</footer>

</body>
</html>