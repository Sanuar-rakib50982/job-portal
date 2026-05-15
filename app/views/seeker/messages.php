<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$userId = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $senderId = isset($_POST['sender_id']) ? (int)$_POST['sender_id'] : 0;
    $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
    $applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    $replyBody = trim($_POST['body'] ?? "");

    if ($messageId > 0) {
        $seeker->markMessageAsRead($messageId, $userId);
    }

    if ($senderId <= 0 || empty($replyBody)) {
        $error = "Reply message is required.";
    } else {
        if ($seeker->sendMessage($userId, $senderId, $applicationId, $replyBody)) {
            $message = "Reply sent successfully.";
        } else {
            $error = "Failed to send reply.";
        }
    }
}

$messages = $seeker->getReceivedMessages($userId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages - Job Seeker</title>
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
        <h1>Messages</h1>
        <p>View and reply to messages from employers or recruiters.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="table-box">
            <h2>Received Messages</h2>

            <?php if ($messages->num_rows > 0) { ?>
                <?php while ($msg = $messages->fetch_assoc()) { ?>
                    <div class="job-card">
                        <h3>
                            Message from <?php echo htmlspecialchars($msg['sender_name']); ?>
                        </h3>

                        <p>
                            <strong>From:</strong>
                            <?php echo htmlspecialchars($msg['sender_name']); ?>
                            (<?php echo htmlspecialchars($msg['sender_role']); ?>)<br>

                            <strong>Email:</strong>
                            <?php echo htmlspecialchars($msg['sender_email']); ?><br>

                            <strong>Related Job:</strong>
                            <?php echo htmlspecialchars($msg['job_title'] ?? 'General Message'); ?><br>

                            <strong>Status:</strong>
                            <?php if ($msg['is_read']) { ?>
                                <span class="badge green">Read</span>
                            <?php } else { ?>
                                <span class="badge yellow">Unread</span>
                            <?php } ?><br>

                            <strong>Received At:</strong>
                            <?php echo htmlspecialchars($msg['sent_at']); ?>
                        </p>

                        <p>
                            <?php echo nl2br(htmlspecialchars($msg['body'])); ?>
                        </p>

                        <form method="POST" action="">
                            <input type="hidden" name="sender_id" value="<?php echo $msg['sender_id']; ?>">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($msg['application_id'] ?? ''); ?>">

                            <label>Reply Message</label>
                            <textarea name="body" placeholder="Write your reply..." required></textarea>

                            <button type="submit">Send Reply</button>
                        </form>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No messages found.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>