<?php

class AdminController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    private function getSingleCount($sql) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function countTotalUsers() {
        return $this->getSingleCount("SELECT COUNT(*) AS total FROM users");
    }

    public function countUsersByRole($role) {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'] ?? 0;
    }

    public function countActiveJobs() {
        return $this->getSingleCount("SELECT COUNT(*) AS total FROM jobs WHERE status = 'active'");
    }

    public function countTotalApplications() {
        return $this->getSingleCount("SELECT COUNT(*) AS total FROM applications");
    }

    public function countApplicationsToday() {
        return $this->getSingleCount("SELECT COUNT(*) AS total FROM applications WHERE DATE(applied_at) = CURDATE()");
    }

    public function countPendingVerifications() {
        return $this->getSingleCount(
            "SELECT COUNT(*) AS total 
             FROM users 
             WHERE role IN ('employer', 'recruiter') 
             AND is_verified = 0"
        );
    }

    public function countOpenComplaints() {
        return $this->getSingleCount("SELECT COUNT(*) AS total FROM complaints WHERE status = 'open'");
    }

    public function getRecentUsers() {
        $sql = "SELECT id, name, email, phone, role, is_active, is_verified, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getRecentComplaints() {
        $sql = "SELECT complaints.id, complaints.description, complaints.status, complaints.created_at,
                       users.name AS submitter_name
                FROM complaints
                INNER JOIN users ON complaints.submitter_id = users.id
                ORDER BY complaints.created_at DESC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result();
    }
    public function getUsers($search = "", $role = "", $status = "", $verified = "") {
    $sql = "SELECT id, name, email, phone, role, is_active, is_verified, verification_note, created_at 
            FROM users 
            WHERE 1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($role)) {
        $sql .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }

    if ($status !== "") {
        $sql .= " AND is_active = ?";
        $params[] = (int)$status;
        $types .= "i";
    }

    if ($verified !== "") {
        $sql .= " AND is_verified = ?";
        $params[] = (int)$verified;
        $types .= "i";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

public function approveUser($userId, $adminId) {
    $sql = "UPDATE users 
            SET is_verified = 1, verification_note = NULL 
            WHERE id = ? AND role IN ('employer', 'recruiter')";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, $userId, null, "approve_user", "User account approved");
    }

    return $success;
}

public function rejectUser($userId, $adminId, $reason) {
    $sql = "UPDATE users 
            SET is_verified = 0, verification_note = ? 
            WHERE id = ? AND role IN ('employer', 'recruiter')";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $reason, $userId);
    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, $userId, null, "reject_user", $reason);
    }

    return $success;
}

public function suspendUser($userId, $adminId, $reason) {
    $sql = "UPDATE users 
            SET is_active = 0 
            WHERE id = ? AND role != 'admin'";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, $userId, null, "suspend_user", $reason);
    }

    return $success;
}

public function reactivateUser($userId, $adminId) {
    $sql = "UPDATE users 
            SET is_active = 1 
            WHERE id = ? AND role != 'admin'";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, $userId, null, "reactivate_user", "User account reactivated");
    }

    return $success;
}

