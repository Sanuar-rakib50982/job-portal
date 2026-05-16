<?php
require_once "../../helpers/auth.php";
requireRole('employer');

require_once "../../config/database.php";
require_once "../../controllers/EmployerController.php";

$employer = new EmployerController($conn);

$employerId = $_SESSION['user_id'];

$subjects = $employer->getComplaintSubjectUsers();

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subjectId = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $description = trim($_POST['description'] ?? "");

    if (empty($description)) {
        $error = "Complaint description is required.";
    } elseif (strlen($description) < 10) {
        $error = "Complaint description must be at least 10 characters.";
    } else {
        if ($employer->submitComplaint($employerId, $subjectId, $description)) {
            $message = "Complaint submitted successfully. Admin will review it.";
        } else {
            $error = "Failed to submit complaint.";
        }
    }
}

$complaints = $employer->getMyComplaints($employerId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Complaint - Employer</title>
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
        <h1>Submit Complaint</h1>
        <p>Report misleading users, inappropriate behavior, or platform issues.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>New Complaint</h2>

            <form method="POST" action="">
                <label>Complaint Against</label>
                <select name="subject_id">
                    <option value="">General Platform Complaint</option>

                    <?php while ($subject = $subjects->fetch_assoc()) { ?>
                        <option value="<?php echo $subject['id']; ?>">
                            <?php echo htmlspecialchars($subject['name']); ?>
                            —
                            <?php echo htmlspecialchars($subject['role']); ?>
                            —
                            <?php echo htmlspecialchars($subject['email']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Complaint Description</label>
                <textarea name="description" placeholder="Write your complaint clearly..." required></textarea>

                <button type="submit">Submit Complaint</button>
            </form>
        </div>

        <div class="table-box">
            <h2>My Complaint History</h2>

            <?php if ($complaints->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Subject User</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Admin Note</th>
                        <th>Submitted At</th>
                    </tr>

                    <?php while ($complaint = $complaints->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $complaint['id']; ?></td>

                            <td>
                                <?php if (!empty($complaint['subject_name'])) { ?>
                                    <strong><?php echo htmlspecialchars($complaint['subject_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($complaint['subject_email']); ?><br>
                                    <span class="badge gray"><?php echo htmlspecialchars($complaint['subject_role']); ?></span>
                                <?php } else { ?>
                                    General Platform Complaint
                                <?php } ?>
                            </td>

                            <td><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></td>

                            <td>
                                <?php if ($complaint['status'] === 'resolved') { ?>
                                    <span class="badge green">Resolved</span>
                                <?php } else { ?>
                                    <span class="badge yellow">Open</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php
                                if (!empty($complaint['admin_note'])) {
                                    echo nl2br(htmlspecialchars($complaint['admin_note']));
                                } else {
                                    echo "No admin note yet";
                                }
                                ?>
                            </td>

                            <td><?php echo htmlspecialchars($complaint['created_at']); ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No complaints submitted yet.</p>
            <?php } ?>
        </div>
    </main>
</div>

</body>
</html>