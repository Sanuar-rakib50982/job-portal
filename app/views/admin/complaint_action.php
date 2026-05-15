<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: complaints.php?error=Invalid request");
    exit;
}

$complaintId = isset($_POST['complaint_id']) ? (int)$_POST['complaint_id'] : 0;
$action = $_POST['action'] ?? "";
$adminId = $_SESSION['user_id'];

if ($complaintId <= 0 || empty($action)) {
    header("Location: complaints.php?error=Invalid complaint or action");
    exit;
}

if ($action === "resolve") {
    $adminNote = trim($_POST['admin_note'] ?? "");

    if (empty($adminNote)) {
        header("Location: complaint_view.php?id=$complaintId&error=Admin note is required");
        exit;
    }

    if ($admin->resolveComplaint($complaintId, $adminNote, $adminId)) {
        header("Location: complaints.php?message=Complaint resolved successfully");
        exit;
    }

    header("Location: complaint_view.php?id=$complaintId&error=Failed to resolve complaint");
    exit;
}

if ($action === "reopen") {
    if ($admin->reopenComplaint($complaintId, $adminId)) {
        header("Location: complaints.php?message=Complaint reopened successfully");
        exit;
    }

    header("Location: complaint_view.php?id=$complaintId&error=Failed to reopen complaint");
    exit;
}

header("Location: complaints.php?error=Unknown action");
exit;