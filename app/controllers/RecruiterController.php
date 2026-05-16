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
                    SET agency_name = ?, specialization = ?, experience_years = ?, bio = ?, website = ?
                    WHERE user_id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "ssissi",
                $data['agency_name'],
                $data['specialization'],
                $data['experience_years'],
                $data['bio'],
                $data['website'],
                $recruiterId
            );

            return $stmt->execute();
        }

        $sql = "INSERT INTO recruiter_profiles
                (user_id, agency_name, specialization, experience_years, bio, website)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ississ",
            $recruiterId,
            $data['agency_name'],
            $data['specialization'],
            $data['experience_years'],
            $data['bio'],
            $data['website']
        );

        return $stmt->execute();
    }

    public function getClients($recruiterId) {
    $sql = "SELECT * FROM recruiter_clients WHERE recruiter_id = ? ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $recruiterId);
    $stmt->execute();

    return $stmt->get_result();
}

public function getClientById($clientId, $recruiterId) {
    $sql = "SELECT * FROM recruiter_clients WHERE id = ? AND recruiter_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $clientId, $recruiterId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

public function createClient($recruiterId, $data) {
    $sql = "INSERT INTO recruiter_clients
            (recruiter_id, company_name, contact_person, email, phone, industry, address)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
        "issssss",
        $recruiterId,
        $data['company_name'],
        $data['contact_person'],
        $data['email'],
        $data['phone'],
        $data['industry'],
        $data['address']
    );

    return $stmt->execute();
}

public function updateClient($clientId, $recruiterId, $data) {
    $sql = "UPDATE recruiter_clients
            SET company_name = ?, contact_person = ?, email = ?, phone = ?, industry = ?, address = ?
            WHERE id = ? AND recruiter_id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param(
        "ssssssii",
        $data['company_name'],
        $data['contact_person'],
        $data['email'],
        $data['phone'],
        $data['industry'],
        $data['address'],
        $clientId,
        $recruiterId
    );

    return $stmt->execute();
}

public function deleteClient($clientId, $recruiterId) {
    $sql = "DELETE FROM recruiter_clients WHERE id = ? AND recruiter_id = ?";
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

}