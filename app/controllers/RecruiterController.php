<?php

class RecruiterController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getProfile($recruiterId) {
        $sql = "SELECT * FROM recruiter_profiles WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $recruiterId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function saveProfile($recruiterId, $data) {
        $profile = $this->getProfile($recruiterId);

        if ($profile) {
            $sql = "UPDATE recruiter_profiles
                    SET agency_name = ?, specialization = ?, description = ?, website = ?
                    WHERE user_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssssi",
                $data['agency_name'],
                $data['specialization'],
                $data['description'],
                $data['website'],
                $recruiterId
            );

            return $stmt->execute();
        }

        $sql = "INSERT INTO recruiter_profiles
                (user_id, agency_name, specialization, description, website)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "issss",
            $recruiterId,
            $data['agency_name'],
            $data['specialization'],
            $data['description'],
            $data['website']
        );

        return $stmt->execute();
    }

    public function getEmployersForClient() {
        $sql = "SELECT id, name, email, phone 
                FROM users 
                WHERE role = 'employer' 
                AND is_active = 1
                ORDER BY name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

 public function getClients($recruiterId) {
    $sql = "SELECT recruiter_clients.id,
                   recruiter_clients.recruiter_id,
                   recruiter_clients.employer_id,
                   recruiter_clients.company_name_override,
                   recruiter_clients.added_at,
                   users.name AS employer_name,
                   users.email AS employer_email,
                   users.phone AS employer_phone
            FROM recruiter_clients
            INNER JOIN users ON recruiter_clients.employer_id = users.id
            WHERE recruiter_clients.recruiter_id = ?
            ORDER BY recruiter_clients.added_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $recruiterId);
    $stmt->execute();

    return $stmt->get_result();
}

    public function getClientById($clientId, $recruiterId) {
        $sql = "SELECT recruiter_clients.id,
                       recruiter_clients.recruiter_id,
                       recruiter_clients.employer_id,
                       recruiter_clients.company_name_override,
                       recruiter_clients.added_at,
                       users.name AS employer_name,
                       users.email AS employer_email,
                       users.phone AS employer_phone
                FROM recruiter_clients
                INNER JOIN users ON recruiter_clients.employer_id = users.id
                WHERE recruiter_clients.id = ?
                AND recruiter_clients.recruiter_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $clientId, $recruiterId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function isClientAlreadyAdded($recruiterId, $employerId) {
        $sql = "SELECT id FROM recruiter_clients 
                WHERE recruiter_id = ? AND employer_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $recruiterId, $employerId);
        $stmt->execute();

        return $stmt->get_result()->num_rows > 0;
    }

    public function createClient($recruiterId, $employerId, $companyNameOverride) {
        if ($this->isClientAlreadyAdded($recruiterId, $employerId)) {
            return "already_exists";
        }

        $sql = "INSERT INTO recruiter_clients 
                (recruiter_id, employer_id, company_name_override)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iis", $recruiterId, $employerId, $companyNameOverride);

        return $stmt->execute();
    }

    public function updateClient($clientId, $recruiterId, $companyNameOverride) {
        $sql = "UPDATE recruiter_clients
                SET company_name_override = ?
                WHERE id = ? AND recruiter_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $companyNameOverride, $clientId, $recruiterId);

        return $stmt->execute();
    }

    public function deleteClient($clientId, $recruiterId) {
        $sql = "DELETE FROM recruiter_clients 
                WHERE id = ? AND recruiter_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $clientId, $recruiterId);

        return $stmt->execute();
    }

    public function getCategories() {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getRecruiterJobs($recruiterId) {
        $sql = "SELECT jobs.*, categories.name AS category_name
                FROM jobs
                LEFT JOIN categories ON jobs.category_id = categories.id
                WHERE jobs.recruiter_id = ?
                ORDER BY jobs.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $recruiterId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getJobById($jobId, $recruiterId) {
        $sql = "SELECT * FROM jobs WHERE id = ? AND recruiter_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $jobId, $recruiterId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function createJob($recruiterId, $data) {
        $sql = "INSERT INTO jobs
                (employer_id, recruiter_id, category_id, title, description, requirements, benefits,
                 salary_min, salary_max, location, job_type, experience_level, deadline, status)
                VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "iissssddssss",
            $recruiterId,
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['requirements'],
            $data['benefits'],
            $data['salary_min'],
            $data['salary_max'],
            $data['location'],
            $data['job_type'],
            $data['experience_level'],
            $data['deadline']
        );

        return $stmt->execute();
    }

    public function updateJob($jobId, $recruiterId, $data) {
        $sql = "UPDATE jobs
                SET category_id = ?, title = ?, description = ?, requirements = ?, benefits = ?,
                    salary_min = ?, salary_max = ?, location = ?, job_type = ?, experience_level = ?,
                    deadline = ?
                WHERE id = ? AND recruiter_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "issssddssssii",
            $data['category_id'],
            $data['title'],
            $data['description'],
            $data['requirements'],
            $data['benefits'],
            $data['salary_min'],
            $data['salary_max'],
            $data['location'],
            $data['job_type'],
            $data['experience_level'],
            $data['deadline'],
            $jobId,
            $recruiterId
        );

        return $stmt->execute();
    }

    public function updateJobStatus($jobId, $recruiterId, $status) {
        $allowed = ['active', 'closed'];

        if (!in_array($status, $allowed)) {
            return false;
        }

        $sql = "UPDATE jobs SET status = ? WHERE id = ? AND recruiter_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $status, $jobId, $recruiterId);

        return $stmt->execute();
    }

    public function getRecruiterApplications($recruiterId) {
        $sql = "SELECT applications.id AS application_id,
                       applications.cover_letter,
                       applications.resume_path AS application_resume,
                       applications.status,
                       applications.applied_at,
                       jobs.id AS job_id,
                       jobs.title AS job_title,
                       jobs.location,
                       jobs.job_type,
                       jobs.experience_level,
                       users.id AS seeker_id,
                       users.name AS seeker_name,
                       users.email AS seeker_email,
                       users.phone AS seeker_phone,
                       seeker_profiles.headline,
                       seeker_profiles.skills,
                       seeker_profiles.years_experience,
                       seeker_profiles.education_level,
                       seeker_profiles.preferred_location,
                       seeker_profiles.resume_path AS profile_resume
                FROM applications
                INNER JOIN jobs ON applications.job_id = jobs.id
                INNER JOIN users ON applications.seeker_id = users.id
                LEFT JOIN seeker_profiles ON users.id = seeker_profiles.user_id
                WHERE jobs.recruiter_id = ? OR applications.recruiter_id = ?
                ORDER BY applications.applied_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $recruiterId, $recruiterId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function updateApplicationStatus($applicationId, $recruiterId, $status) {
        $allowedStatuses = ['submitted', 'reviewed', 'shortlisted', 'interview', 'rejected'];

        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $sql = "UPDATE applications
                INNER JOIN jobs ON applications.job_id = jobs.id
                SET applications.status = ?
                WHERE applications.id = ?
                AND (jobs.recruiter_id = ? OR applications.recruiter_id = ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siii", $status, $applicationId, $recruiterId, $recruiterId);

        return $stmt->execute();
    }

    public function searchSeekers($keyword = "", $skills = "", $location = "", $experience = "") {
        $sql = "SELECT users.id, users.name, users.email, users.phone, users.profile_pic,
                       seeker_profiles.headline,
                       seeker_profiles.summary,
                       seeker_profiles.skills,
                       seeker_profiles.years_experience,
                       seeker_profiles.education_level,
                       seeker_profiles.current_salary,
                       seeker_profiles.expected_salary,
                       seeker_profiles.preferred_location,
                       seeker_profiles.resume_path
                FROM users
                LEFT JOIN seeker_profiles ON users.id = seeker_profiles.user_id
                WHERE users.role = 'seeker'
                AND users.is_active = 1";

        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (
                        users.name LIKE ?
                        OR users.email LIKE ?
                        OR seeker_profiles.headline LIKE ?
                        OR seeker_profiles.summary LIKE ?
                     )";

            $keywordTerm = "%" . $keyword . "%";
            $params[] = $keywordTerm;
            $params[] = $keywordTerm;
            $params[] = $keywordTerm;
            $params[] = $keywordTerm;
            $types .= "ssss";
        }

        if (!empty($skills)) {
            $sql .= " AND seeker_profiles.skills LIKE ?";
            $skillsTerm = "%" . $skills . "%";
            $params[] = $skillsTerm;
            $types .= "s";
        }

        if (!empty($location)) {
            $sql .= " AND seeker_profiles.preferred_location LIKE ?";
            $locationTerm = "%" . $location . "%";
            $params[] = $locationTerm;
            $types .= "s";
        }

        if ($experience !== "") {
            $sql .= " AND seeker_profiles.years_experience >= ?";
            $params[] = (int)$experience;
            $types .= "i";
        }

        $sql .= " ORDER BY users.name ASC";

        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();

        return $stmt->get_result();
    }

    public function getSeekerProfileById($seekerId) {
        $sql = "SELECT users.id, users.name, users.email, users.phone, users.profile_pic,
                       seeker_profiles.headline,
                       seeker_profiles.summary,
                       seeker_profiles.skills,
                       seeker_profiles.years_experience,
                       seeker_profiles.education_level,
                       seeker_profiles.current_salary,
                       seeker_profiles.expected_salary,
                       seeker_profiles.preferred_location,
                       seeker_profiles.resume_path
                FROM users
                LEFT JOIN seeker_profiles ON users.id = seeker_profiles.user_id
                WHERE users.id = ?
                AND users.role = 'seeker'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $seekerId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getSeekersForOutreach() {
        $sql = "SELECT users.id, users.name, users.email,
                       seeker_profiles.headline,
                       seeker_profiles.skills,
                       seeker_profiles.preferred_location
                FROM users
                LEFT JOIN seeker_profiles ON users.id = seeker_profiles.user_id
                WHERE users.role = 'seeker'
                AND users.is_active = 1
                ORDER BY users.name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getRecruiterActiveJobs($recruiterId) {
        $sql = "SELECT id, title, location, job_type
                FROM jobs
                WHERE recruiter_id = ?
                AND status = 'active'
                ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $recruiterId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function sendOutreach($recruiterId, $seekerId, $jobId, $message) {
        if ($jobId === null || $jobId === "" || $jobId == 0) {
            $sql = "INSERT INTO recruiter_outreach 
                    (recruiter_id, seeker_id, job_id, message, status, sent_at)
                    VALUES (?, ?, NULL, ?, 'sent', NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iis", $recruiterId, $seekerId, $message);
        } else {
            $sql = "INSERT INTO recruiter_outreach 
                    (recruiter_id, seeker_id, job_id, message, status, sent_at)
                    VALUES (?, ?, ?, ?, 'sent', NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iiis", $recruiterId, $seekerId, $jobId, $message);
        }

        return $stmt->execute();
    }

    public function getOutreachList($recruiterId) {
        $sql = "SELECT recruiter_outreach.id,
                       recruiter_outreach.message,
                       recruiter_outreach.status,
                       recruiter_outreach.sent_at,
                       users.name AS seeker_name,
                       users.email AS seeker_email,
                       jobs.title AS job_title,
                       jobs.location AS job_location
                FROM recruiter_outreach
                INNER JOIN users ON recruiter_outreach.seeker_id = users.id
                LEFT JOIN jobs ON recruiter_outreach.job_id = jobs.id
                WHERE recruiter_outreach.recruiter_id = ?
                ORDER BY recruiter_outreach.sent_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $recruiterId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getSeekersForMessage() {
        $sql = "SELECT id, name, email, phone
                FROM users
                WHERE role = 'seeker'
                AND is_active = 1
                ORDER BY name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getRecruiterApplicationsForMessage($recruiterId) {
        $sql = "SELECT applications.id AS application_id,
                       users.name AS seeker_name,
                       users.email AS seeker_email,
                       jobs.title AS job_title
                FROM applications
                INNER JOIN users ON applications.seeker_id = users.id
                INNER JOIN jobs ON applications.job_id = jobs.id
                WHERE jobs.recruiter_id = ? OR applications.recruiter_id = ?
                ORDER BY applications.applied_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $recruiterId, $recruiterId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getReceivedMessages($userId) {
        $sql = "SELECT messages.id,
                       messages.sender_id,
                       messages.recipient_id,
                       messages.application_id,
                       messages.body,
                       messages.sent_at,
                       messages.is_read,
                       sender.name AS sender_name,
                       sender.email AS sender_email,
                       sender.role AS sender_role,
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

    public function getSentMessages($userId) {
        $sql = "SELECT messages.id,
                       messages.sender_id,
                       messages.recipient_id,
                       messages.application_id,
                       messages.body,
                       messages.sent_at,
                       messages.is_read,
                       recipient.name AS recipient_name,
                       recipient.email AS recipient_email,
                       recipient.role AS recipient_role,
                       jobs.title AS job_title
                FROM messages
                INNER JOIN users AS recipient ON messages.recipient_id = recipient.id
                LEFT JOIN applications ON messages.application_id = applications.id
                LEFT JOIN jobs ON applications.job_id = jobs.id
                WHERE messages.sender_id = ?
                ORDER BY messages.sent_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        return $stmt->get_result();
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

    public function markMessageAsRead($messageId, $userId) {
        $sql = "UPDATE messages 
                SET is_read = 1
                WHERE id = ? AND recipient_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $messageId, $userId);

        return $stmt->execute();
    }

    public function getComplaintSubjectUsers() {
        $sql = "SELECT id, name, email, role 
                FROM users 
                WHERE role IN ('admin', 'employer', 'seeker')
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
        $sql = "SELECT complaints.id,
                       complaints.description,
                       complaints.status,
                       complaints.admin_note,
                       complaints.created_at,
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