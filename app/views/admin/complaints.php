<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$status = $_GET['status'] ?? "";
$search = $_GET['search'] ?? "";

$complaints = $admin->getComplaints($status, $search);

$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaints - Admin Panel</title>
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

        .filter-box, .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        input, select {
            width: 100%;
            padding: 9px;
            margin-top: 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
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

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 11px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f9fafb;
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

        .alert-success {
            padding: 12px;
            background: #dcfce7;
            color: #166534;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-error {
            padding: 12px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .small-text {
            font-size: 12px;
            color: #6b7280;
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
        <h1 class="page-title">Handle Complaints</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="filter-box">
            <h2>Search and Filter Complaints</h2>

            <form method="GET" action="">
                <div class="filter-grid">
                    <div>
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search complaint, submitter, subject" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="open" <?php if ($status === 'open') echo 'selected'; ?>>Open</option>
                            <option value="resolved" <?php if ($status === 'resolved') echo 'selected'; ?>>Resolved</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="complaints.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <div class="table-box">
            <h2>All Complaints</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Submitter</th>
                    <th>Subject User</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>

                <?php if ($complaints->num_rows > 0) { ?>
                    <?php while ($complaint = $complaints->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $complaint['id']; ?></td>

                            <td>
                                <strong><?php echo htmlspecialchars($complaint['submitter_name']); ?></strong><br>
                                <?php echo htmlspecialchars($complaint['submitter_email']); ?><br>
                                <span class="badge blue"><?php echo htmlspecialchars($complaint['submitter_role']); ?></span>
                            </td>

                            <td>
                                <?php if (!empty($complaint['subject_name'])) { ?>
                                    <strong><?php echo htmlspecialchars($complaint['subject_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($complaint['subject_email']); ?><br>
                                    <span class="badge blue"><?php echo htmlspecialchars($complaint['subject_role']); ?></span>
                                <?php } else { ?>
                                    <span class="small-text">No subject user</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(substr($complaint['description'], 0, 100)); ?>
                                <?php if (strlen($complaint['description']) > 100) echo "..."; ?>
                            </td>

                            <td>
                                <?php if ($complaint['status'] === 'open') { ?>
                                    <span class="badge yellow">Open</span>
                                <?php } else { ?>
                                    <span class="badge green">Resolved</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php 
                                if (!empty($complaint['admin_note'])) {
                                    echo htmlspecialchars(substr($complaint['admin_note'], 0, 80));
                                    if (strlen($complaint['admin_note']) > 80) echo "...";
                                } else {
                                    echo "<span class='small-text'>No note yet</span>";
                                }
                                ?>
                            </td>

                            <td><?php echo $complaint['created_at']; ?></td>

                            <td>
                                <a href="complaint_view.php?id=<?php echo $complaint['id']; ?>" class="btn btn-success">
                                    View / Resolve
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">No complaints found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>