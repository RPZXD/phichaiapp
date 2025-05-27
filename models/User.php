<?php
// models/User.php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;
    public function __construct() {
        $this->db = new \App\Database();
    }
    public function findByUsernameOrEmail($usernameOrEmail) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$usernameOrEmail, $usernameOrEmail]);
        return $stmt->fetch();
    }
    public function verifyPassword($user, $password) {
        // ป้องกัน error หาก password_hash ในฐานข้อมูลเป็น null หรือว่าง
        if (!isset($user['password_hash']) || empty($user['password_hash'])) {
            return false;
        }
        return password_verify($password, $user['password_hash']);
    }    public function getAllUsers() {
        $sql = "SELECT user_id, username, email, role, phone_number, created_at, updated_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getUserById($id) {
        $sql = "SELECT user_id, username, email, role, phone_number, created_at, updated_at FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password_hash, role, phone_number, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->query($sql, [
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['role'],
            $data['phone_number'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    public function updateUser($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE users SET username = ?, email = ?, password_hash = ?, role = ?, phone_number = ?, updated_at = NOW() WHERE user_id = ?";
            $params = [$data['username'], $data['email'], $data['password_hash'], $data['role'], $data['phone_number'], $id];
        } else {
            $sql = "UPDATE users SET username = ?, email = ?, role = ?, phone_number = ?, updated_at = NOW() WHERE user_id = ?";
            $params = [$data['username'], $data['email'], $data['role'], $data['phone_number'], $id];
        }
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE users SET password_hash = ?, force_password_change = 0, last_password_change = NOW(), updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$hashedPassword, $userId]);
        return $stmt->rowCount() > 0;
    }
}
