<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($jobId <= 0) {
    header("Location: jobs.php");
    exit;
}

$job = $seeker->getJobById($jobId);
$seekerId = $_SESSION['user_id'];
$isSaved = $seeker->isJobSaved($jobId, $seekerId);

if (!$job) {
    header("Location: jobs.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Details - Job Seeker</title>
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
        <h1><?php echo htmlspecialchars($job['title']); ?></h1>
        <p>Review the full job details before applying.</p>

        <div class="form-box">
            <?php if ($job['is_featured']) { ?>
                <span class="badge yellow">Featured Job</span>
                <br><br>
            <?php } ?>

            <p><strong>Category:</strong> <?php echo htmlspecialchars($job['category_name'] ?? 'N/A'); ?></p>
            <p><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name'] ?? 'N/A'); ?></p>
            <p><strong>Recruiter:</strong> <?php echo htmlspecialchars($job['recruiter_name'] ?? 'N/A'); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></p>
            <p><strong>Job Type:</strong> <?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?></p>
            <p><strong>Experience Level:</strong> <?php echo htmlspecialchars($job['experience_level'] ?? 'N/A'); ?></p>
            <p><strong>Salary Range:</strong> 
                <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?> -
                <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?>
            </p>
            <p><strong>Deadline:</strong> <?php echo htmlspecialchars($job['deadline'] ?? 'N/A'); ?></p>
        </div>

        <div class="form-box">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
        </div>

        <div class="form-box">
            <h2>Requirements</h2>
            <p><?php echo nl2br(htmlspecialchars($job['requirements'] ?? 'Not specified')); ?></p>
        </div>

        <div class="form-box">
            <h2>Benefits</h2>
            <p><?php echo nl2br(htmlspecialchars($job['benefits'] ?? 'Not specified')); ?></p>
        </div>

        <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn">Apply Now</a>

<button 
    type="button" 
    class="btn btn-secondary save-job-btn" 
    data-job-id="<?php echo $job['id']; ?>">
    <?php echo $isSaved ? "Unsave Job" : "Save Job"; ?>
</button>

<a href="jobs.php" class="btn btn-secondary">Back to Jobs</a>
    </main>
</div>
<script src="../../../public/js/seeker.js"></script>
</body>
</html>