<?php
require_once "../../app/helpers/auth.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'recruiter') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

require_once "../../app/config/database.php";
require_once "../../app/controllers/RecruiterController.php";

$recruiter = new RecruiterController($conn);

$keyword = trim($_GET['keyword'] ?? "");
$skills = trim($_GET['skills'] ?? "");
$location = trim($_GET['location'] ?? "");
$experience = trim($_GET['experience'] ?? "");

$seekersResult = $recruiter->searchSeekers($keyword, $skills, $location, $experience);

$seekers = [];

while ($seeker = $seekersResult->fetch_assoc()) {
    $seekers[] = [
        "id" => $seeker['id'],
        "name" => $seeker['name'],
        "email" => $seeker['email'],
        "phone" => $seeker['phone'],
        "profile_pic" => $seeker['profile_pic'],
        "headline" => $seeker['headline'],
        "summary" => $seeker['summary'],
        "skills" => $seeker['skills'],
        "years_experience" => $seeker['years_experience'],
        "education_level" => $seeker['education_level'],
        "preferred_location" => $seeker['preferred_location'],
        "resume_path" => $seeker['resume_path']
    ];
}

echo json_encode([
    "success" => true,
    "seekers" => $seekers
]);
exit;