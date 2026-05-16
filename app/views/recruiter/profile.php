<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];
$profile = $recruiter->getProfile($recruiterId);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "agency_name" => trim($_POST['agency_name'] ?? ""),
        "specialization" => trim($_POST['specialization'] ?? ""),
        "experience_years" => (int)($_POST['experience_years'] ?? 0),
        "bio" => trim($_POST['bio'] ?? ""),
        "website" => trim($_POST['website'] ?? "")
    ];

    if (empty($data['agency_name'])) {
        $error = "Agency name is required.";
    } else {
        if ($recruiter->saveProfile($recruiterId, $data)) {
            $message = "Profile saved successfully.";
            $profile = $recruiter->getProfile($recruiterId);
        } else {
            $error = "Failed to save profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recruiter Profile</title>
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
        <h1>Recruiter Profile</h1>
        <p>Manage your recruiter or agency profile.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="">
                <label>Agency Name</label>
                <input type="text" name="agency_name" value="<?php echo htmlspecialchars($profile['agency_name'] ?? ''); ?>" required>

                <label>Specialization</label>
                <input type="text" name="specialization" value="<?php echo htmlspecialchars($profile['specialization'] ?? ''); ?>" placeholder="Example: IT, Banking, Marketing">

                <label>Years of Experience</label>
                <input type="number" name="experience_years" min="0" value="<?php echo htmlspecialchars($profile['experience_years'] ?? '0'); ?>">

                <label>Bio</label>
                <textarea name="bio"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>

                <label>Website</label>
                <input type="text" name="website" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>" placeholder="https://example.com">

                <button type="submit">Save Profile</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>