<?php
require_once "../../helpers/auth.php";
requireRole('employer');

require_once "../../config/database.php";
require_once "../../controllers/EmployerController.php";

$employer = new EmployerController($conn);

$employerId = $_SESSION['user_id'];
$profile = $employer->getProfile($employerId);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "company_name" => trim($_POST['company_name'] ?? ""),
        "industry" => trim($_POST['industry'] ?? ""),
        "description" => trim($_POST['description'] ?? ""),
        "website" => trim($_POST['website'] ?? ""),
        "address" => trim($_POST['address'] ?? "")
    ];

    $logoPath = null;

    if (empty($data['company_name'])) {
        $error = "Company name is required.";
    }

    if (empty($error) && !empty($_FILES['logo']['name'])) {
        $logoName = $_FILES['logo']['name'];
        $logoTmp = $_FILES['logo']['tmp_name'];
        $logoSize = $_FILES['logo']['size'];
        $logoExt = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));

        $allowedLogoTypes = ['jpg', 'jpeg', 'png'];

        if (!in_array($logoExt, $allowedLogoTypes)) {
            $error = "Logo must be JPG, JPEG, or PNG.";
        } elseif ($logoSize > 2 * 1024 * 1024) {
            $error = "Logo size must not exceed 2 MB.";
        } else {
            $newLogoName = "company_logo_" . $employerId . "_" . time() . "." . $logoExt;
            $uploadDir = __DIR__ . "/../../../public/uploads/company_logos/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $targetPath = $uploadDir . $newLogoName;

            if (move_uploaded_file($logoTmp, $targetPath)) {
                $logoPath = "public/uploads/company_logos/" . $newLogoName;
            } else {
                $error = "Failed to upload company logo.";
            }
        }
    }

    if (empty($error)) {
        if ($employer->saveProfile($employerId, $data, $logoPath)) {
            $message = "Company profile saved successfully.";
            $profile = $employer->getProfile($employerId);
        } else {
            $error = "Failed to save company profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Company Profile</title>
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
        <h1>Company Profile</h1>
        <p>Manage your employer/company information.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="" enctype="multipart/form-data">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>" required>

                <label>Industry</label>
                <input type="text" name="industry" value="<?php echo htmlspecialchars($profile['industry'] ?? ''); ?>">

                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>

                <label>Website</label>
                <input type="text" name="website" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">

                <label>Address</label>
                <textarea name="address"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>

                <label>Company Logo JPG, JPEG, PNG, Max 2 MB</label>
                <input type="file" name="logo" accept="image/jpeg,image/png">

                <?php if (!empty($profile['logo_path'])) { ?>
                    <p>Current Logo:</p>
                    <img src="../../../<?php echo htmlspecialchars($profile['logo_path']); ?>" width="120" style="border-radius: 10px;">
                <?php } ?>

                <button type="submit">Save Profile</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>