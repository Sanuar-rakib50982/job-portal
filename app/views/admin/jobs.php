<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$search = $_GET['search'] ?? "";
$status = $_GET['status'] ?? "";
$categoryId = $_GET['category_id'] ?? "";
$featured = $_GET['featured'] ?? "";

$jobs = $admin->getAllJobs($search, $status, $categoryId, $featured);
$categories = $admin->getAllCategoriesSimple();

$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Jobs - Admin Panel</title>
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
            width: 231px;
            background: #111827;
            color: white;
            padding: 21px;
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

        input, select, textarea {
            width: 100%;
            padding: 9px;
            margin-top: 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        textarea {
            min-height: 70px;
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

        .btn-danger {
            background: #dc2626;
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

        .red {
            background: #dc2626;
        }

        .yellow {
            background: #f59e0b;
        }

        .blue {
            background: #2563eb;
        }

        .gray {
            background: #6b7280;
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

        .action-form {
            margin-bottom: 8px;
        }

        .featured-status {
            margin-bottom: 6px;
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
        <h1 class="page-title">Manage Jobs</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="filter-box">
            <h2>Search and Filter Jobs</h2>

            <form method="GET" action="">
                <div class="filter-grid">
                    <div>
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Title, location, employer, recruiter" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="active" <?php if ($status === 'active') echo 'selected'; ?>>Active</option>
                            <option value="closed" <?php if ($status === 'closed') echo 'selected'; ?>>Closed</option>
                            <option value="draft" <?php if ($status === 'draft') echo 'selected'; ?>>Draft</option>
                        </select>
                    </div>

                    <div>
                        <label>Category</label>
                        <select name="category_id">
                            <option value="">All Categories</option>
                            <?php while ($category = $categories->fetch_assoc()) { ?>
                                <option value="<?php echo $category['id']; ?>" <?php if ($categoryId == $category['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label>Featured</label>
                        <select name="featured">
                            <option value="">All</option>
                            <option value="1" <?php if ($featured === "1") echo 'selected'; ?>>Featured</option>
                            <option value="0" <?php if ($featured === "0") echo 'selected'; ?>>Not Featured</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="jobs.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <div class="table-box">
            <h2>All Job Posts</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Job Info</th>
                    <th>Posted By</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>

                <?php if ($jobs->num_rows > 0) { ?>
                    <?php while ($job = $jobs->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $job['id']; ?></td>

                            <td>
                                <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                                <span class="small-text">
                                    Category: <?php echo htmlspecialchars($job['category_name'] ?? 'No category'); ?><br>
                                    Location: <?php echo htmlspecialchars($job['location'] ?? 'Not specified'); ?><br>
                                    Type: <?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?> |
                                    Experience: <?php echo htmlspecialchars($job['experience_level'] ?? 'N/A'); ?>
                                </span>
                            </td>

                            <td>
                                <?php if (!empty($job['employer_name'])) { ?>
                                    <strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name']); ?><br>
                                <?php } else { ?>
                                    <strong>Employer:</strong> <span class="small-text">N/A</span><br>
                                <?php } ?>

                                <?php if (!empty($job['recruiter_name'])) { ?>
                                    <strong>Recruiter:</strong> <?php echo htmlspecialchars($job['recruiter_name']); ?>
                                <?php } else { ?>
                                    <strong>Recruiter:</strong> <span class="small-text">N/A</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?> -
                                <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?>
                            </td>

                            <td>
                                <?php if ($job['status'] === 'active') { ?>
                                    <span class="badge green">Active</span>
                                <?php } elseif ($job['status'] === 'closed') { ?>
                                    <span class="badge red">Closed</span>
                                <?php } else { ?>
                                    <span class="badge gray">Draft</span>
                                <?php } ?>
                            </td>

                            <td>
                                <div class="featured-status" id="featured-status-<?php echo $job['id']; ?>">
                                    <?php if ($job['is_featured']) { ?>
                                        <span class="badge yellow">Featured</span>
                                    <?php } else { ?>
                                        <span class="badge gray">Not Featured</span>
                                    <?php } ?>
                                </div>

                                <button 
                                    class="btn btn-warning toggle-featured-btn" 
                                    data-job-id="<?php echo $job['id']; ?>">
                                    Toggle Featured
                                </button>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($job['deadline'] ?? 'No deadline'); ?><br>
                                <span class="small-text">Posted: <?php echo $job['created_at']; ?></span>
                            </td>

                            <td>
                                <?php if ($job['status'] !== 'closed') { ?>
                                    <form method="POST" action="job_action.php" class="action-form">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <input type="hidden" name="action" value="close">
                                        <textarea name="reason" placeholder="Reason for closing/removing job" required></textarea>
                                        <button type="submit" class="btn btn-danger">Close / Remove</button>
                                    </form>
                                <?php } ?>

                                <?php if ($job['status'] === 'closed') { ?>
                                    <form method="POST" action="job_action.php" class="action-form">
                                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-success">Reactivate</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">No jobs found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

<script src="../../../public/js/admin.js"></script>
</body>
</html>