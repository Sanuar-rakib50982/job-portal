<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$seekerId = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $outreachId = isset($_POST['outreach_id']) ? (int)$_POST['outreach_id'] : 0;
    $status = $_POST['status'] ?? "";

    if ($outreachId > 0 && $seeker->updateOutreachStatus($outreachId, $seekerId, $status)) {
        $message = "Outreach status updated successfully.";
    } else {
        $error = "Failed to update outreach status.";
    }
}

$outreachList = $seeker->getRecruiterOutreach($seekerId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Outreach - Job Seeker</title>
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
        <h1>Recruiter Outreach</h1>
        <p>View job opportunities sent by recruiters.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="table-box">
            <h2>Outreach Messages</h2>

            <?php if ($outreachList->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Recruiter</th>
                        <th>Job</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Sent At</th>
                        <th>Action</th>
                    </tr>

                    <?php while ($item = $outreachList->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['recruiter_name']); ?></strong><br>
                                <?php echo htmlspecialchars($item['recruiter_email']); ?>
                            </td>

                            <td>
                                <?php if (!empty($item['job_id'])) { ?>
                                    <a href="job_details.php?id=<?php echo $item['job_id']; ?>">
                                        <?php echo htmlspecialchars($item['job_title']); ?>
                                    </a>
                                <?php } else { ?>
                                    General outreach
                                <?php } ?>
                            </td>

                            <td><?php echo nl2br(htmlspecialchars($item['message'])); ?></td>

                            <td>
                                <?php
                                $badgeClass = "gray";

                                if ($item['status'] === "read") {
                                    $badgeClass = "yellow";
                                }

                                if ($item['status'] === "responded") {
                                    $badgeClass = "green";
                                }
                                ?>

                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($item['status']); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($item['sent_at']); ?></td>

                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="outreach_id" value="<?php echo $item['id']; ?>">

                                    <select name="status">
                                        <option value="read">Mark as Read</option>
                                        <option value="responded">Mark as Responded</option>
                                    </select>

                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No recruiter outreach found.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>