public function logAdminAction($adminId, $targetUserId, $targetJobId, $actionType, $reason = null) {
    $sql = "CREATE TABLE IF NOT EXISTS admin_actions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        target_user_id INT NULL,
        target_job_id INT NULL,
        action_type VARCHAR(100) NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $this->conn->query($sql);

    $insertSql = "INSERT INTO admin_actions 
                  (admin_id, target_user_id, target_job_id, action_type, reason)
                  VALUES (?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($insertSql);
    $stmt->bind_param("iiiss", $adminId, $targetUserId, $targetJobId, $actionType, $reason);

    return $stmt->execute();
 }
public function getCategoriesWithJobCount() {
    $sql = "SELECT categories.id, categories.name, categories.description,
                   COUNT(jobs.id) AS total_jobs,
                   SUM(CASE WHEN jobs.status = 'active' THEN 1 ELSE 0 END) AS active_jobs
            FROM categories
            LEFT JOIN jobs ON categories.id = jobs.category_id
            GROUP BY categories.id, categories.name, categories.description
            ORDER BY categories.name ASC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function categoryExists($name, $excludeId = null) {
    if ($excludeId) {
        $sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $excludeId);
    } else {
        $sql = "SELECT id FROM categories WHERE name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

public function addCategory($name, $description) {
    if ($this->categoryExists($name)) {
        return "exists";
    }

    $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $name, $description);

    return $stmt->execute();
}

public function getCategoryById($categoryId) {
    $sql = "SELECT id, name, description FROM categories WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

public function updateCategory($categoryId, $name, $description) {
    if ($this->categoryExists($name, $categoryId)) {
        return "exists";
    }

    $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $description, $categoryId);

    return $stmt->execute();
}

public function deleteCategory($categoryId) {
    $countSql = "SELECT COUNT(*) AS total 
                 FROM jobs 
                 WHERE category_id = ? AND status = 'active'";

    $countStmt = $this->conn->prepare($countSql);
    $countStmt->bind_param("i", $categoryId);
    $countStmt->execute();

    $countResult = $countStmt->get_result();
    $row = $countResult->fetch_assoc();

    if ($row['total'] > 0) {
        return "has_active_jobs";
    }

    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);

    return $stmt->execute();
}
public function getAllJobs($search = "", $status = "", $categoryId = "", $featured = "") {
    $sql = "SELECT jobs.id, jobs.title, jobs.location, jobs.job_type, jobs.experience_level,
                   jobs.salary_min, jobs.salary_max, jobs.deadline, jobs.status, jobs.is_featured,
                   jobs.created_at,
                   categories.name AS category_name,
                   employer.name AS employer_name,
                   recruiter.name AS recruiter_name
            FROM jobs
            LEFT JOIN categories ON jobs.category_id = categories.id
            LEFT JOIN users AS employer ON jobs.employer_id = employer.id
            LEFT JOIN users AS recruiter ON jobs.recruiter_id = recruiter.id
            WHERE 1";

    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (
                    jobs.title LIKE ? 
                    OR jobs.description LIKE ? 
                    OR jobs.location LIKE ?
                    OR employer.name LIKE ?
                    OR recruiter.name LIKE ?
                 )";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sssss";
    }

    if (!empty($status)) {
        $sql .= " AND jobs.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($categoryId)) {
        $sql .= " AND jobs.category_id = ?";
        $params[] = (int)$categoryId;
        $types .= "i";
    }

    if ($featured !== "") {
        $sql .= " AND jobs.is_featured = ?";
        $params[] = (int)$featured;
        $types .= "i";
    }

    $sql .= " ORDER BY jobs.created_at DESC";

    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

public function getAllCategoriesSimple() {
    $sql = "SELECT id, name FROM categories ORDER BY name ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function updateJobStatus($jobId, $status, $adminId, $reason = null) {
    $allowedStatuses = ['active', 'closed', 'draft'];

    if (!in_array($status, $allowedStatuses)) {
        return false;
    }

    $sql = "UPDATE jobs SET status = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $status, $jobId);
    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, null, $jobId, "update_job_status", $reason);
    }

    return $success;
}

public function getJobFeaturedStatus($jobId) {
    $sql = "SELECT is_featured FROM jobs WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $jobId);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return null;
    }

    $row = $result->fetch_assoc();
    return (int)$row['is_featured'];
}

public function toggleFeaturedJob($jobId, $adminId) {
    $currentStatus = $this->getJobFeaturedStatus($jobId);

    if ($currentStatus === null) {
        return false;
    }

    $newStatus = $currentStatus ? 0 : 1;

    $sql = "UPDATE jobs SET is_featured = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $newStatus, $jobId);
    $success = $stmt->execute();

    if ($success) {
        $reason = $newStatus ? "Job marked as featured" : "Job removed from featured list";
        $this->logAdminAction($adminId, null, $jobId, "toggle_featured_job", $reason);

        return $newStatus;
    }

    return false;
}

public function getComplaints($status = "", $search = "") {
    $sql = "SELECT complaints.id, complaints.description, complaints.status, 
                   complaints.admin_note, complaints.created_at,
                   submitter.name AS submitter_name,
                   submitter.email AS submitter_email,
                   submitter.role AS submitter_role,
                   subject.name AS subject_name,
                   subject.email AS subject_email,
                   subject.role AS subject_role
            FROM complaints
            INNER JOIN users AS submitter ON complaints.submitter_id = submitter.id
            LEFT JOIN users AS subject ON complaints.subject_id = subject.id
            WHERE 1";

    $params = [];
    $types = "";

    if (!empty($status)) {
        $sql .= " AND complaints.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (
                    complaints.description LIKE ?
                    OR submitter.name LIKE ?
                    OR submitter.email LIKE ?
                    OR subject.name LIKE ?
                    OR subject.email LIKE ?
                 )";

        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sssss";
    }

    $sql .= " ORDER BY complaints.created_at DESC";

    $stmt = $this->conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    return $stmt->get_result();
}

