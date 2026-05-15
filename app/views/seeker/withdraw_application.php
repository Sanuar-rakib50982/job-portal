<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: applications.php?error=Invalid request");
    exit;
}

$seekerId = $_SESSION['user_id'];
$applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;

if ($applicationId <= 0) {
    header("Location: applications.php?error=Invalid application ID");
    exit;
}

$result = $seeker->withdrawApplication($applicationId, $seekerId);

if ($result === "not_found") {
    header("Location: applications.php?error=Application not found");
    exit;
}

if ($result === "not_allowed") {
    header("Location: applications.php?error=Only submitted applications can be withdrawn");
    exit;
}

if ($result) {
    header("Location: applications.php?message=Application withdrawn successfully");
    exit;
}

header("Location: applications.php?error=Failed to withdraw application");
exit;