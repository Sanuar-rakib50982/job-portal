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
public function getProfileResume($userId) {
    $sql = "SELECT resume_path FROM seeker_profiles WHERE user_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return $result['resume_path'] ?? null;
}

public function hasAlreadyApplied($jobId, $seekerId) {
    $sql = "SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $jobId, $seekerId);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

public function applyToJob($jobId, $seekerId, $recruiterId, $coverLetter, $resumePath) {
    if ($recruiterId === null || $recruiterId === "" || $recruiterId == 0) {
        $sql = "INSERT INTO applications (job_id, seeker_id, recruiter_id, cover_letter, resume_path)
                VALUES (?, ?, NULL, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiss", $jobId, $seekerId, $coverLetter, $resumePath);
    } else {
        $sql = "INSERT INTO applications (job_id, seeker_id, recruiter_id, cover_letter, resume_path)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiss", $jobId, $seekerId, $recruiterId, $coverLetter, $resumePath);
    }

    return $stmt->execute();
}

public function getMyApplications($seekerId) {
    $sql = "SELECT applications.id, applications.cover_letter, applications.resume_path,
                   applications.status, applications.applied_at,
                   jobs.title, jobs.location, jobs.job_type, jobs.experience_level,
                   jobs.salary_min, jobs.salary_max,
                   employer.name AS employer_name,
                   recruiter.name AS recruiter_name
            FROM applications
            INNER JOIN jobs ON applications.job_id = jobs.id
            LEFT JOIN users AS employer ON jobs.employer_id = employer.id
            LEFT JOIN users AS recruiter ON applications.recruiter_id = recruiter.id
            WHERE applications.seeker_id = ?
            ORDER BY applications.applied_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();

    return $stmt->get_result();
}

public function getApplicationById($applicationId, $seekerId) {
    $sql = "SELECT applications.*, jobs.title
            FROM applications
            INNER JOIN jobs ON applications.job_id = jobs.id
            WHERE applications.id = ? AND applications.seeker_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $applicationId, $seekerId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

public function withdrawApplication($applicationId, $seekerId) {
    $application = $this->getApplicationById($applicationId, $seekerId);

    if (!$application) {
        return "not_found";
    }

    if ($application['status'] !== 'submitted') {
        return "not_allowed";
    }

    $sql = "UPDATE applications 
            SET status = 'withdrawn'
            WHERE id = ? AND seeker_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $applicationId, $seekerId);

    return $stmt->execute();
}
public function isJobSaved($jobId, $seekerId) {
    $sql = "SELECT id FROM saved_jobs WHERE job_id = ? AND user_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $jobId, $seekerId);
    $stmt->execute();

    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

public function saveJob($jobId, $seekerId) {
    if ($this->isJobSaved($jobId, $seekerId)) {
        return "already_saved";
    }

    $sql = "INSERT INTO saved_jobs (job_id, user_id) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $jobId, $seekerId);

    return $stmt->execute();
}

public function unsaveJob($jobId, $seekerId) {
    $sql = "DELETE FROM saved_jobs WHERE job_id = ? AND user_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $jobId, $seekerId);

    return $stmt->execute();
}

public function toggleSavedJob($jobId, $seekerId) {
    if ($this->isJobSaved($jobId, $seekerId)) {
        if ($this->unsaveJob($jobId, $seekerId)) {
            return "unsaved";
        }

        return false;
    }

    if ($this->saveJob($jobId, $seekerId)) {
        return "saved";
    }

    return false;
}

public function getSavedJobs($seekerId) {
    $sql = "SELECT saved_jobs.id AS saved_id, saved_jobs.saved_at,
                   jobs.id AS job_id, jobs.title, jobs.description,
                   jobs.salary_min, jobs.salary_max, jobs.location,
                   jobs.job_type, jobs.experience_level, jobs.deadline,
                   jobs.is_featured, jobs.created_at,
                   categories.name AS category_name,
                   employer.name AS employer_name,
                   recruiter.name AS recruiter_name
            FROM saved_jobs
            INNER JOIN jobs ON saved_jobs.job_id = jobs.id
            LEFT JOIN categories ON jobs.category_id = categories.id
            LEFT JOIN users AS employer ON jobs.employer_id = employer.id
            LEFT JOIN users AS recruiter ON jobs.recruiter_id = recruiter.id
            WHERE saved_jobs.user_id = ?
            ORDER BY saved_jobs.saved_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();

    return $stmt->get_result();
}
public function createJobAlert($seekerId, $keyword, $categoryId, $location, $jobType) {
    if ($categoryId === "" || $categoryId == 0) {
        $categoryId = null;
    }

    if ($categoryId === null) {
        $sql = "INSERT INTO job_alerts (seeker_id, keyword, category_id, location, job_type)
                VALUES (?, ?, NULL, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $seekerId, $keyword, $location, $jobType);
    } else {
        $sql = "INSERT INTO job_alerts (seeker_id, keyword, category_id, location, job_type)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isiss", $seekerId, $keyword, $categoryId, $location, $jobType);
    }

    return $stmt->execute();
}

public function getJobAlerts($seekerId) {
    $sql = "SELECT job_alerts.*, categories.name AS category_name
            FROM job_alerts
            LEFT JOIN categories ON job_alerts.category_id = categories.id
            WHERE job_alerts.seeker_id = ?
            ORDER BY job_alerts.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();

    return $stmt->get_result();
}

public function deleteJobAlert($alertId, $seekerId) {
    $sql = "DELETE FROM job_alerts WHERE id = ? AND seeker_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $alertId, $seekerId);

    return $stmt->execute();
}

public function getRecruiterOutreach($seekerId) {
    $sql = "SELECT recruiter_outreach.id, recruiter_outreach.message,
                   recruiter_outreach.status, recruiter_outreach.sent_at,
                   recruiter.name AS recruiter_name,
                   recruiter.email AS recruiter_email,
                   jobs.title AS job_title,
                   jobs.id AS job_id
            FROM recruiter_outreach
            INNER JOIN users AS recruiter ON recruiter_outreach.recruiter_id = recruiter.id
            LEFT JOIN jobs ON recruiter_outreach.job_id = jobs.id
            WHERE recruiter_outreach.seeker_id = ?
            ORDER BY recruiter_outreach.sent_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $seekerId);
    $stmt->execute();

    return $stmt->get_result();
}

