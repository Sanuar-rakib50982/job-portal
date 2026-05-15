<?php
require_once "../../app/helpers/auth.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

require_once "../../app/config/database.php";
require_once "../../app/controllers/SeekerController.php";

$seeker = new SeekerController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$seekerId = $_SESSION['user_id'];
$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

if ($jobId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid job ID"
    ]);
    exit;
}

$result = $seeker->toggleSavedJob($jobId, $seekerId);

if ($result === "saved") {
    echo json_encode([
        "success" => true,
        "status" => "saved",
        "button_text" => "Unsave Job",
        "message" => "Job saved successfully"
    ]);
    exit;
}

if ($result === "unsaved") {
    echo json_encode([
        "success" => true,
        "status" => "unsaved",
        "button_text" => "Save Job",
        "message" => "Job removed from saved jobs"
    ]);
    exit;
}

echo json_encode([
    "success" => false,
    "message" => "Failed to update saved job status"
]);
exit;