<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$seekerId = $_SESSION['user_id'];
$applications = $seeker->getMyApplications($seekerId);

$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Applications - Job Seeker</title>
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
        <h1>My Applications</h1>
        <p>Track your submitted job applications.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="table-box">
            <h2>Application History</h2>

            <table>
                <tr>
                    <th>Job</th>
                    <th>Company</th>
                    <th>Job Info</th>
                    <th>Status</th>
                    <th>Resume</th>
                    <th>Applied At</th>
                    <th>Action</th>
                </tr>

                <?php if ($applications->num_rows > 0) { ?>
                    <?php while ($application = $applications->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($application['title']); ?></strong>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($application['employer_name'] ?? 'N/A'); ?>

                                <?php if (!empty($application['recruiter_name'])) { ?>
                                    <br><small>Recruiter: <?php echo htmlspecialchars($application['recruiter_name']); ?></small>
                                <?php } ?>
                            </td>

                            <td>
                                Location: <?php echo htmlspecialchars($application['location'] ?? 'N/A'); ?><br>
                                Type: <?php echo htmlspecialchars($application['job_type'] ?? 'N/A'); ?><br>
                                Experience: <?php echo htmlspecialchars($application['experience_level'] ?? 'N/A'); ?><br>
                                Salary:
                                <?php echo htmlspecialchars($application['salary_min'] ?? '0'); ?> -
                                <?php echo htmlspecialchars($application['salary_max'] ?? '0'); ?>
                            </td>

                            <td>
                                <?php
                                $status = $application['status'];
                                $class = "gray";

                                if ($status === "submitted") {
                                    $class = "yellow";
                                } elseif ($status === "shortlisted" || $status === "interview") {
                                    $class = "green";
                                } elseif ($status === "rejected" || $status === "withdrawn") {
                                    $class = "red";
                                }
                                ?>

                                <span class="badge <?php echo $class; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>

                            <td>
                                <?php if (!empty($application['resume_path'])) { ?>
                                    <a href="../../../<?php echo htmlspecialchars($application['resume_path']); ?>" target="_blank">View Resume</a>
                                <?php } else { ?>
                                    No resume
                                <?php } ?>
                            </td>

                            <td><?php echo $application['applied_at']; ?></td>

                            <td>
                                <?php if ($application['status'] === 'submitted') { ?>
                                    <form method="POST" action="withdraw_application.php" onsubmit="return confirm('Are you sure you want to withdraw this application?');">
                                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                        <button type="submit" class="btn-danger">Withdraw</button>
                                    </form>
                                <?php } else { ?>
                                    No action
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7">No applications found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>

</body>
</html>