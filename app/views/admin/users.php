<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$search = $_GET['search'] ?? "";
$role = $_GET['role'] ?? "";
$status = $_GET['status'] ?? "";
$verified = $_GET['verified'] ?? "";

$users = $admin->getUsers($search, $role, $status, $verified);

$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin Panel</title>
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

        input, select, textarea {
            width: 100%;
            padding: 9px;
            margin-top: 6px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        button, .btn {
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

        .action-form {
            margin-bottom: 8px;
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
        <h1 class="page-title">Manage Users</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="filter-box">
            <h2>Search and Filter Users</h2>

            <form method="GET" action="">
                <div class="filter-grid">
                    <div>
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Name, email, phone" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div>
                        <label>Role</label>
                        <select name="role">
                            <option value="">All Roles</option>
                            <option value="seeker" <?php if ($role === 'seeker') echo 'selected'; ?>>Job Seeker</option>
                            <option value="employer" <?php if ($role === 'employer') echo 'selected'; ?>>Employer</option>
                            <option value="recruiter" <?php if ($role === 'recruiter') echo 'selected'; ?>>Recruiter</option>
                            <option value="admin" <?php if ($role === 'admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>

                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">All</option>
                            <option value="1" <?php if ($status === "1") echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if ($status === "0") echo 'selected'; ?>>Suspended</option>
                        </select>
                    </div>

                    <div>
                        <label>Verification</label>
                        <select name="verified">
                            <option value="">All</option>
                            <option value="1" <?php if ($verified === "1") echo 'selected'; ?>>Verified</option>
                            <option value="0" <?php if ($verified === "0") echo 'selected'; ?>>Pending / Rejected</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="users.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>

        <div class="table-box">
            <h2>All Users</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>User Info</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Verification</th>
                    <th>Verification Note</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>

                <?php if ($users->num_rows > 0) { ?>
                    <?php while ($user = $users->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>

                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                                <?php echo htmlspecialchars($user['email']); ?><br>
                                <span class="small-text"><?php echo htmlspecialchars($user['phone']); ?></span>
                            </td>

                            <td>
                                <span class="badge blue">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>

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
                                    <span class="badge yellow">Pending / Rejected</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?php 
                                if (!empty($user['verification_note'])) {
                                    echo htmlspecialchars($user['verification_note']);
                                } else {
                                    echo "<span class='small-text'>No note</span>";
                                }
                                ?>
                            </td>

                            <td><?php echo $user['created_at']; ?></td>

                            <td>
                                <?php if ($user['role'] === 'employer' || $user['role'] === 'recruiter') { ?>
                                    <?php if (!$user['is_verified']) { ?>
                                        <form method="POST" action="user_action.php" class="action-form">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </form>
                                    <?php } ?>

                                    <form method="POST" action="user_action.php" class="action-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <textarea name="reason" placeholder="Reject reason" required></textarea>
                                        <button type="submit" class="btn btn-warning">Reject</button>
                                    </form>
                                <?php } ?>

                                <?php if ($user['role'] !== 'admin') { ?>
                                    <?php if ($user['is_active']) { ?>
                                        <form method="POST" action="user_action.php" class="action-form">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="suspend">
                                            <textarea name="reason" placeholder="Suspend reason" required></textarea>
                                            <button type="submit" class="btn btn-danger">Suspend</button>
                                        </form>
                                    <?php } else { ?>
                                        <form method="POST" action="user_action.php" class="action-form">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="reactivate">
                                            <button type="submit" class="btn btn-success">Reactivate</button>
                                        </form>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="small-text">Admin protected</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="8">No users found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>