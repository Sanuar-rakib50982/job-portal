<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$seekerId = $_SESSION['user_id'];
$savedJobs = $seeker->getSavedJobs($seekerId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Saved Jobs - Job Seeker</title>
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
        <h1>Saved Jobs</h1>
        <p>Jobs you saved for later review.</p>

        <div class="table-box">
            <h2>My Saved Jobs</h2>

            <?php if ($savedJobs->num_rows > 0) { ?>
                <?php while ($job = $savedJobs->fetch_assoc()) { ?>
                    <div class="job-card">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>

                        <?php if ($job['is_featured']) { ?>
                            <span class="badge yellow">Featured</span>
                            <br><br>
                        <?php } ?>

                        <p>
                            <strong>Category:</strong> <?php echo htmlspecialchars($job['category_name'] ?? 'N/A'); ?><br>
                            <strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name'] ?? 'N/A'); ?><br>
                            <strong>Recruiter:</strong> <?php echo htmlspecialchars($job['recruiter_name'] ?? 'N/A'); ?><br>
                            <strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?><br>
                            <strong>Type:</strong> <?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?><br>
                            <strong>Experience:</strong> <?php echo htmlspecialchars($job['experience_level'] ?? 'N/A'); ?><br>
                            <strong>Salary:</strong>
                            <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?> -
                            <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?><br>
                            <strong>Deadline:</strong> <?php echo htmlspecialchars($job['deadline'] ?? 'N/A'); ?><br>
                            <strong>Saved At:</strong> <?php echo htmlspecialchars($job['saved_at']); ?>
                        </p>

                        <a class="btn" href="job_details.php?id=<?php echo $job['job_id']; ?>">View Details</a>

                        <button 
                            type="button" 
                            class="btn btn-danger save-job-btn" 
                            data-job-id="<?php echo $job['job_id']; ?>">
                            Unsave Job
                        </button>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No saved jobs found.</p>
                <a href="jobs.php" class="btn">Browse Jobs</a>
            <?php } ?>
        </div>
    </main>
</div>

<script src="../../../public/js/seeker.js"></script>
</body>
</html>