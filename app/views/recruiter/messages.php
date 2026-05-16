<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "send") {
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        $applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
        $body = trim($_POST['body'] ?? "");

        if ($recipientId <= 0) {
            $error = "Please select a seeker.";
        } elseif (empty($body)) {
            $error = "Message body is required.";
        } elseif (strlen($body) < 5) {
            $error = "Message must be at least 5 characters.";
        } else {
            if ($recruiter->sendMessage($recruiterId, $recipientId, $applicationId, $body)) {
                $message = "Message sent successfully.";
            } else {
                $error = "Failed to send message.";
            }
        }
    }

    if ($action === "reply") {
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
        $applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
        $body = trim($_POST['body'] ?? "");

        if ($messageId > 0) {
            $recruiter->markMessageAsRead($messageId, $recruiterId);
        }

        if ($recipientId <= 0) {
            $error = "Invalid recipient.";
        } elseif (empty($body)) {
            $error = "Reply message is required.";
        } elseif (strlen($body) < 5) {
            $error = "Reply message must be at least 5 characters.";
        } else {
            if ($recruiter->sendMessage($recruiterId, $recipientId, $applicationId, $body)) {
                $message = "Reply sent successfully.";
            } else {
                $error = "Failed to send reply.";
            }
        }
    }

    if ($action === "mark_read") {
        $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

        if ($messageId > 0 && $recruiter->markMessageAsRead($messageId, $recruiterId)) {
            $message = "Message marked as read.";
        } else {
            $error = "Failed to mark message as read.";
        }
    }
}

$seekers = $recruiter->getSeekersForMessage();
$applicationsForMessage = $recruiter->getRecruiterApplicationsForMessage($recruiterId);
$receivedMessages = $recruiter->getReceivedMessages($recruiterId);
$sentMessages = $recruiter->getSentMessages($recruiterId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages - Recruiter</title>
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
        <h1>Messages</h1>
        <p>Send, receive, and reply to seeker messages.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>Send New Message</h2>

            <form method="POST" action="">
                <input type="hidden" name="action" value="send">

                <label>Select Seeker</label>
                <select name="recipient_id" required>
                    <option value="">Select Seeker</option>

                    <?php while ($seeker = $seekers->fetch_assoc()) { ?>
                        <option value="<?php echo $seeker['id']; ?>">
                            <?php echo htmlspecialchars($seeker['name']); ?>
                            —
                            <?php echo htmlspecialchars($seeker['email']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Related Application Optional</label>
                <select name="application_id">
                    <option value="">General Message</option>

                    <?php while ($application = $applicationsForMessage->fetch_assoc()) { ?>
                        <option value="<?php echo $application['application_id']; ?>">
                            Application #<?php echo $application['application_id']; ?>
                            —
                            <?php echo htmlspecialchars($application['seeker_name']); ?>
                            —
                            <?php echo htmlspecialchars($application['job_title']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Message</label>
                <textarea name="body" placeholder="Write your message..." required></textarea>

                <button type="submit">Send Message</button>
            </form>
        </div>

        <div class="table-box">
            <h2>Received Messages</h2>

            <?php if ($receivedMessages->num_rows > 0) { ?>
                <?php while ($msg = $receivedMessages->fetch_assoc()) { ?>
                    <div class="job-card">
                        <h3>
                            Message from <?php echo htmlspecialchars($msg['sender_name']); ?>
                        </h3>

                        <p>
                            <strong>From:</strong>
                            <?php echo htmlspecialchars($msg['sender_name']); ?>
                            —
                            <?php echo htmlspecialchars($msg['sender_role']); ?><br>

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

                        <?php if (!$msg['is_read']) { ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" class="btn-secondary">Mark as Read</button>
                            </form>
                        <?php } ?>

                        <form method="POST" action="">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="recipient_id" value="<?php echo $msg['sender_id']; ?>">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($msg['application_id'] ?? ''); ?>">

                            <label>Reply Message</label>
                            <textarea name="body" placeholder="Write your reply..." required></textarea>

                            <button type="submit">Send Reply</button>
                        </form>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No received messages found.</p>
            <?php } ?>
        </div>

        <div class="table-box">
            <h2>Sent Messages</h2>

            <?php if ($sentMessages->num_rows > 0) { ?>
                <?php while ($sent = $sentMessages->fetch_assoc()) { ?>
                    <div class="job-card">
                        <h3>
                            Message to <?php echo htmlspecialchars($sent['recipient_name']); ?>
                        </h3>

                        <p>
                            <strong>To:</strong>
                            <?php echo htmlspecialchars($sent['recipient_name']); ?>
                            —
                            <?php echo htmlspecialchars($sent['recipient_role']); ?><br>

                            <strong>Email:</strong>
                            <?php echo htmlspecialchars($sent['recipient_email']); ?><br>

                            <strong>Related Job:</strong>
                            <?php echo htmlspecialchars($sent['job_title'] ?? 'General Message'); ?><br>

                            <strong>Sent At:</strong>
                            <?php echo htmlspecialchars($sent['sent_at']); ?><br>

                            <strong>Receiver Read Status:</strong>
                            <?php if ($sent['is_read']) { ?>
                                <span class="badge green">Read</span>
                            <?php } else { ?>
                                <span class="badge yellow">Unread</span>
                            <?php } ?>
                        </p>

                        <p>
                            <?php echo nl2br(htmlspecialchars($sent['body'])); ?>
                        </p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p>No sent messages found.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>