<?php
require_once "../../helpers/auth.php";
requireRole('admin');
?>

<h1>Admin Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="../../../logout.php">Logout</a>