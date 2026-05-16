<?php
require_once "../../helpers/auth.php";
requireRole('employer');

require_once "../../config/database.php";
require_once "../../controllers/EmployerController.php";

$employer = new EmployerController($conn);

$employerId = $_SESSION['user_id'];
$applications = $employer->getEmployerApplications($employerId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Applications - Employer</title>
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
        <h1>Applications</h1>
        <p>Review candidates and update application status.</p>

        <div id="statusMessage"></div>

        <div class="table-box">
            <h2>Candidate Pipeline</h2>

            <table>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Profile</th>
                    <th>Cover Letter</th>
                    <th>Resume</th>
                    <th>Status</th>
                    <th>Applied At</th>
                    <th>Action</th>
                </tr>

                <?php if ($applications->num_rows > 0) { ?>
                    <?php while ($application = $applications->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($application['seeker_name']); ?></strong><br>
                                <?php echo htmlspecialchars($application['seeker_email']); ?><br>
                                <?php echo htmlspecialchars($application['seeker_phone'] ?? 'N/A'); ?>
                            </td>

                            <td>
                                <strong><?php echo htmlspecialchars($application['job_title']); ?></strong><br>
                                Location: <?php echo htmlspecialchars($application['location'] ?? 'N/A'); ?><br>
                                Type: <?php echo htmlspecialchars($application['job_type'] ?? 'N/A'); ?><br>
                                Experience: <?php echo htmlspecialchars($application['experience_level'] ?? 'N/A'); ?>
                            </td>

                            <td>
                                <strong><?php echo htmlspecialchars($application['headline'] ?? 'No headline'); ?></strong><br>
                                Skills: <?php echo htmlspecialchars($application['skills'] ?? 'N/A'); ?><br>
                                Experience: <?php echo htmlspecialchars($application['years_experience'] ?? '0'); ?> years<br>
                                Education: <?php echo htmlspecialchars($application['education_level'] ?? 'N/A'); ?><br>
                                Preferred Location: <?php echo htmlspecialchars($application['preferred_location'] ?? 'N/A'); ?><br><br>

                                <a class="btn btn-secondary" href="seeker_profile.php?id=<?php echo $application['seeker_id']; ?>">View Profile</a>
                            </td>

                            <td><?php echo nl2br(htmlspecialchars($application['cover_letter'] ?? 'No cover letter')); ?></td>

                            <td>
                                <?php if (!empty($application['application_resume'])) { ?>
                                    <a href="../../../<?php echo htmlspecialchars($application['application_resume']); ?>" target="_blank">Application Resume</a>
                                <?php } elseif (!empty($application['profile_resume'])) { ?>
                                    <a href="../../../<?php echo htmlspecialchars($application['profile_resume']); ?>" target="_blank">Profile Resume</a>
                                <?php } else { ?>
                                    No resume
                                <?php } ?>
                            </td>

                            <td>
                                <?php
                                $status = $application['status'];
                                $badgeClass = "gray";

                                if ($status === "submitted") {
                                    $badgeClass = "yellow";
                                } elseif ($status === "shortlisted" || $status === "interview") {
                                    $badgeClass = "green";
                                } elseif ($status === "rejected") {
                                    $badgeClass = "red";
                                }
                                ?>

                                <span class="badge <?php echo $badgeClass; ?>" id="statusBadge-<?php echo $application['application_id']; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>

                            <td><?php echo htmlspecialchars($application['applied_at']); ?></td>

                            <td>
                                <select id="statusSelect-<?php echo $application['application_id']; ?>">
                                    <option value="submitted" <?php echo $status === 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                                    <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="shortlisted" <?php echo $status === 'shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                    <option value="interview" <?php echo $status === 'interview' ? 'selected' : ''; ?>>Interview</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>

                                <button 
                                    type="button" 
                                    class="btn update-status-btn"
                                    data-application-id="<?php echo $application['application_id']; ?>">
                                    Update
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">No applications found for your jobs.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>

<script src="../../../public/js/employer.js"></script>
</body>
</html>