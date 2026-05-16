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
}