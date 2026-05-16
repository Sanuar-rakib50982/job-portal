<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];
$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? "";
    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

    if ($jobId > 0 && ($action === "close" || $action === "reopen")) {
        $status = $action === "close" ? "closed" : "active";

        if ($recruiter->updateJobStatus($jobId, $recruiterId, $status)) {
            header("Location: jobs.php?message=Job status updated successfully");
            exit;
        } else {
            header("Location: jobs.php?error=Failed to update job status");
            exit;
        }
    }
}

$jobs = $recruiter->getRecruiterJobs($recruiterId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Jobs - Recruiter</title>
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
        <h1>Manage Jobs</h1>
        <p>Create, edit, close, and reopen jobs posted by you.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <a href="job_form.php" class="btn">Post New Job</a>
        <br><br>

        <div class="table-box">
            <h2>My Posted Jobs</h2>

            <table>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Experience</th>
                    <th>Salary</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php if ($jobs->num_rows > 0) { ?>
                    <?php while ($job = $jobs->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                            </td>

                            <td><?php echo htmlspecialchars($job['category_name'] ?? 'N/A'); ?></td>

                            <td><?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></td>

                            <td><?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?></td>

                            <td><?php echo htmlspecialchars($job['experience_level'] ?? 'N/A'); ?></td>

                            <td>
                                <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?>
                                -
                                <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?>
                            </td>

                            <td><?php echo htmlspecialchars($job['deadline'] ?? 'N/A'); ?></td>

                            <td>
                                <?php if ($job['status'] === 'active') { ?>
                                    <span class="badge green">Active</span>
                                <?php } else { ?>
                                    <span class="badge red">Closed</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a class="btn btn-secondary" href="job_form.php?id=<?php echo $job['id']; ?>">Edit</a>

                                <?php if ($job['status'] === 'active') { ?>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Close this job?');">
                                        <input type="hidden" name="action" value="close">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="btn-danger">Close</button>
                                    </form>
                                <?php } else { ?>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Reopen this job?');">
                                        <input type="hidden" name="action" value="reopen">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <button type="submit" class="btn-success">Reopen</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9">No jobs posted yet.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>

</body>
</html>