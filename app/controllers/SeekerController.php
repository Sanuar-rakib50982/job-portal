<?php

class SeekerController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getProfile($userId) {
        $sql = "SELECT seeker_profiles.*, users.profile_pic
                FROM seeker_profiles
                INNER JOIN users ON seeker_profiles.user_id = users.id
                WHERE seeker_profiles.user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function saveProfile($userId, $data, $resumePath = null) {
        $existingProfile = $this->getProfile($userId);

        if ($existingProfile) {
            if ($resumePath === null) {
                $resumePath = $existingProfile['resume_path'];
            }

            $sql = "UPDATE seeker_profiles
                    SET headline = ?, summary = ?, skills = ?, years_experience = ?,
                        education_level = ?, current_salary = ?, expected_salary = ?,
                        preferred_location = ?, resume_path = ?
                    WHERE user_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssisdsssi",
                $data['headline'],
                $data['summary'],
                $data['skills'],
                $data['years_experience'],
                $data['education_level'],
                $data['current_salary'],
                $data['expected_salary'],
                $data['preferred_location'],
                $resumePath,
                $userId
            );

            return $stmt->execute();
        } else {
            $sql = "INSERT INTO seeker_profiles
                    (user_id, headline, summary, skills, years_experience, education_level,
                     current_salary, expected_salary, preferred_location, resume_path)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "isssisdsss",
                $userId,
                $data['headline'],
                $data['summary'],
                $data['skills'],
                $data['years_experience'],
                $data['education_level'],
                $data['current_salary'],
                $data['expected_salary'],
                $data['preferred_location'],
                $resumePath
            );

            return $stmt->execute();
        }
    }

    public function updateProfilePicture($userId, $profilePicPath) {
        $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $profilePicPath, $userId);

        return $stmt->execute();
    }
    public function getCategories() {
    $sql = "SELECT id, name FROM categories ORDER BY name ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getFilteredJobs($keyword = "", $categoryId = "", $location = "", $jobType = "", $experienceLevel = "", $salaryMin = "", $salaryMax = "") {
    $sql = "SELECT jobs.id, jobs.title, jobs.description, jobs.salary_min, jobs.salary_max,
                   jobs.location, jobs.job_type, jobs.experience_level, jobs.deadline,
                   jobs.is_featured, jobs.created_at,
                   categories.name AS category_name,
                   employer.name AS employer_name,
                   recruiter.name AS recruiter_name
            FROM jobs
            LEFT JOIN categories ON jobs.category_id = categories.id
            LEFT JOIN users AS employer ON jobs.employer_id = employer.id
            LEFT JOIN users AS recruiter ON jobs.recruiter_id = recruiter.id
            WHERE jobs.status = 'active'";

    $params = [];
    $types = "";

    if (!empty($keyword)) {
        $sql .= " AND (
                    jobs.title LIKE ?
                    OR jobs.description LIKE ?
                    OR employer.name LIKE ?
                    OR recruiter.name LIKE ?
                 )";
        $searchTerm = "%" . $keyword . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ssss";
    }

    if (!empty($categoryId)) {
        $sql .= " AND jobs.category_id = ?";
        $params[] = (int)$categoryId;
        $types .= "i";
    }

    if (!empty($location)) {
        $sql .= " AND jobs.location LIKE ?";
        $locationTerm = "%" . $location . "%";
        $params[] = $locationTerm;
        $types .= "s";
    }

    if (!empty($jobType)) {
        $sql .= " AND jobs.job_type = ?";
        $params[] = $jobType;
        $types .= "s";
    }

    if (!empty($experienceLevel)) {
        $sql .= " AND jobs.experience_level = ?";
        $params[] = $experienceLevel;
        $types .= "s";
    }

    if ($salaryMin !== "") {
        $sql .= " AND jobs.salary_max >= ?";
        $params[] = (float)$salaryMin;
        $types .= "d";
    }

    if ($salaryMax !== "") {
        $sql .= " AND jobs.salary_min <= ?";
        $params[] = (float)$salaryMax;
        $types .= "d";
    }

    $sql .= " ORDER BY jobs.is_featured DESC, jobs.created_at DESC";

    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    return $stmt->get_result();
}

public function getJobById($jobId) {
    $sql = "SELECT jobs.*, 
                   categories.name AS category_name,
                   employer.name AS employer_name,
                   employer.email AS employer_email,
                   employer.phone AS employer_phone,
                   recruiter.name AS recruiter_name
            FROM jobs
            LEFT JOIN categories ON jobs.category_id = categories.id
            LEFT JOIN users AS employer ON jobs.employer_id = employer.id
            LEFT JOIN users AS recruiter ON jobs.recruiter_id = recruiter.id
            WHERE jobs.id = ? AND jobs.status = 'active'";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}
}