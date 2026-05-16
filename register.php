<?php

require_once "app/config/database.php";
require_once "app/models/User.php";

$error = "";
$success = "";

$name = "";
$email = "";
$phone = "";
$role = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = trim($_POST["role"] ?? "");

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^[0-9]{11}$/', $phone)) {
        $error = "Phone number must be exactly 11 digits and contain only numbers.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!in_array($role, ['seeker', 'employer', 'recruiter'], true)) {
        $error = "Invalid role selected.";
    } else {
        $userModel = new User($conn);

        if ($userModel->emailExists($email)) {
            $error = "Email already exists.";
        } else {
            if ($userModel->register($name, $email, $phone, $password, $role)) {
                $success = "Registration successful. You can now login.";

                $name = "";
                $email = "";
                $phone = "";
                $role = "";
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
        $displayError = $error ?? "";
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
                <label>Full Name</label>
                <input 
                    type="text" 
                    name="name" 
                    placeholder="Enter your full name" 
                    value="<?php echo htmlspecialchars($name); ?>" 
                    required
                >
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Enter your email" 
                    value="<?php echo htmlspecialchars($email); ?>" 
                    required
                >
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input 
                    type="text" 
                    name="phone" 
                    placeholder="Example: 01712345678" 
                    value="<?php echo htmlspecialchars($phone); ?>" 
                    maxlength="11" 
                    pattern="[0-9]{11}" 
                    title="Phone number must be exactly 11 digits and contain only numbers." 
                    required
                >
            </div>

            <div class="form-group">
                <label>Select Role</label>
                <select name="role" required>
                    <option value="">Choose your role</option>
                    <option value="seeker" <?php echo $role === 'seeker' ? 'selected' : ''; ?>>Job Seeker</option>
                    <option value="employer" <?php echo $role === 'employer' ? 'selected' : ''; ?>>Employer</option>
                    <option value="recruiter" <?php echo $role === 'recruiter' ? 'selected' : ''; ?>>Recruiter</option>
                </select>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Create a password" 
                    required
                >
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