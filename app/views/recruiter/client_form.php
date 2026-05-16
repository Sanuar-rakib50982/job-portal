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

$employers = $recruiter->getEmployersForClient();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $companyNameOverride = trim($_POST['company_name_override'] ?? "");

    if ($clientId > 0) {
        if ($recruiter->updateClient($clientId, $recruiterId, $companyNameOverride)) {
            header("Location: clients.php?message=Client updated successfully");
            exit;
        }

        $error = "Failed to update client.";
    } else {
        $employerId = isset($_POST['employer_id']) ? (int)$_POST['employer_id'] : 0;

        if ($employerId <= 0) {
            $error = "Please select an employer company.";
        } else {
            $result = $recruiter->createClient($recruiterId, $employerId, $companyNameOverride);

            if ($result === "already_exists") {
                $error = "This employer is already added as your client.";
            } elseif ($result) {
                header("Location: clients.php?message=Client added successfully");
                exit;
            } else {
                $error = "Failed to add client.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $clientId > 0 ? "Edit Client" : "Add Client"; ?></title>
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
        <h1><?php echo $clientId > 0 ? "Edit Client Company" : "Add Client Company"; ?></h1>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="">
                <?php if ($clientId > 0) { ?>
                    <label>Employer Account</label>
                    <input 
                        type="text" 
                        value="<?php echo htmlspecialchars($client['employer_name'] . ' — ' . $client['employer_email']); ?>" 
                        disabled
                    >
                <?php } else { ?>
                    <label>Select Employer Company</label>
                    <select name="employer_id" required>
                        <option value="">Select Employer</option>

                        <?php while ($employer = $employers->fetch_assoc()) { ?>
                            <option value="<?php echo $employer['id']; ?>">
                                <?php echo htmlspecialchars($employer['name']); ?>
                                —
                                <?php echo htmlspecialchars($employer['email']); ?>
                            </option>
                        <?php } ?>
                    </select>
                <?php } ?>

                <label>Company Name Override Optional</label>
                <input 
                    type="text" 
                    name="company_name_override" 
                    value="<?php echo htmlspecialchars($client['company_name_override'] ?? ''); ?>" 
                    placeholder="Example: ABC Software Ltd."
                >

                <button type="submit">
                    <?php echo $clientId > 0 ? "Update Client" : "Add Client"; ?>
                </button>

                <a href="clients.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </main>
</div>

</body>
</html>