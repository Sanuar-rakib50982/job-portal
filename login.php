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
    <title>Login - Job Portal</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<h1>Login</h1>

<?php if (!empty($error)) { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST" action="">
    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>

<a href="register.php">Create new account</a>
<a href="index.php">Back to Home</a>

</body>
</html>