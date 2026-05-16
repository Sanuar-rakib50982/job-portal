<?php
require_once "../../helpers/auth.php";
requireRole('employer');

require_once "../../config/database.php";
require_once "../../controllers/EmployerController.php";

$employer = new EmployerController($conn);

$seekerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($seekerId <= 0) {
    header("Location: applications.php");
    exit;
}

$seeker = $employer->getSeekerProfileById($seekerId);

if (!$seeker) {
    header("Location: applications.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seeker Profile - Employer</title>
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
        <h1>Seeker Profile</h1>

        <div class="form-box">
            <?php if (!empty($seeker['profile_pic'])) { ?>
                <img src="../../../<?php echo htmlspecialchars($seeker['profile_pic']); ?>" width="120" style="border-radius: 12px; margin-bottom: 15px;">
            <?php } ?>

            <h2><?php echo htmlspecialchars($seeker['name']); ?></h2>

            <p>
                <strong>Email:</strong> <?php echo htmlspecialchars($seeker['email']); ?><br>
                <strong>Phone:</strong> <?php echo htmlspecialchars($seeker['phone'] ?? 'N/A'); ?><br>
                <strong>Headline:</strong> <?php echo htmlspecialchars($seeker['headline'] ?? 'No headline'); ?><br>
                <strong>Years of Experience:</strong> <?php echo htmlspecialchars($seeker['years_experience'] ?? '0'); ?><br>
                <strong>Education:</strong> <?php echo htmlspecialchars($seeker['education_level'] ?? 'N/A'); ?><br>
                <strong>Preferred Location:</strong> <?php echo htmlspecialchars($seeker['preferred_location'] ?? 'N/A'); ?><br>
                <strong>Current Salary:</strong> <?php echo htmlspecialchars($seeker['current_salary'] ?? '0'); ?><br>
                <strong>Expected Salary:</strong> <?php echo htmlspecialchars($seeker['expected_salary'] ?? '0'); ?>
            </p>
        </div>

        <div class="form-box">
            <h2>Summary</h2>
            <p><?php echo nl2br(htmlspecialchars($seeker['summary'] ?? 'No summary available.')); ?></p>
        </div>

        <div class="form-box">
            <h2>Skills</h2>
            <p><?php echo nl2br(htmlspecialchars($seeker['skills'] ?? 'No skills added.')); ?></p>
        </div>

        <div class="form-box">
            <h2>Resume</h2>

            <?php if (!empty($seeker['resume_path'])) { ?>
                <a class="btn" href="../../../<?php echo htmlspecialchars($seeker['resume_path']); ?>" target="_blank">View Resume</a>
            <?php } else { ?>
                <p>No resume uploaded.</p>
            <?php } ?>
        </div>

        <a class="btn btn-secondary" href="applications.php">Back to Applications</a>
    </main>
</div>

</body>
</html>