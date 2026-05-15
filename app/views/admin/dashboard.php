<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$totalUsers = $admin->countTotalUsers();
$totalSeekers = $admin->countUsersByRole('seeker');
$totalEmployers = $admin->countUsersByRole('employer');
$totalRecruiters = $admin->countUsersByRole('recruiter');
$activeJobs = $admin->countActiveJobs();
$totalApplications = $admin->countTotalApplications();
$applicationsToday = $admin->countApplicationsToday();
$pendingVerifications = $admin->countPendingVerifications();
$openComplaints = $admin->countOpenComplaints();

$recentUsers = $admin->getRecentUsers();
$recentComplaints = $admin->getRecentComplaints();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Job Portal</title>
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

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .card h3 {
            margin: 0;
            font-size: 15px;
            color: #6b7280;
        }

        .card p {
            margin: 10px 0 0;
            font-size: 28px;
            font-weight: bold;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 11px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #f9fafb;
        }

        .badge {
            padding: 5px 8px;
            border-radius: 12px;
            font-size: 12px;
            color: white;
        }

        .green {
            background: #16a34a;
        }

        .red {
            background: #dc2626;
        }

        .yellow {
            background: #f59e0b;
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
        <h1 class="page-title">Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>

        <div class="card-grid">
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>

            <div class="card">
                <h3>Job Seekers</h3>
                <p><?php echo $totalSeekers; ?></p>
            </div>

            <div class="card">
                <h3>Employers</h3>
                <p><?php echo $totalEmployers; ?></p>
            </div>

            <div class="card">
                <h3>Recruiters</h3>
                <p><?php echo $totalRecruiters; ?></p>
            </div>

            <div class="card">
                <h3>Active Jobs</h3>
                <p><?php echo $activeJobs; ?></p>
            </div>

            <div class="card">
                <h3>Total Applications</h3>
                <p><?php echo $totalApplications; ?></p>
            </div>

            <div class="card">
                <h3>Applications Today</h3>
                <p><?php echo $applicationsToday; ?></p>
            </div>

            <div class="card">
                <h3>Pending Verification</h3>
                <p><?php echo $pendingVerifications; ?></p>
            </div>

            <div class="card">
                <h3>Open Complaints</h3>
                <p><?php echo $openComplaints; ?></p>
            </div>
        </div>

        <div class="table-box">
            <h2>Recent Users</h2>

            <table>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Active</th>
                    <th>Verified</th>
                    <th>Created At</th>
                </tr>

                <?php while ($user = $recentUsers->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php if ($user['is_active']) { ?>
                                <span class="badge green">Active</span>
                            <?php } else { ?>
                                <span class="badge red">Suspended</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($user['is_verified']) { ?>
                                <span class="badge green">Verified</span>
                            <?php } else { ?>
                                <span class="badge yellow">Pending</span>
                            <?php } ?>
                        </td>
                        <td><?php echo $user['created_at']; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="table-box">
            <h2>Recent Complaints</h2>

            <table>
                <tr>
                    <th>Submitter</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>

                <?php if ($recentComplaints->num_rows > 0) { ?>
                    <?php while ($complaint = $recentComplaints->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($complaint['submitter_name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($complaint['description'], 0, 80)); ?>...</td>
                            <td>
                                <?php if ($complaint['status'] === 'open') { ?>
                                    <span class="badge yellow">Open</span>
                                <?php } else { ?>
                                    <span class="badge green">Resolved</span>
                                <?php } ?>
                            </td>
                            <td><?php echo $complaint['created_at']; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="4">No complaints found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>