<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');
?>

<h1>Recruiter Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="../../../logout.php">Logout</a>