<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$jobsByCategory = $admin->getJobsByCategory();
$applicationsOverTime = $admin->getApplicationsOverTime();
$topEmployers = $admin->getTopEmployers();
$activeRecruiters = $admin->getMostActiveRecruiters();
$popularLocations = $admin->getPopularLocations();
$popularJobTypes = $admin->getPopularJobTypes();
$userGrowth = $admin->getUserGrowthByRole();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analytics - Admin Panel</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
    <link rel="stylesheet" href="../../../public/css/admin.css">

    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; color: #1f2937; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 230px; background: #111827; color: white; padding: 20px; }
        .sidebar h2 { margin-bottom: 25px; }
        .sidebar a { display: block; color: #d1d5db; text-decoration: none; padding: 10px; margin-bottom: 8px; border-radius: 6px; }
        .sidebar a:hover { background: #374151; color: white; }
        .main-content { flex: 1; padding: 25px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 20px; }
        .table-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f9fafb; }
        .btn { padding: 9px 14px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; display: inline-block; margin-bottom: 20px; }
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
        <h1>Platform Analytics</h1>

        <a href="export_report.php" class="btn">Export Monthly Summary CSV</a>

        <div class="grid">
            <div class="table-box">
                <h2>Jobs by Category</h2>
                <table>
                    <tr><th>Category</th><th>Total Jobs</th></tr>
                    <?php while ($row = $jobsByCategory->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['total_jobs']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>Applications Over Time</h2>
                <table>
                    <tr><th>Date</th><th>Total Applications</th></tr>
                    <?php while ($row = $applicationsOverTime->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['application_date']); ?></td>
                            <td><?php echo $row['total_applications']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>Top Employers</h2>
                <table>
                    <tr><th>Employer</th><th>Applications Received</th></tr>
                    <?php while ($row = $topEmployers->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['employer_name']); ?></td>
                            <td><?php echo $row['total_applications']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>Most Active Recruiters</h2>
                <table>
                    <tr><th>Recruiter</th><th>Total Jobs Posted</th></tr>
                    <?php while ($row = $activeRecruiters->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['recruiter_name']); ?></td>
                            <td><?php echo $row['total_jobs']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>Popular Locations</h2>
                <table>
                    <tr><th>Location</th><th>Total Jobs</th></tr>
                    <?php while ($row = $popularLocations->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><?php echo $row['total_jobs']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>Popular Job Types</h2>
                <table>
                    <tr><th>Job Type</th><th>Total Jobs</th></tr>
                    <?php while ($row = $popularJobTypes->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['job_type']); ?></td>
                            <td><?php echo $row['total_jobs']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="table-box">
                <h2>User Growth by Role</h2>
                <table>
                    <tr><th>Month</th><th>Role</th><th>Total Users</th></tr>
                    <?php while ($row = $userGrowth->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['month']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo $row['total_users']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>