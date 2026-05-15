<?php
require_once "../../helpers/auth.php";
requireRole('seeker');

require_once "../../config/database.php";
require_once "../../controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$categories = $seeker->getCategories();
$jobs = $seeker->getFilteredJobs();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Jobs - Job Seeker</title>
    <link rel="stylesheet" href="../../../public/css/seeker.css">
</head>
<body>

<div class="seeker-wrapper">
    <aside class="sidebar">
        <h2>Job Seeker</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="jobs.php">Browse Jobs</a>
        <a href="applications.php">My Applications</a>
        <a href="saved_jobs.php">Saved Jobs</a>
        <a href="alerts.php">Job Alerts</a>
        <a href="outreach.php">Recruiter Outreach</a>
        <a href="messages.php">Messages</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="../../../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Browse Jobs</h1>
        <p>Search and filter active job opportunities.</p>

        <div class="form-box">
            <h2>Search and Filter Jobs</h2>

            <form id="jobFilterForm">
                <label>Keyword</label>
                <input type="text" name="keyword" id="keyword" placeholder="Search title, description, company">

                <label>Category</label>
                <select name="category_id" id="category_id">
                    <option value="">All Categories</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Location</label>
                <input type="text" name="location" id="location" placeholder="Example: Dhaka">

                <label>Job Type</label>
                <select name="job_type" id="job_type">
                    <option value="">All Types</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="remote">Remote</option>
                    <option value="contract">Contract</option>
                </select>

                <label>Experience Level</label>
                <select name="experience_level" id="experience_level">
                    <option value="">All Levels</option>
                    <option value="entry">Entry</option>
                    <option value="mid">Mid</option>
                    <option value="senior">Senior</option>
                </select>

                <label>Minimum Salary</label>
                <input type="number" name="salary_min" id="salary_min" placeholder="Example: 20000">

                <label>Maximum Salary</label>
                <input type="number" name="salary_max" id="salary_max" placeholder="Example: 60000">

                <button type="submit">Search Jobs</button>
                <button type="button" class="btn-secondary" id="resetFilters">Reset</button>
            </form>
        </div>

        <div class="table-box">
            <h2>Available Jobs</h2>

            <div id="jobResults">
                <?php if ($jobs->num_rows > 0) { ?>
                    <?php while ($job = $jobs->fetch_assoc()) { ?>
                        <div class="job-card">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>

                            <p>
                                <strong>Category:</strong> <?php echo htmlspecialchars($job['category_name'] ?? 'N/A'); ?><br>
                                <strong>Employer:</strong> <?php echo htmlspecialchars($job['employer_name'] ?? 'N/A'); ?><br>
                                <strong>Recruiter:</strong> <?php echo htmlspecialchars($job['recruiter_name'] ?? 'N/A'); ?><br>
                                <strong>Location:</strong> <?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?><br>
                                <strong>Type:</strong> <?php echo htmlspecialchars($job['job_type'] ?? 'N/A'); ?><br>
                                <strong>Experience:</strong> <?php echo htmlspecialchars($job['experience_level'] ?? 'N/A'); ?><br>
                                <strong>Salary:</strong> 
                                <?php echo htmlspecialchars($job['salary_min'] ?? '0'); ?> - 
                                <?php echo htmlspecialchars($job['salary_max'] ?? '0'); ?><br>
                                <strong>Deadline:</strong> <?php echo htmlspecialchars($job['deadline'] ?? 'N/A'); ?>
                            </p>

                            <?php if ($job['is_featured']) { ?>
                                <span class="badge yellow">Featured</span>
                            <?php } ?>

                            <br><br>

                            <a class="btn" href="job_details.php?id=<?php echo $job['id']; ?>">View Details</a>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No active jobs found.</p>
                <?php } ?>
            </div>
        </div>
    </main>
</div>

<script src="../../../public/js/seeker.js"></script>
</body>
</html>