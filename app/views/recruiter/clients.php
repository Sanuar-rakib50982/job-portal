<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$recruiterId = $_SESSION['user_id'];
$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === "delete") {
    $clientId = isset($_POST['client_id']) ? (int)$_POST['client_id'] : 0;

    if ($clientId > 0 && $recruiter->deleteClient($clientId, $recruiterId)) {
        header("Location: clients.php?message=Client removed successfully");
        exit;
    }

    header("Location: clients.php?error=Failed to remove client");
    exit;
}

$clients = $recruiter->getClients($recruiterId);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Companies</title>
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
        <h1>Client Companies</h1>
        <p>Manage companies you recruit for.</p>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <a href="client_form.php" class="btn">Add New Client</a>
        <br><br>

        <div class="table-box">
            <h2>My Clients</h2>

            <table>
                <tr>
                    <th>Company</th>
                    <th>Employer Account</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Added At</th>
                    <th>Action</th>
                </tr>

                <?php if ($clients->num_rows > 0) { ?>
                    <?php while ($client = $clients->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        !empty($client['company_name_override'])
                                            ? $client['company_name_override']
                                            : ($client['employer_name'] ?? 'N/A')
                                    );
                                    ?>
                                </strong>
                            </td>

                            <td><?php echo htmlspecialchars($client['employer_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($client['employer_email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($client['employer_phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($client['added_at'] ?? 'N/A'); ?></td>

                            <td>
                                <a class="btn btn-secondary" href="client_form.php?id=<?php echo $client['id']; ?>">Edit</a>

                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Remove this client?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                    <button type="submit" class="btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">No client companies added yet.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>

</body>
</html>