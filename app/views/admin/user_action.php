<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php?error=Invalid request");
    exit;
}

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$action = $_POST['action'] ?? "";
$reason = trim($_POST['reason'] ?? "");
$adminId = $_SESSION['user_id'];

if ($userId <= 0 || empty($action)) {
    header("Location: users.php?error=Invalid user or action");
    exit;
}

if ($action === "approve") {
    if ($admin->approveUser($userId, $adminId)) {
        header("Location: users.php?message=User approved successfully");
        exit;
    }

    header("Location: users.php?error=Failed to approve user");
    exit;
}

if ($action === "reject") {
    if (empty($reason)) {
        header("Location: users.php?error=Reject reason is required");
        exit;
    }

    if ($admin->rejectUser($userId, $adminId, $reason)) {
        header("Location: users.php?message=User rejected successfully");
        exit;
    }

    header("Location: users.php?error=Failed to reject user");
    exit;
}

if ($action === "suspend") {
    if (empty($reason)) {
        header("Location: users.php?error=Suspend reason is required");
        exit;
    }

    if ($admin->suspendUser($userId, $adminId, $reason)) {
        header("Location: users.php?message=User suspended successfully");
        exit;
    }

    header("Location: users.php?error=Failed to suspend user");
    exit;
}

if ($action === "reactivate") {
    if ($admin->reactivateUser($userId, $adminId)) {
        header("Location: users.php?message=User reactivated successfully");
        exit;
    }

    header("Location: users.php?error=Failed to reactivate user");
    exit;
}

header("Location: users.php?error=Unknown action");
exit;