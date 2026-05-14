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
    <title>Register - Job Portal</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<h1>Register</h1>

<?php if (!empty($error)) { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<?php if (!empty($success)) { ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php } ?>

<form method="POST" action="">
    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Phone</label>
    <input type="text" name="phone">

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Role</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="seeker">Job Seeker</option>
        <option value="employer">Employer</option>
        <option value="recruiter">Recruiter</option>
    </select>

    <button type="submit">Register</button>
</form>

<a href="login.php">Already have an account? Login</a>
<a href="index.php">Back to Home</a>

</body>
</html>