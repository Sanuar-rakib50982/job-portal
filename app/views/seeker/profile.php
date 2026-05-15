<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$userId = $_SESSION['user_id'];
$profile = $seeker->getProfile($userId);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "headline" => trim($_POST['headline'] ?? ""),
        "summary" => trim($_POST['summary'] ?? ""),
        "skills" => trim($_POST['skills'] ?? ""),
        "years_experience" => (int)($_POST['years_experience'] ?? 0),
        "education_level" => trim($_POST['education_level'] ?? ""),
        "current_salary" => (float)($_POST['current_salary'] ?? 0),
        "expected_salary" => (float)($_POST['expected_salary'] ?? 0),
        "preferred_location" => trim($_POST['preferred_location'] ?? "")
    ];

    $resumePath = null;

    if (!empty($_FILES['resume']['name'])) {
        $resumeName = $_FILES['resume']['name'];
        $resumeTmp = $_FILES['resume']['tmp_name'];
        $resumeSize = $_FILES['resume']['size'];
        $resumeExt = strtolower(pathinfo($resumeName, PATHINFO_EXTENSION));

        if ($resumeExt !== "pdf") {
            $error = "Resume must be a PDF file.";
        } elseif ($resumeSize > 5 * 1024 * 1024) {
            $error = "Resume size must not exceed 5 MB.";
        } else {
            $newResumeName = "resume_" . $userId . "_" . time() . ".pdf";
            $uploadDir = __DIR__ . "/../../../public/uploads/resumes/";
            $targetPath = $uploadDir . $newResumeName;

            if (move_uploaded_file($resumeTmp, $targetPath)) {
                $resumePath = "public/uploads/resumes/" . $newResumeName;
            } else {
                $error = "Failed to upload resume.";
            }
        }
    }

    if (empty($error) && !empty($_FILES['profile_pic']['name'])) {
        $picName = $_FILES['profile_pic']['name'];
        $picTmp = $_FILES['profile_pic']['tmp_name'];
        $picSize = $_FILES['profile_pic']['size'];
        $picExt = strtolower(pathinfo($picName, PATHINFO_EXTENSION));

        $allowedPicTypes = ['jpg', 'jpeg', 'png'];

        if (!in_array($picExt, $allowedPicTypes)) {
            $error = "Profile picture must be JPG, JPEG, or PNG.";
        } elseif ($picSize > 2 * 1024 * 1024) {
            $error = "Profile picture size must not exceed 2 MB.";
        } else {
            $newPicName = "profile_" . $userId . "_" . time() . "." . $picExt;
            $uploadDir = __DIR__ . "/../../../public/uploads/profile_pics/";
            $targetPath = $uploadDir . $newPicName;

            if (move_uploaded_file($picTmp, $targetPath)) {
                $profilePicPath = "public/uploads/profile_pics/" . $newPicName;
                $seeker->updateProfilePicture($userId, $profilePicPath);
            } else {
                $error = "Failed to upload profile picture.";
            }
        }
    }

    if (empty($error)) {
        if ($seeker->saveProfile($userId, $data, $resumePath)) {
            $message = "Profile updated successfully.";
            $profile = $seeker->getProfile($userId);
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Job Seeker</title>
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
        <h1>My Profile</h1>
        <p>Build and update your professional profile.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="" enctype="multipart/form-data">
                <label>Headline</label>
                <input type="text" name="headline" value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>" placeholder="Example: Junior Web Developer">

                <label>Professional Summary</label>
                <textarea name="summary" placeholder="Write a short professional summary"><?php echo htmlspecialchars($profile['summary'] ?? ''); ?></textarea>

                <label>Skills</label>
                <input type="text" name="skills" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" placeholder="Example: PHP, MySQL, HTML, CSS">

                <label>Years of Experience</label>
                <input type="number" name="years_experience" min="0" value="<?php echo htmlspecialchars($profile['years_experience'] ?? '0'); ?>">

                <label>Education Level</label>
                <input type="text" name="education_level" value="<?php echo htmlspecialchars($profile['education_level'] ?? ''); ?>" placeholder="Example: BSc in CSE">

                <label>Current Salary</label>
                <input type="number" name="current_salary" min="0" value="<?php echo htmlspecialchars($profile['current_salary'] ?? '0'); ?>">

                <label>Expected Salary</label>
                <input type="number" name="expected_salary" min="0" value="<?php echo htmlspecialchars($profile['expected_salary'] ?? '0'); ?>">

                <label>Preferred Location</label>
                <input type="text" name="preferred_location" value="<?php echo htmlspecialchars($profile['preferred_location'] ?? ''); ?>" placeholder="Example: Dhaka">

                <label>Upload Resume PDF, Max 5 MB</label>
                <input type="file" name="resume" accept="application/pdf">

                <?php if (!empty($profile['resume_path'])) { ?>
                    <p>Current Resume: <a href="../../../<?php echo htmlspecialchars($profile['resume_path']); ?>" target="_blank">View Resume</a></p>
                <?php } ?>

                <label>Upload Profile Picture JPG, JPEG, PNG, Max 2 MB</label>
                <input type="file" name="profile_pic" accept="image/jpeg,image/png">

                <?php if (!empty($profile['profile_pic'])) { ?>
                    <p>Current Profile Picture:</p>
                    <img src="../../../<?php echo htmlspecialchars($profile['profile_pic']); ?>" width="120" style="border-radius: 10px;">
                <?php } ?>

                <button type="submit">Save Profile</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>