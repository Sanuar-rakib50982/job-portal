<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$categories = $admin->getCategoriesWithJobCount();

$message = $_GET['message'] ?? "";
$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - Admin Panel</title>
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

        .form-box, .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        textarea {
            min-height: 90px;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-block;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
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

        .inline-form {
            display: inline-block;
            margin-left: 5px;
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
        <h1 class="page-title">Manage Job Categories</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>Add New Category</h2>

            <form method="POST" action="category_action.php">
                <input type="hidden" name="action" value="add">

                <label>Category Name</label>
                <input type="text" name="name" placeholder="Example: IT & Software" required>

                <label>Description</label>
                <textarea name="description" placeholder="Write short category description"></textarea>

                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <div class="table-box">
            <h2>All Categories</h2>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Total Jobs</th>
                    <th>Active Jobs</th>
                    <th>Actions</th>
                </tr>

                <?php if ($categories->num_rows > 0) { ?>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>

                            <td>
                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                            </td>

                            <td>
                                <?php 
                                if (!empty($category['description'])) {
                                    echo htmlspecialchars($category['description']);
                                } else {
                                    echo "<span class='small-text'>No description</span>";
                                }
                                ?>
                            </td>

                            <td><?php echo $category['total_jobs']; ?></td>
                            <td><?php echo $category['active_jobs'] ?? 0; ?></td>

                            <td>
                                <a class="btn btn-warning" href="category_edit.php?id=<?php echo $category['id']; ?>">Edit</a>

                                <form method="POST" action="category_action.php" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6">No categories found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</div>

</body>
</html>