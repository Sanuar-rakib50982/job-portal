<?php
require_once "../../helpers/auth.php";
requireRole('employer');

require_once "../../config/database.php";
require_once "../../controllers/EmployerController.php";

$employer = new EmployerController($conn);

$employerId = $_SESSION['user_id'];
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$job = null;

if ($jobId > 0) {
    $job = $employer->getJobById($jobId, $employerId);

    if (!$job) {
        header("Location: jobs.php?error=Job not found or access denied");
        exit;
    }
}

$categories = $employer->getCategories();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "category_id" => (int)($_POST['category_id'] ?? 0),
        "title" => trim($_POST['title'] ?? ""),
        "description" => trim($_POST['description'] ?? ""),
        "requirements" => trim($_POST['requirements'] ?? ""),
        "benefits" => trim($_POST['benefits'] ?? ""),
        "salary_min" => (float)($_POST['salary_min'] ?? 0),
        "salary_max" => (float)($_POST['salary_max'] ?? 0),
        "location" => trim($_POST['location'] ?? ""),
        "job_type" => trim($_POST['job_type'] ?? ""),
        "experience_level" => trim($_POST['experience_level'] ?? ""),
        "deadline" => trim($_POST['deadline'] ?? "")
    ];

    if ($data['category_id'] <= 0) {
        $error = "Please select a category.";
    } elseif (empty($data['title'])) {
        $error = "Job title is required.";
    } elseif (empty($data['description'])) {
        $error = "Job description is required.";
    } elseif (empty($data['location'])) {
        $error = "Job location is required.";
    } elseif (empty($data['job_type'])) {
        $error = "Job type is required.";
    } elseif (empty($data['experience_level'])) {
        $error = "Experience level is required.";
    } elseif (empty($data['deadline'])) {
        $error = "Deadline is required.";
    } elseif ($data['salary_min'] < 0 || $data['salary_max'] < 0) {
        $error = "Salary cannot be negative.";
    } elseif ($data['salary_max'] > 0 && $data['salary_min'] > $data['salary_max']) {
        $error = "Minimum salary cannot be greater than maximum salary.";
    } else {
        if ($jobId > 0) {
            if ($employer->updateJob($jobId, $employerId, $data)) {
                header("Location: jobs.php?message=Job updated successfully");
                exit;
            }

            $error = "Failed to update job.";
        } else {
            if ($employer->createJob($employerId, $data)) {
                header("Location: jobs.php?message=Job posted successfully");
                exit;
            }

            $error = "Failed to post job.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $jobId > 0 ? "Edit Job" : "Post Job"; ?> - Employer</title>
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
        <h1><?php echo $jobId > 0 ? "Edit Job" : "Post New Job"; ?></h1>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $category['id']; ?>"
                            <?php echo (($job['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Job Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($job['title'] ?? ''); ?>" required>

                <label>Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($job['description'] ?? ''); ?></textarea>

                <label>Requirements</label>
                <textarea name="requirements"><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>

                <label>Benefits</label>
                <textarea name="benefits"><?php echo htmlspecialchars($job['benefits'] ?? ''); ?></textarea>

                <label>Minimum Salary</label>
                <input type="number" name="salary_min" min="0" step="0.01" value="<?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?>">

                <label>Maximum Salary</label>
                <input type="number" name="salary_max" min="0" step="0.01" value="<?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?>">

                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>" required>

                <label>Job Type</label>
                <select name="job_type" required>
                    <option value="">Select Type</option>
                    <option value="full-time" <?php echo (($job['job_type'] ?? '') === 'full-time') ? 'selected' : ''; ?>>Full-time</option>
                    <option value="part-time" <?php echo (($job['job_type'] ?? '') === 'part-time') ? 'selected' : ''; ?>>Part-time</option>
                    <option value="remote" <?php echo (($job['job_type'] ?? '') === 'remote') ? 'selected' : ''; ?>>Remote</option>
                    <option value="contract" <?php echo (($job['job_type'] ?? '') === 'contract') ? 'selected' : ''; ?>>Contract</option>
                </select>

                <label>Experience Level</label>
                <select name="experience_level" required>
                    <option value="">Select Level</option>
                    <option value="entry" <?php echo (($job['experience_level'] ?? '') === 'entry') ? 'selected' : ''; ?>>Entry</option>
                    <option value="mid" <?php echo (($job['experience_level'] ?? '') === 'mid') ? 'selected' : ''; ?>>Mid</option>
                    <option value="senior" <?php echo (($job['experience_level'] ?? '') === 'senior') ? 'selected' : ''; ?>>Senior</option>
                </select>

                <label>Deadline</label>
                <input type="date" name="deadline" value="<?php echo htmlspecialchars($job['deadline'] ?? ''); ?>" required>

                <button type="submit"><?php echo $jobId > 0 ? "Update Job" : "Post Job"; ?></button>
                <a href="jobs.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>