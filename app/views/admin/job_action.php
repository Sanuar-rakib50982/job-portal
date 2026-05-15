<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: jobs.php?error=Invalid request");
    exit;
}

$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$action = $_POST['action'] ?? "";
$reason = trim($_POST['reason'] ?? "");
$adminId = $_SESSION['user_id'];

if ($jobId <= 0 || empty($action)) {
    header("Location: jobs.php?error=Invalid job or action");
    exit;
}

if ($action === "close") {
    if (empty($reason)) {
        header("Location: jobs.php?error=Reason is required to close or remove a job");
        exit;
    }

    if ($admin->updateJobStatus($jobId, "closed", $adminId, $reason)) {
        header("Location: jobs.php?message=Job closed successfully");
        exit;
    }

    header("Location: jobs.php?error=Failed to close job");
    exit;
}

if ($action === "activate") {
    if ($admin->updateJobStatus($jobId, "active", $adminId, "Job reactivated by admin")) {
        header("Location: jobs.php?message=Job reactivated successfully");
        exit;
    }

    header("Location: jobs.php?error=Failed to reactivate job");
    exit;
}

header("Location: jobs.php?error=Unknown action");
exit;