public function getComplaintById($complaintId) {
    $sql = "SELECT complaints.id, complaints.description, complaints.status,
                   complaints.admin_note, complaints.created_at,
                   submitter.id AS submitter_id,
                   submitter.name AS submitter_name,
                   submitter.email AS submitter_email,
                   submitter.phone AS submitter_phone,
                   submitter.role AS submitter_role,
                   subject.id AS subject_id,
                   subject.name AS subject_name,
                   subject.email AS subject_email,
                   subject.phone AS subject_phone,
                   subject.role AS subject_role
            FROM complaints
            INNER JOIN users AS submitter ON complaints.submitter_id = submitter.id
            LEFT JOIN users AS subject ON complaints.subject_id = subject.id
            WHERE complaints.id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $complaintId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

public function resolveComplaint($complaintId, $adminNote, $adminId) {
    $sql = "UPDATE complaints 
            SET status = 'resolved', admin_note = ?
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $adminNote, $complaintId);

    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, null, null, "resolve_complaint", "Complaint ID $complaintId resolved");
    }

    return $success;
}

public function reopenComplaint($complaintId, $adminId) {
    $sql = "UPDATE complaints 
            SET status = 'open'
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $complaintId);

    $success = $stmt->execute();

    if ($success) {
        $this->logAdminAction($adminId, null, null, "reopen_complaint", "Complaint ID $complaintId reopened");
    }

    return $success;
}

public function getPlatformSettings() {
    $sql = "SELECT setting_key, setting_value FROM platform_settings";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();
    $settings = [];

    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    return $settings;
}

public function updatePlatformSetting($key, $value) {
    $sql = "UPDATE platform_settings SET setting_value = ? WHERE setting_key = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $value, $key);

    return $stmt->execute();
}

public function createAnnouncement($title, $body, $adminId) {
    $sql = "INSERT INTO announcements (title, body, posted_by) VALUES (?, ?, ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssi", $title, $body, $adminId);

    return $stmt->execute();
}

public function getAnnouncements() {
    $sql = "SELECT announcements.id, announcements.title, announcements.body, announcements.created_at,
                   users.name AS admin_name
            FROM announcements
            INNER JOIN users ON announcements.posted_by = users.id
            ORDER BY announcements.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function deleteAnnouncement($announcementId) {
    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $announcementId);

    return $stmt->execute();
}

public function getJobsByCategory() {
    $sql = "SELECT categories.name, COUNT(jobs.id) AS total_jobs
            FROM categories
            LEFT JOIN jobs ON categories.id = jobs.category_id
            GROUP BY categories.id, categories.name
            ORDER BY total_jobs DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getApplicationsOverTime() {
    $sql = "SELECT DATE(applied_at) AS application_date, COUNT(*) AS total_applications
            FROM applications
            GROUP BY DATE(applied_at)
            ORDER BY application_date DESC
            LIMIT 10";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getTopEmployers() {
    $sql = "SELECT users.name AS employer_name, COUNT(applications.id) AS total_applications
            FROM users
            INNER JOIN jobs ON users.id = jobs.employer_id
            LEFT JOIN applications ON jobs.id = applications.job_id
            WHERE users.role = 'employer'
            GROUP BY users.id, users.name
            ORDER BY total_applications DESC
            LIMIT 10";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getMostActiveRecruiters() {
    $sql = "SELECT users.name AS recruiter_name, COUNT(jobs.id) AS total_jobs
            FROM users
            LEFT JOIN jobs ON users.id = jobs.recruiter_id
            WHERE users.role = 'recruiter'
            GROUP BY users.id, users.name
            ORDER BY total_jobs DESC
            LIMIT 10";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getPopularLocations() {
    $sql = "SELECT location, COUNT(*) AS total_jobs
            FROM jobs
            WHERE location IS NOT NULL AND location != ''
            GROUP BY location
            ORDER BY total_jobs DESC
            LIMIT 10";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getPopularJobTypes() {
    $sql = "SELECT job_type, COUNT(*) AS total_jobs
            FROM jobs
            WHERE job_type IS NOT NULL
            GROUP BY job_type
            ORDER BY total_jobs DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getUserGrowthByRole() {
    $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, role, COUNT(*) AS total_users
            FROM users
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), role
            ORDER BY month DESC, role ASC
            LIMIT 24";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->get_result();
}

public function getReportSummary() {
    $summary = [];

    $queries = [
        "total_users" => "SELECT COUNT(*) AS total FROM users",
        "total_jobs" => "SELECT COUNT(*) AS total FROM jobs",
        "active_jobs" => "SELECT COUNT(*) AS total FROM jobs WHERE status = 'active'",
        "total_applications" => "SELECT COUNT(*) AS total FROM applications",
        "total_complaints" => "SELECT COUNT(*) AS total FROM complaints",
        "resolved_complaints" => "SELECT COUNT(*) AS total FROM complaints WHERE status = 'resolved'",
        "pending_verifications" => "SELECT COUNT(*) AS total FROM users WHERE role IN ('employer','recruiter') AND is_verified = 0"
    ];

    foreach ($queries as $key => $sql) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $summary[$key] = $row['total'] ?? 0;
    }

    return $summary;
}

}