public function updateOutreachStatus($outreachId, $seekerId, $status) {
    $allowedStatuses = ['sent', 'read', 'responded'];

    if (!in_array($status, $allowedStatuses)) {
        return false;
    }

    $sql = "UPDATE recruiter_outreach
            SET status = ?
            WHERE id = ? AND seeker_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sii", $status, $outreachId, $seekerId);

    return $stmt->execute();
}

public function getReceivedMessages($userId) {
    $sql = "SELECT messages.id, messages.body, messages.sent_at, messages.is_read,
                   messages.application_id,
                   sender.name AS sender_name,
                   sender.email AS sender_email,
                   sender.role AS sender_role,
                   sender.id AS sender_id,
                   jobs.title AS job_title
            FROM messages
            INNER JOIN users AS sender ON messages.sender_id = sender.id
            LEFT JOIN applications ON messages.application_id = applications.id
            LEFT JOIN jobs ON applications.job_id = jobs.id
            WHERE messages.recipient_id = ?
            ORDER BY messages.sent_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    return $stmt->get_result();
}

public function markMessageAsRead($messageId, $userId) {
    $sql = "UPDATE messages 
            SET is_read = 1
            WHERE id = ? AND recipient_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $messageId, $userId);

    return $stmt->execute();
}

public function sendMessage($senderId, $recipientId, $applicationId, $body) {
    if ($applicationId === null || $applicationId === "" || $applicationId == 0) {
        $sql = "INSERT INTO messages (sender_id, recipient_id, application_id, body, sent_at, is_read)
                VALUES (?, ?, NULL, ?, NOW(), 0)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $senderId, $recipientId, $body);
    } else {
        $sql = "INSERT INTO messages (sender_id, recipient_id, application_id, body, sent_at, is_read)
                VALUES (?, ?, ?, ?, NOW(), 0)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $senderId, $recipientId, $applicationId, $body);
    }

    return $stmt->execute();
}
public function getComplaintSubjectUsers() {
    $sql = "SELECT id, name, email, role 
            FROM users 
            WHERE role IN ('employer', 'recruiter', 'admin')
            ORDER BY role ASC, name ASC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function submitComplaint($submitterId, $subjectId, $description) {
    if ($subjectId === null || $subjectId === "" || $subjectId == 0) {
        $sql = "INSERT INTO complaints (submitter_id, subject_id, description, status)
                VALUES (?, NULL, ?, 'open')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $submitterId, $description);
    } else {
        $sql = "INSERT INTO complaints (submitter_id, subject_id, description, status)
                VALUES (?, ?, ?, 'open')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $submitterId, $subjectId, $description);
    }

    return $stmt->execute();
}

public function getMyComplaints($submitterId) {
    $sql = "SELECT complaints.id, complaints.description, complaints.status,
                   complaints.admin_note, complaints.created_at,
                   subject.name AS subject_name,
                   subject.email AS subject_email,
                   subject.role AS subject_role
            FROM complaints
            LEFT JOIN users AS subject ON complaints.subject_id = subject.id
            WHERE complaints.submitter_id = ?
            ORDER BY complaints.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $submitterId);
    $stmt->execute();

    return $stmt->get_result();
}

}