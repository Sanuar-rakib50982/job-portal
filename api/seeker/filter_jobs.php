<?php
require_once "../../app/helpers/auth.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seeker') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

require_once "../../app/config/database.php";
require_once "../../app/controllers/SeekerController.php";

$seeker = new SeekerController($conn);

$keyword = trim($_GET['keyword'] ?? "");
$categoryId = trim($_GET['category_id'] ?? "");
$location = trim($_GET['location'] ?? "");
$jobType = trim($_GET['job_type'] ?? "");
$experienceLevel = trim($_GET['experience_level'] ?? "");
$salaryMin = trim($_GET['salary_min'] ?? "");
$salaryMax = trim($_GET['salary_max'] ?? "");

$jobsResult = $seeker->getFilteredJobs(
    $keyword,
    $categoryId,
    $location,
    $jobType,
    $experienceLevel,
    $salaryMin,
    $salaryMax
);

$jobs = [];

$seekerId = $_SESSION['user_id'];

while ($job = $jobsResult->fetch_assoc()) {
    $jobs[] = [
        "id" => $job['id'],
        "title" => $job['title'],
        "category_name" => $job['category_name'],
        "employer_name" => $job['employer_name'],
        "recruiter_name" => $job['recruiter_name'],
        "location" => $job['location'],
        "job_type" => $job['job_type'],
        "experience_level" => $job['experience_level'],
        "salary_min" => $job['salary_min'],
        "salary_max" => $job['salary_max'],
        "deadline" => $job['deadline'],
        "is_featured" => $job['is_featured'],
        "is_saved" => $seeker->isJobSaved($job['id'], $seekerId)
    ];
}

echo json_encode([
    "success" => true,
    "jobs" => $jobs
]);
exit;