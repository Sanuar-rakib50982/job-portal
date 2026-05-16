<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];
$clientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$client = null;

if ($clientId > 0) {
    $client = $recruiter->getClientById($clientId, $recruiterId);

    if (!$client) {
        header("Location: clients.php?error=Client not found");
        exit;
    }
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "company_name" => trim($_POST['company_name'] ?? ""),
        "contact_person" => trim($_POST['contact_person'] ?? ""),
        "email" => trim($_POST['email'] ?? ""),
        "phone" => trim($_POST['phone'] ?? ""),
        "industry" => trim($_POST['industry'] ?? ""),
        "address" => trim($_POST['address'] ?? "")
    ];

    if (empty($data['company_name'])) {
        $error = "Company name is required.";
    } else {
        if ($clientId > 0) {
            if ($recruiter->updateClient($clientId, $recruiterId, $data)) {
                header("Location: clients.php?message=Client updated successfully");
                exit;
            }
        } else {
            if ($recruiter->createClient($recruiterId, $data)) {
                header("Location: clients.php?message=Client added successfully");
                exit;
            }
        }

        $error = "Failed to save client.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Form</title>
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
        <h1><?php echo $clientId > 0 ? "Edit Client" : "Add Client"; ?></h1>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?php echo htmlspecialchars($client['company_name'] ?? ''); ?>" required>

                <label>Contact Person</label>
                <input type="text" name="contact_person" value="<?php echo htmlspecialchars($client['contact_person'] ?? ''); ?>">

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($client['email'] ?? ''); ?>">

                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>">

                <label>Industry</label>
                <input type="text" name="industry" value="<?php echo htmlspecialchars($client['industry'] ?? ''); ?>">

                <label>Address</label>
                <textarea name="address"><?php echo htmlspecialchars($client['address'] ?? ''); ?></textarea>

                <button type="submit">Save Client</button>
                <a href="clients.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>
