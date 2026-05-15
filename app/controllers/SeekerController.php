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
}