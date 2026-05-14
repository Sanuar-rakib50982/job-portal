<?php
require_once "../../helpers/auth.php";
requireRole('employer');
?>

<h1>Employer Dashboard</h1>
<p>Welcome, <?php echo $_SESSION['name']; ?></p>

<a href="../../../logout.php">Logout</a>