<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$seekerId = $_SESSION['user_id'];
$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

if ($jobId <= 0) {
    header("Location: jobs.php");
    exit;
}

$job = $seeker->getJobById($jobId);

if (!$job) {
    header("Location: jobs.php");
    exit;
}

$profileResume = $seeker->getProfileResume($seekerId);
$alreadyApplied = $seeker->hasAlreadyApplied($jobId, $seekerId);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($alreadyApplied) {
        $error = "You have already applied to this job.";
    } else {
        $coverLetter = trim($_POST['cover_letter'] ?? "");
        $resumeOption = $_POST['resume_option'] ?? "profile";
        $resumePath = null;

        if (empty($coverLetter)) {
            $error = "Cover letter is required.";
        }

        if (empty($error)) {
            if ($resumeOption === "profile") {
                if (empty($profileResume)) {
                    $error = "No profile resume found. Please upload a new resume.";
                } else {
                    $resumePath = $profileResume;
                }
            }

            if ($resumeOption === "new") {
                if (empty($_FILES['resume']['name'])) {
                    $error = "Please upload a resume.";
                } else {
                    $resumeName = $_FILES['resume']['name'];
                    $resumeTmp = $_FILES['resume']['tmp_name'];
                    $resumeSize = $_FILES['resume']['size'];
                    $resumeExt = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));

                    if ($resumeExt !== "pdf") {
                        $error = "Resume must be a PDF file.";
                    } elseif ($resumeSize > 5 * 1024 * 1024) {
                        $error = "Resume size must not exceed 5 MB.";
                    } else {
                        $newResumeName = "application_resume_" . $seekerId . "_" . time() . ".pdf";
                        $uploadDir = __DIR__ . "/../../../public/uploads/resumes/";
                        $targetPath = $uploadDir . $newResumeName;

                        if (move_uploaded_file($resumeTmp, $targetPath)) {
                            $resumePath = "public/uploads/resumes/" . $newResumeName;
                        } else {
                            $error = "Failed to upload resume.";
                        }
                    }
                }
            }
        }

        if (empty($error)) {
            $recruiterId = $job['recruiter_id'] ?? null;

            if ($seeker->applyToJob($jobId, $seekerId, $recruiterId, $coverLetter, $resumePath)) {
                $message = "Application submitted successfully.";
                $alreadyApplied = true;
            } else {
                $error = "Failed to submit application. You may have already applied.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply to Job - Job Seeker</title>
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
        <h1>Apply to Job</h1>
        <p>Submit your application for this job.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2><?php echo htmlspecialchars($job['title']); ?></h2>

            <p><strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name'] ?? 'N/A'); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></p>
            <p><strong>Salary:</strong> 
                <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?> -
                <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?>
            </p>
        </div>

        <?php if ($alreadyApplied) { ?>
            <div class="alert-error">
                You have already applied to this job.
            </div>

            <a href="applications.php" class="btn">View My Applications</a>
            <a href="jobs.php" class="btn btn-secondary">Back to Jobs</a>
        <?php } else { ?>
            <div class="form-box">
                <form method="POST" action="" enctype="multipart/form-data">
                    <label>Cover Letter</label>
                    <textarea name="cover_letter" placeholder="Write a short cover letter explaining why you are suitable for this job." required></textarea>

                    <label>Resume Option</label>
                    <select name="resume_option" id="resume_option">
                        <option value="profile">Use my profile resume</option>
                        <option value="new">Upload new resume</option>
                    </select>

                    <?php if (!empty($profileResume)) { ?>
                        <p>
                            Current Profile Resume:
                            <a href="../../../<?php echo htmlspecialchars($profileResume); ?>" target="_blank">View Resume</a>
                        </p>
                    <?php } else { ?>
                        <p class="alert-error">No profile resume found. Please upload a new resume.</p>
                    <?php } ?>

                    <label>Upload New Resume PDF, Max 5 MB</label>
                    <input type="file" name="resume" accept="application/pdf">

                    <button type="submit">Submit Application</button>
                    <a href="job_details.php?id=<?php echo $jobId; ?>" class="btn btn-secondary">Back</a>
                </form>
            </div>
        <?php } ?>
    </main>
</div>

</body>
</html>