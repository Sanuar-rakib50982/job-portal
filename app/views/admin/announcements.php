<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "create") {
        $title = trim($_POST['title'] ?? "");
        $body = trim($_POST['body'] ?? "");

        if (empty($title) || empty($body)) {
            $error = "Title and body are required.";
        } else {
            if ($admin->createAnnouncement($title, $body, $_SESSION['user_id'])) {
                $message = "Announcement posted successfully.";
            } else {
                $error = "Failed to post announcement.";
            }
        }
    }

    if ($action === "delete") {
        $announcementId = isset($_POST['announcement_id']) ? (int)$_POST['announcement_id'] : 0;

        if ($announcementId > 0 && $admin->deleteAnnouncement($announcementId)) {
            $message = "Announcement deleted successfully.";
        } else {
            $error = "Failed to delete announcement.";
        }
    }
}

$announcements = $admin->getAnnouncements();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Announcements - Admin Panel</title>
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
        .form-box, .table-box { background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        input, textarea { width: 100%; padding: 10px; margin-top: 6px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 6px; }
        textarea { min-height: 120px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-danger { background: #dc2626; color: white; }
        .alert-success { padding: 12px; background: #dcfce7; color: #166534; border-radius: 6px; margin-bottom: 15px; }
        .alert-error { padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 6px; margin-bottom: 15px; }
        .small-text { font-size: 12px; color: #6b7280; }
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
        <h1>Platform Announcements</h1>

        <?php if (!empty($message)) { ?>
            <div class="alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="form-box">
            <h2>Post New Announcement</h2>

            <form method="POST" action="">
                <input type="hidden" name="action" value="create">

                <label>Title</label>
                <input type="text" name="title" required>

                <label>Announcement Body</label>
                <textarea name="body" required></textarea>

                <button type="submit" class="btn btn-primary">Post Announcement</button>
            </form>
        </div>

        <div class="table-box">
            <h2>Previous Announcements</h2>

            <table>
                <tr>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Posted By</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>

                <?php if ($announcements->num_rows > 0) { ?>
                    <?php while ($announcement = $announcements->fetch_assoc()) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($announcement['title']); ?></strong></td>
                            <td><?php echo nl2br(htmlspecialchars($announcement['body'])); ?></td>
                            <td><?php echo htmlspecialchars($announcement['admin_name']); ?></td>
                            <td><?php echo $announcement['created_at']; ?></td>
                            <td>
                                <form method="POST" action="" onsubmit="return confirm('Delete this announcement?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="announcement_id" value="<?php echo $announcement['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5">No announcements found.</td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>