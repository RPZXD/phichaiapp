<?php
// models/UserRole.php
require_once __DIR__ . '/../config/Database.php';

class UserRole {
    private $db;
    public function __construct() {
        $this->db = new \App\Database();
    }
    public function getUserRole($user_id) {
        $sql = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
        $row = $stmt->fetch();
        return $row ? $row['role'] : null;
    }
}
