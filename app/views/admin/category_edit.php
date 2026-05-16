<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($categoryId <= 0) {
    header("Location: categories.php?error=Invalid category ID");
    exit;
}

$category = $admin->getCategoryById($categoryId);

if (!$category) {
    header("Location: categories.php?error=Category not found");
    exit;
}

$error = $_GET['error'] ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category - Admin Panel</title>
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

        .form-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 650px;
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
            min-height: 110px;
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

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .alert-error {
            padding: 12px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 6px;
            margin-bottom: 15px;
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
        <h1>Edit Category</h1>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <form method="POST" action="category_action.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">

                <label>Category Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>

                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($category['description']); ?></textarea>

                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="categories.php" class="btn btn-secondary">Back</a>
            </form>
        </div>

    </div>
</div>

</body>
</html>