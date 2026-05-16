<?php
require_once "../../helpers/auth.php";
requireRole('seeker');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Seeker Dashboard</title>
    <link rel="stylesheet" href="../../../public/css/seeker.css">
</head>
<body>

<div class="seeker-wrapper">
    <aside class="sidebar">
        <h2>Job Seeker</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="jobs.php">Browse Jobs</a>
        <a href="applications.php">My Applications</a>
        <a href="saved_jobs.php">Saved Jobs</a>
        <a href="alerts.php">Job Alerts</a>
        <a href="outreach.php">Recruiter Outreach</a>
        <a href="messages.php">Messages</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="../../../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Job Seeker Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>

        <div class="card-grid">
            <a class="card" href="profile.php">Complete Profile</a>
            <a class="card" href="jobs.php">Browse Jobs</a>
            <a class="card" href="applications.php">Track Applications</a>
            <a class="card" href="saved_jobs.php">Saved Jobs</a>
            <a class="card" href="alerts.php">Job Alerts</a>
            <a class="card" href="complaint.php">Submit Complaint</a>
        </div>
    </main>
</div>

</body>
</html>