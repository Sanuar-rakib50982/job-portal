<?php

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function register($name, $email, $phone, $password, $role) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Seeker can be verified automatically.
        // Employer and recruiter will be verified later by admin.
        $is_verified = ($role === 'seeker') ? 1 : 0;

        $sql = "INSERT INTO users (name, email, phone, password_hash, role, is_verified)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $phone, $password_hash, $role, $is_verified);

        return $stmt->execute();
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }

        return false;
    }
}