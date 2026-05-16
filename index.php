<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: app/views/admin/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'seeker') {
        header("Location: app/views/seeker/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'employer') {
        header("Location: app/views/employer/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'recruiter') {
        header("Location: app/views/recruiter/dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>CareerBridge Job Portal</title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body class="auth-page">

<nav class="navbar">
    <a href="index.php" class="brand">
        <span class="brand-mark">CB</span>
        <span>CareerBridge</span>
    </a>

    <div class="nav-links">
        <a href="login.php">Login</a>
        <a href="register.php" class="nav-btn">Create Account</a>
    </div>
</nav>

<section class="hero">
    <div>
        <span class="hero-badge">Smart Job Portal for Modern Careers</span>
        <h1>Build your future with <span>CareerBridge</span></h1>
        <p>
            CareerBridge connects job seekers, employers, and recruiters in one organized platform.
            Search jobs, post vacancies, manage applications, communicate with candidates, and build
            professional career opportunities with confidence.
        </p>

        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-outline">Login Now</a>
        </div>
    </div>

    <div class="hero-card">
        <div class="stat-grid">
            <div class="stat-card">
                <h3>4</h3>
                <p>User roles: Admin, Seeker, Employer, Recruiter</p>
            </div>

            <div class="stat-card">
                <h3>24/7</h3>
                <p>Online access for job posting and applications</p>
            </div>

            <div class="stat-card">
                <h3>Fast</h3>
                <p>Simple application tracking and status updates</p>
            </div>

            <div class="stat-card">
                <h3>Secure</h3>
                <p>Role-based dashboard access and protected pages</p>
            </div>
        </div>
    </div>
</section>

<section class="features">
    <div class="section-title">
        <h2>Everything in one job portal</h2>
        <p>Designed for academic project demonstration and real-world job portal workflow.</p>
    </div>

    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">JS</div>
            <h3>For Job Seekers</h3>
            <p>Create profile, upload resume, browse jobs, save jobs, apply, receive outreach, and send messages.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">EM</div>
            <h3>For Employers</h3>
            <p>Manage company profile, post jobs, review applications, update status, and contact applicants.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">RC</div>
            <h3>For Recruiters</h3>
            <p>Post recruiter jobs, manage client companies, search seekers, send outreach, and handle messages.</p>
        </div>
    </div>
</section>

<footer class="footer">
    © <?php echo date('Y'); ?> CareerBridge Job Portal. Web Technologies Academic Project.
</footer>

</body>
</html>