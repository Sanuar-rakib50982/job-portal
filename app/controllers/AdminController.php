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
}