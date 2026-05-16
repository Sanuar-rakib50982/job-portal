<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];

$selectedSeekerId = isset($_GET['seeker_id']) ? (int)$_GET['seeker_id'] : 0;

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $seekerId = isset($_POST['seeker_id']) ? (int)$_POST['seeker_id'] : 0;
    $jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $outreachMessage = trim($_POST['message'] ?? "");

    if ($seekerId <= 0) {
        $error = "Please select a seeker.";
    } elseif (empty($outreachMessage)) {
        $error = "Outreach message is required.";
    } elseif (strlen($outreachMessage) < 10) {
        $error = "Outreach message must be at least 10 characters.";
    } else {
        if ($recruiter->sendOutreach($recruiterId, $seekerId, $jobId, $outreachMessage)) {
            $message = "Outreach message sent successfully.";
        } else {
            $error = "Failed to send outreach message.";
        }
    }
}

$seekers = $recruiter->getSeekersForOutreach();
$activeJobs = $recruiter->getRecruiterActiveJobs($recruiterId);
$outreachList = $recruiter->getOutreachList($recruiterId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Outreach</title>
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
        <h1>Recruiter Outreach</h1>
        <p>Send job opportunities or professional outreach messages to seekers.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>Send New Outreach</h2>

            <form method="POST" action="">
                <label>Select Seeker</label>
                <select name="seeker_id" required>
                    <option value="">Select Seeker</option>

                    <?php while ($seeker = $seekers->fetch_assoc()) { ?>
                        <option value="<?php echo $seeker['id']; ?>"
                            <?php echo $selectedSeekerId === (int)$seeker['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($seeker['name']); ?>
                            —
                            <?php echo htmlspecialchars($seeker['email']); ?>
                            <?php if (!empty($seeker['headline'])) { ?>
                                —
                                <?php echo htmlspecialchars($seeker['headline']); ?>
                            <?php } ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Related Job</label>
                <select name="job_id">
                    <option value="">General Outreach</option>

                    <?php while ($job = $activeJobs->fetch_assoc()) { ?>
                        <option value="<?php echo $job['id']; ?>">
                            <?php echo htmlspecialchars($job['title']); ?>
                            —
                            <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?>
                            —
                            <?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Message</label>
                <textarea name="message" placeholder="Write your outreach message clearly..." required></textarea>

                <button type="submit">Send Outreach</button>
            </form>
        </div>

        <div class="table-box">
            <h2>Outreach History</h2>

            <?php if ($outreachList->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Seeker</th>
                        <th>Related Job</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Sent At</th>
                    </tr>

                    <?php while ($outreach = $outreachList->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($outreach['seeker_name']); ?></strong><br>
                                <?php echo htmlspecialchars($outreach['seeker_email']); ?>
                            </td>

                            <td>
                                <?php if (!empty($outreach['job_title'])) { ?>
                                    <strong><?php echo htmlspecialchars($outreach['job_title']); ?></strong><br>
                                    <?php echo htmlspecialchars($outreach['job_location'] ?? 'N/A'); ?>
                                <?php } else { ?>
                                    General Outreach
                                <?php } ?>
                            </td>

                            <td><?php echo nl2br(htmlspecialchars($outreach['message'])); ?></td>

                            <td>
                                <?php
                                $badgeClass = "gray";

                                if ($outreach['status'] === "read") {
                                    $badgeClass = "yellow";
                                }

                                if ($outreach['status'] === "responded") {
                                    $badgeClass = "green";
                                }
                                ?>

                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($outreach['status']); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($outreach['sent_at']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No outreach messages sent yet.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>
