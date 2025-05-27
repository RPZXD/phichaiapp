<?php
// models/UserPermission.php
require_once __DIR__ . '/../config/Database.php';

class UserPermission {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Database();
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($user_id, $permission) {
        $sql = "SELECT COUNT(*) as cnt FROM user_permissions WHERE user_id = ? AND permission_code = ? AND granted = 1";
        $stmt = $this->db->query($sql, [$user_id, $permission]);
        $row = $stmt->fetch();
        return $row && $row['cnt'] > 0;
    }
    
    /**
     * Get all permissions for a user
     */
    public function getUserPermissions($user_id) {
        $sql = "SELECT permission_code, granted, granted_at FROM user_permissions WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Set user permissions (bulk update)
     */
    public function setUserPermissions($user_id, $permissions) {
        try {
            $this->db->query("START TRANSACTION");
            
            // Remove all current permissions
            $sql = "DELETE FROM user_permissions WHERE user_id = ?";
            $this->db->query($sql, [$user_id]);
            
            // Insert new permissions
            foreach ($permissions as $permission_code => $granted) {
                if ($granted) {
                    $sql = "INSERT INTO user_permissions (user_id, permission_code, granted, granted_at) VALUES (?, ?, 1, NOW())";
                    $this->db->query($sql, [$user_id, $permission_code]);
                }
            }
            
            $this->db->query("COMMIT");
            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
    
    /**
     * Grant single permission
     */
    public function grantPermission($user_id, $permission_code) {
        $sql = "INSERT INTO user_permissions (user_id, permission_code, granted, granted_at) 
                VALUES (?, ?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE granted = 1, granted_at = NOW()";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Revoke single permission
     */
    public function revokePermission($user_id, $permission_code) {
        $sql = "UPDATE user_permissions SET granted = 0 WHERE user_id = ? AND permission_code = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get users with specific permission
     */
    public function getUsersWithPermission($permission_code) {
        $sql = "SELECT u.user_id, u.username, u.email, u.role 
                FROM users u 
                JOIN user_permissions up ON u.user_id = up.user_id 
                WHERE up.permission_code = ? AND up.granted = 1";
        $stmt = $this->db->query($sql, [$permission_code]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission statistics
     */
    public function getPermissionStats() {
        $sql = "SELECT 
                    permission_code,
                    COUNT(*) as user_count,
                    MAX(granted_at) as last_granted
                FROM user_permissions 
                WHERE granted = 1 
                GROUP BY permission_code 
                ORDER BY user_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission history for user
     */
    public function getPermissionHistory($user_id, $limit = 50) {
        $sql = "SELECT permission_code, granted, granted_at 
                FROM user_permissions 
                WHERE user_id = ? 
                ORDER BY granted_at DESC 
                LIMIT ?";
        $stmt = $this->db->query($sql, [$user_id, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Bulk grant permissions to multiple users
     */
    public function bulkGrantPermissions($user_ids, $permission_codes) {
        try {
            $this->db->query("START TRANSACTION");
            
            foreach ($user_ids as $user_id) {
                foreach ($permission_codes as $permission_code) {
                    $sql = "INSERT INTO user_permissions (user_id, permission_code, granted, granted_at) 
                            VALUES (?, ?, 1, NOW()) 
                            ON DUPLICATE KEY UPDATE granted = 1, granted_at = NOW()";
                    $this->db->query($sql, [$user_id, $permission_code]);
                }
            }
            
            $this->db->query("COMMIT");
            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
    
    /**
     * Bulk revoke permissions from multiple users
     */
    public function bulkRevokePermissions($user_ids, $permission_codes) {
        try {
            $this->db->query("START TRANSACTION");
            
            foreach ($user_ids as $user_id) {
                foreach ($permission_codes as $permission_code) {
                    $sql = "DELETE FROM user_permissions WHERE user_id = ? AND permission_code = ?";
                    $this->db->query($sql, [$user_id, $permission_code]);
                }
            }
            
            $this->db->query("COMMIT");
            return true;
        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            throw $e;
        }
    }
}
