<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$complaintId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaintId <= 0) {
    header("Location: complaints.php?error=Invalid complaint ID");
    exit;
}

$complaint = $admin->getComplaintById($complaintId);

if (!$complaint) {
    header("Location: complaints.php?error=Complaint not found");
    exit;
}

$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaint Details - Admin Panel</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <link rel="stylesheet" href="../../../public/css/admin.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            color: #1f2937;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            background: #111827;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 25px;
        }

        .sidebar a {
            display: block;
            color: #d1d5db;
            text-decoration: none;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
        }

        .sidebar a:hover {
            background: #374151;
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 25px;
        }

        .page-title {
            font-size: 28px;
            margin-bottom: 20px;
        }

        .detail-box, .form-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        textarea {
            width: 100%;
            min-height: 140px;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
            margin-top: 5px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .badge {
            padding: 5px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
            display: inline-block;
        }

        .green {
            background: #16a34a;
        }

        .yellow {
            background: #f59e0b;
        }

        .blue {
            background: #2563eb;
        }

        .alert-error {
            padding: 12px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .text-block {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

<div class="admin-wrapper">

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="categories.php">Categories</a>
        <a href="jobs.php">Jobs</a>
        <a href="complaints.php">Complaints</a>
        <a href="policies.php">Policies</a>
        <a href="announcements.php">Announcements</a>
        <a href="analytics.php">Analytics</a>
        <a href="../../../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1 class="page-title">Complaint Details</h1>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="detail-box">
            <h2>Complaint Status</h2>

            <?php if ($complaint['status'] === 'open') { ?>
                <span class="badge yellow">Open</span>
            <?php } else { ?>
                <span class="badge green">Resolved</span>
            <?php } ?>

            <p><strong>Submitted At:</strong> <?php echo $complaint['created_at']; ?></p>
        </div>

        <div class="detail-box">
            <h2>User Information</h2>

            <div class="info-grid">
                <div>
                    <h3>Submitter</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($complaint['submitter_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($complaint['submitter_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($complaint['submitter_phone']); ?></p>
                    <p><strong>Role:</strong> <span class="badge blue"><?php echo htmlspecialchars($complaint['submitter_role']); ?></span></p>
                </div>

                <div>
                    <h3>Subject User</h3>

                    <?php if (!empty($complaint['subject_name'])) { ?>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($complaint['subject_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($complaint['subject_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($complaint['subject_phone']); ?></p>
                        <p><strong>Role:</strong> <span class="badge blue"><?php echo htmlspecialchars($complaint['subject_role']); ?></span></p>
                    <?php } else { ?>
                        <p>No subject user selected.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="detail-box">
            <h2>Complaint Description</h2>
            <div class="text-block"><?php echo htmlspecialchars($complaint['description']); ?></div>
        </div>

        <div class="detail-box">
            <h2>Current Admin Note</h2>

            <?php if (!empty($complaint['admin_note'])) { ?>
                <div class="text-block"><?php echo htmlspecialchars($complaint['admin_note']); ?></div>
            <?php } else { ?>
                <p>No admin note added yet.</p>
            <?php } ?>
        </div>

        <div class="form-box">
            <h2>Resolve / Update Complaint</h2>

            <form method="POST" action="complaint_action.php">
                <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                <input type="hidden" name="action" value="resolve">

                <label>Admin Resolution Note</label>
                <textarea name="admin_note" placeholder="Write resolution note..." required><?php echo htmlspecialchars($complaint['admin_note'] ?? ""); ?></textarea>

                <button type="submit" class="btn btn-success">Mark as Resolved</button>
                <a href="complaints.php" class="btn btn-secondary">Back</a>
            </form>

            <?php if ($complaint['status'] === 'resolved') { ?>
                <form method="POST" action="complaint_action.php" style="margin-top: 10px;">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                    <input type="hidden" name="action" value="reopen">
                    <button type="submit" class="btn btn-warning">Reopen Complaint</button>
                </form>
            <?php } ?>
        </div>

    </div>
</div>

</body>
</html>