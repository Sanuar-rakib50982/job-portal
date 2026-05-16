<?php
require_once "../../helpers/auth.php";
requireRole('employer');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employer Dashboard</title>
    <link rel="stylesheet" href="../../../public/css/employer.css">
</head>
<body>

<div class="employer-wrapper">
    <aside class="sidebar">
        <h2>Employer Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Company Profile</a>
        <a href="jobs.php">Manage Jobs</a>
        <a href="applications.php">Applications</a>
        <a href="messages.php">Messages</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="../../../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Employer Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>

        <div class="card-grid">
            <a class="card" href="profile.php">Complete Company Profile</a>
            <a class="card" href="jobs.php">Post & Manage Jobs</a>
            <a class="card" href="applications.php">Review Applications</a>
            <a class="card" href="messages.php">Messages</a>
            <a class="card" href="complaint.php">Submit Complaint</a>
        </div>
    </main>
</div>

</body>
</html>