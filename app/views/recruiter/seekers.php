<?php
require_once "../../helpers/auth.php";
requireRole('recruiter');

require_once "../../config/database.php";
require_once "../../controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$seekers = $recruiter->searchSeekers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Seekers - Recruiter</title>
    <link rel="stylesheet" href="../../../public/css/recruiter.css">
</head>
<body>

<div class="recruiter-wrapper">
    <aside class="sidebar">
        <h2>Recruiter Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">My Profile</a>
        <a href="clients.php">Client Companies</a>
        <a href="jobs.php">Manage Jobs</a>
        <a href="applications.php">Applications</a>
        <a href="seekers.php">Search Seekers</a>
        <a href="outreach.php">Outreach</a>
        <a href="messages.php">Messages</a>
        <a href="complaint.php">Submit Complaint</a>
        <a href="../../../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <h1>Search Job Seekers</h1>
        <p>Find suitable candidates using keyword, skills, location, and experience filters.</p>

        <div class="form-box">
            <h2>Search Filters</h2>

            <form id="seekerSearchForm">
                <label>Keyword</label>
                <input type="text" name="keyword" id="seekerKeyword" placeholder="Search name, email, headline, summary">

                <label>Skills</label>
                <input type="text" name="skills" id="seekerSkills" placeholder="Example: PHP, MySQL, JavaScript">

                <label>Preferred Location</label>
                <input type="text" name="location" id="seekerLocation" placeholder="Example: Dhaka">

                <label>Minimum Experience Years</label>
                <input type="number" name="experience" id="seekerExperience" min="0" placeholder="Example: 1">

                <button type="submit">Search Seekers</button>
                <button type="button" class="btn-secondary" id="resetSeekerSearch">Reset</button>
            </form>
        </div>

        <div class="table-box">
            <h2>Seeker Results</h2>

            <div id="seekerResults">
                <?php if ($seekers->num_rows > 0) { ?>
                    <?php while ($seeker = $seekers->fetch_assoc()) { ?>
                        <div class="job-card">
                            <h3><?php echo htmlspecialchars($seeker['name']); ?></h3>

                            <p>
                                <strong>Email:</strong> <?php echo htmlspecialchars($seeker['email']); ?><br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($seeker['phone'] ?? 'N/A'); ?><br>
                                <strong>Headline:</strong> <?php echo htmlspecialchars($seeker['headline'] ?? 'No headline'); ?><br>
                                <strong>Skills:</strong> <?php echo htmlspecialchars($seeker['skills'] ?? 'N/A'); ?><br>
                                <strong>Experience:</strong> <?php echo htmlspecialchars($seeker['years_experience'] ?? '0'); ?> years<br>
                                <strong>Education:</strong> <?php echo htmlspecialchars($seeker['education_level'] ?? 'N/A'); ?><br>
                                <strong>Preferred Location:</strong> <?php echo htmlspecialchars($seeker['preferred_location'] ?? 'N/A'); ?>
                            </p>

                            <a class="btn" href="seeker_profile.php?id=<?php echo $seeker['id']; ?>">View Profile</a>

                            <?php if (!empty($seeker['resume_path'])) { ?>
                                <a class="btn btn-secondary" href="../../../<?php echo htmlspecialchars($seeker['resume_path']); ?>" target="_blank">View Resume</a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>No seekers found.</p>
                <?php } ?>
            </div>
        </div>
    </main>
</div>

<script src="../../../public/js/recruiter.js"></script>
</body>
</html>