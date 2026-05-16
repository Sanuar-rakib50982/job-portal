<?php

require_once "../../app/helpers/auth.php";
requireRole('admin');

require_once "../../app/config/database.php";
require_once "../../app/controllers/AdminController.php";

header("Content-Type: application/json");

$admin = new AdminController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$adminId = $_SESSION['user_id'];

if ($jobId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid job ID"
    ]);
    exit;
}

$result = $admin->toggleFeaturedJob($jobId, $adminId);

if ($result === false) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update featured status"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "is_featured" => $result,
    "message" => $result ? "Job marked as featured" : "Job removed from featured list"
]);

exit;