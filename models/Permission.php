<?php
require_once(__DIR__ . '/../config/Database.php');

use App\Database;

class Permission {
    private $conn;
    private $table = 'user_permissions';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Get all permissions for a specific user
     */
    public function getUserPermissions($userId) {
        try {
            $sql = "SELECT permission_code, granted FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($userId, $permissionCode) {
        try {
            $sql = "SELECT granted FROM {$this->table} WHERE user_id = ? AND permission_code = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $permissionCode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (bool)$result['granted'] : false;
        } catch (Exception $e) {
            error_log("Error checking permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Grant permission to user
     */
    public function grantPermission($userId, $permissionCode, $grantedBy = null) {
        try {
            $sql = "INSERT INTO {$this->table} (user_id, permission_code, granted, granted_by, granted_at) 
                   VALUES (?, ?, 1, ?, NOW()) 
                   ON DUPLICATE KEY UPDATE granted = 1, granted_by = ?, granted_at = NOW()";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$userId, $permissionCode, $grantedBy, $grantedBy]);
        } catch (Exception $e) {
            error_log("Error granting permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission($userId, $permissionCode) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ? AND permission_code = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$userId, $permissionCode]);
        } catch (Exception $e) {
            error_log("Error revoking permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all permissions for a user
     */
    public function clearUserPermissions($userId) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Error clearing user permissions: " . $e->getMessage());
            return false;
        }
    }
}
?>
