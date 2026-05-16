<?php
require_once "../../app/helpers/auth.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recruiter') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

require_once "../../app/config/database.php";
require_once "../../app/controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$recruiterId = $_SESSION['user_id'];
$applicationId = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
$status = trim($_POST['status'] ?? "");

$allowedStatuses = ['submitted', 'reviewed', 'shortlisted', 'interview', 'rejected'];

if ($applicationId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid application ID"
    ]);
    exit;
}

if (!in_array($status, $allowedStatuses)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid status selected"
    ]);
    exit;
}

if ($recruiter->updateApplicationStatus($applicationId, $recruiterId, $status)) {
    echo json_encode([
        "success" => true,
        "message" => "Application status updated successfully",
        "status" => $status
    ]);
    exit;
}

echo json_encode([
    "success" => false,
    "message" => "Failed to update application status"
]);
exit;