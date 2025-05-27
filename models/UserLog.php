<?php
// models/UserLog.php
require_once __DIR__ . '/../config/Database.php';
use App\Database;

class UserLog {
    private $db;

    public function __construct() {
        $this->db = new \App\Database();
    }

    public function log($user_id, $action, $details = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $this->db->query("INSERT INTO user_logs (user_id, log_time, action, details, ip_address) VALUES (?, NOW(), ?, ?, ?)", [$user_id, $action, $details, $ip]);
    }
}
