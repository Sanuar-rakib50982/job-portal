<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $maxJobs = trim($_POST['max_jobs_per_employer'] ?? "");
    $maxApplications = trim($_POST['max_active_applications_per_seeker'] ?? "");
    $resumeVisibility = trim($_POST['resume_visibility_default'] ?? "");

    if (!is_numeric($maxJobs) || $maxJobs <= 0) {
        $error = "Maximum job postings must be a positive number.";
    } elseif (!is_numeric($maxApplications) || $maxApplications <= 0) {
        $error = "Maximum active applications must be a positive number.";
    } elseif (!in_array($resumeVisibility, ['public', 'private'])) {
        $error = "Resume visibility must be public or private.";
    } else {
        $admin->updatePlatformSetting('max_jobs_per_employer', $maxJobs);
        $admin->updatePlatformSetting('max_active_applications_per_seeker', $maxApplications);
        $admin->updatePlatformSetting('resume_visibility_default', $resumeVisibility);

        $message = "Platform policies updated successfully.";
    }
}

$settings = $admin->getPlatformSettings();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Platform Policies - Admin Panel</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <link rel="stylesheet" href="../../../public/css/admin.css">

    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; color: #1f2937; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 230px; background: #111827; color: white; padding: 20px; }
        .sidebar h2 { margin-bottom: 25px; }
        .sidebar a { display: block; color: #d1d5db; text-decoration: none; padding: 10px; margin-bottom: 8px; border-radius: 6px; }
        .sidebar a:hover { background: #374151; color: white; }
        .main-content { flex: 1; padding: 25px; }
        .form-box { background: white; padding: 20px; border-radius: 10px; max-width: 650px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        input, select { width: 100%; padding: 10px; margin-top: 6px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 6px; }
        .btn { padding: 9px 14px; border: none; border-radius: 6px; cursor: pointer; background: #2563eb; color: white; }
        .alert-success { padding: 12px; background: #dcfce7; color: #166534; border-radius: 6px; margin-bottom: 15px; }
        .alert-error { padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="categories.php">Categories</a>
        <a href="jobs.php">Jobs</a>
        <a href="complaints.php">Complaints</a>
        <a href="policies.php">Policies</a>
        <a href="announcements.php">Announcements</a>
        <a href="analytics.php">Analytics</a>
        <a href="../../../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Platform Policies</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="">
                <label>Maximum Job Postings Per Employer</label>
                <input type="number" name="max_jobs_per_employer" value="<?php echo htmlspecialchars($settings['max_jobs_per_employer'] ?? '20'); ?>" required>

                <label>Maximum Active Applications Per Seeker</label>
                <input type="number" name="max_active_applications_per_seeker" value="<?php echo htmlspecialchars($settings['max_active_applications_per_seeker'] ?? '50'); ?>" required>

                <label>Resume Visibility Default</label>
                <select name="resume_visibility_default" required>
                    <option value="private" <?php if (($settings['resume_visibility_default'] ?? '') === 'private') echo 'selected'; ?>>Private</option>
                    <option value="public" <?php if (($settings['resume_visibility_default'] ?? '') === 'public') echo 'selected'; ?>>Public</option>
                </select>

                <button type="submit" class="btn">Update Policies</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>