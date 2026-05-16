<?php

class EmployerController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getProfile($employerId) {
        $sql = "SELECT * FROM employer_profiles WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employerId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function saveProfile($employerId, $data, $logoPath = null) {
        $profile = $this->getProfile($employerId);

        if ($profile) {
            if ($logoPath === null) {
                $logoPath = $profile['logo_path'] ?? null;
            }

            $sql = "UPDATE employer_profiles
                    SET company_name = ?, industry = ?, description = ?, website = ?, address = ?, logo_path = ?
                    WHERE user_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssssssi",
                $data['company_name'],
                $data['industry'],
                $data['description'],
                $data['website'],
                $data['address'],
                $logoPath,
                $employerId
            );

            return $stmt->execute();
        }

        $sql = "INSERT INTO employer_profiles
                (user_id, company_name, industry, description, website, address, logo_path)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "issssss",
            $employerId,
            $data['company_name'],
            $data['industry'],
            $data['description'],
            $data['website'],
            $data['address'],
            $logoPath
        );

        return $stmt->execute();
    }

    public function getCategories() {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getEmployerJobs($employerId) {
        $sql = "SELECT jobs.*, categories.name AS category_name
                FROM jobs
                LEFT JOIN categories ON jobs.category_id = categories.id
                WHERE jobs.employer_id = ?
                ORDER BY jobs.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employerId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getJobById($jobId, $employerId) {
        $sql = "SELECT * FROM jobs WHERE id = ? AND employer_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $jobId, $employerId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function createJob($employerId, $data) {
        $sql = "INSERT INTO jobs
                (employer_id, recruiter_id, category_id, title, description, requirements, benefits,
                 salary_min, salary_max, location, job_type, experience_level, deadline, status, is_featured)
                VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 0)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "iissssddssss",
            $employerId,
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

    public function updateJob($jobId, $employerId, $data) {
        $sql = "UPDATE jobs
                SET category_id = ?, title = ?, description = ?, requirements = ?, benefits = ?,
                    salary_min = ?, salary_max = ?, location = ?, job_type = ?, experience_level = ?,
                    deadline = ?
                WHERE id = ? AND employer_id = ?";

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
            $employerId
        );

        return $stmt->execute();
    }

    public function updateJobStatus($jobId, $employerId, $status) {
        $allowedStatuses = ['active', 'closed'];

        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $sql = "UPDATE jobs SET status = ? WHERE id = ? AND employer_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $status, $jobId, $employerId);

        return $stmt->execute();
    }

    public function getEmployerApplications($employerId) {
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
                WHERE jobs.employer_id = ?
                ORDER BY applications.applied_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employerId);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function updateApplicationStatus($applicationId, $employerId, $status) {
        $allowedStatuses = ['submitted', 'reviewed', 'shortlisted', 'interview', 'rejected'];

        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $sql = "UPDATE applications
                INNER JOIN jobs ON applications.job_id = jobs.id
                SET applications.status = ?
                WHERE applications.id = ?
                AND jobs.employer_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $status, $applicationId, $employerId);

        return $stmt->execute();
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

    public function getEmployerApplicationsForMessage($employerId) {
        $sql = "SELECT applications.id AS application_id,
                       users.name AS seeker_name,
                       users.email AS seeker_email,
                       jobs.title AS job_title
                FROM applications
                INNER JOIN users ON applications.seeker_id = users.id
                INNER JOIN jobs ON applications.job_id = jobs.id
                WHERE jobs.employer_id = ?
                ORDER BY applications.applied_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employerId);
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
                WHERE role IN ('admin', 'recruiter', 'seeker')
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