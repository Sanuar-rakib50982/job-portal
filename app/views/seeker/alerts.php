<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$seekerId = $_SESSION['user_id'];
$categories = $seeker->getCategories();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "create") {
        $keyword = trim($_POST['keyword'] ?? "");
        $categoryId = trim($_POST['category_id'] ?? "");
        $location = trim($_POST['location'] ?? "");
        $jobType = trim($_POST['job_type'] ?? "");

        if (empty($keyword) && empty($categoryId) && empty($location) && empty($jobType)) {
            $error = "Please provide at least one alert condition.";
        } else {
            if ($seeker->createJobAlert($seekerId, $keyword, $categoryId, $location, $jobType)) {
                $message = "Job alert created successfully.";
            } else {
                $error = "Failed to create job alert.";
            }
        }
    }

    if ($action === "delete") {
        $alertId = isset($_POST['alert_id']) ? (int)$_POST['alert_id'] : 0;

        if ($alertId > 0 && $seeker->deleteJobAlert($alertId, $seekerId)) {
            $message = "Job alert deleted successfully.";
        } else {
            $error = "Failed to delete job alert.";
        }
    }
}

$alerts = $seeker->getJobAlerts($seekerId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Alerts - Job Seeker</title>
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
        <h1>Job Alerts</h1>
        <p>Create alerts based on your preferred job criteria.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>Create New Alert</h2>

            <form method="POST" action="">
                <input type="hidden" name="action" value="create">

                <label>Keyword</label>
                <input type="text" name="keyword" placeholder="Example: PHP Developer">

                <label>Category</label>
                <select name="category_id">
                    <option value="">Any Category</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Location</label>
                <input type="text" name="location" placeholder="Example: Dhaka">

                <label>Job Type</label>
                <select name="job_type">
                    <option value="">Any Type</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="remote">Remote</option>
                    <option value="contract">Contract</option>
                </select>

                <button type="submit">Create Alert</button>
            </form>
        </div>

        <div class="table-box">
            <h2>My Alerts</h2>

            <?php if ($alerts->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Keyword</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Job Type</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($alert = $alerts->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alert['keyword'] ?: 'Any'); ?></td>
                            <td><?php echo htmlspecialchars($alert['category_name'] ?: 'Any'); ?></td>
                            <td><?php echo htmlspecialchars($alert['location'] ?: 'Any'); ?></td>
                            <td><?php echo htmlspecialchars($alert['job_type'] ?: 'Any'); ?></td>
                            <td><?php echo htmlspecialchars($alert['created_at']); ?></td>
                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Delete this alert?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No job alerts created yet.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>