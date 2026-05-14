<?php
require_once "../../helpers/auth.php";
requireRole('seeker');
?>

<h1>Job Seeker Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="../../../logout.php">Logout</a>