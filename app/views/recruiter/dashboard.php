<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Dashboard</title>
    <link rel="stylesheet" href="../../../public/css/recruiter.css">
</head>
<body>

<div class="recruiter-wrapper">
    <aside class="sidebar">
        <h2>Recruiter Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="clients.php">Client Companies</a>
        <a href="jobs.php">Manage Jobs</a>
        <a href="applications.php">Applications</a>
        <a href="seekers.php">Search Seekers</a>
        <a href="outreach.php">Outreach</a>
        <a href="messages.php">Messages</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="../../../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Recruiter Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>

        <div class="card-grid">
            <a class="card" href="profile.php">Complete Profile</a>
            <a class="card" href="clients.php">Manage Clients</a>
            <a class="card" href="jobs.php">Post & Manage Jobs</a>
            <a class="card" href="applications.php">Review Applications</a>
            <a class="card" href="seekers.php">Search Seekers</a>
            <a class="card" href="messages.php">Messages</a>
        </div>
    </main>
</div>

</body>
</html>