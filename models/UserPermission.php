<?php
// models/UserPermission.php
require_once __DIR__ . '/../config/Database.php';

class UserPermission {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Database();
    }
    
    /**
     * Check if user has permission (includes role-based and direct permissions)
     */
    public function hasPermission($user_id, $permission_code) {
        // Use the view to get effective permissions from both roles and direct assignments
        $sql = "SELECT COUNT(*) as cnt FROM v_user_effective_permissions 
                WHERE user_id = ? AND permission_code = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        $row = $stmt->fetch();
        return $row && $row['cnt'] > 0;
    }
    
    /**
     * Check if user has permission by ID
     */
    public function hasPermissionById($user_id, $permission_id) {
        $sql = "SELECT COUNT(*) as cnt FROM v_user_effective_permissions 
                WHERE user_id = ? AND permission_id = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_id]);
        $row = $stmt->fetch();
        return $row && $row['cnt'] > 0;
    }
    
    /**
     * Check multiple permissions at once
     */
    public function hasPermissions($user_id, $permission_codes) {
        if (empty($permission_codes)) return [];
        
        $placeholders = str_repeat('?,', count($permission_codes) - 1) . '?';
        $sql = "SELECT permission_code FROM v_user_effective_permissions 
                WHERE user_id = ? AND permission_code IN ($placeholders)";
        
        $params = array_merge([$user_id], $permission_codes);
        $stmt = $this->db->query($sql, $params);
        $granted = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $result = [];
        foreach ($permission_codes as $code) {
            $result[$code] = in_array($code, $granted);
        }
        return $result;
    }
      
    /**
     * Get all effective permissions for a user (from roles + direct assignments)
     */
    public function getUserEffectivePermissions($user_id) {
        $sql = "SELECT DISTINCT 
                    permission_id,
                    permission_code, 
                    permission_name,
                    module,
                    category,
                    permission_source,
                    source_detail
                FROM v_user_effective_permissions 
                WHERE user_id = ? 
                ORDER BY module, category, permission_name";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get direct permissions assigned to user (not from roles)
     */
    public function getUserDirectPermissions($user_id) {
        $sql = "SELECT p.permission_id, p.permission_code, p.permission_name, p.module, p.category,
                       up.granted, up.granted_at, up.expires_at, up.reason,
                       granter.username as granted_by_username
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.permission_id
                LEFT JOIN users granter ON up.granted_by = granter.user_id
                WHERE up.user_id = ? AND up.granted = TRUE
                  AND (up.expires_at IS NULL OR up.expires_at > NOW())
                ORDER BY p.module, p.category, p.permission_name";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permissions by module for a user
     */
    public function getUserPermissionsByModule($user_id, $module = null) {
        $whereClause = $module ? 'AND module = ?' : '';
        $params = $module ? [$user_id, $module] : [$user_id];
        
        $sql = "SELECT module, permission_code, permission_name, permission_source
                FROM v_user_effective_permissions 
                WHERE user_id = ? {$whereClause}
                ORDER BY module, permission_name";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
      
    /**
     * Set direct user permissions (not role-based)
     */
    public function setUserDirectPermissions($user_id, $permissions, $granted_by = null, $reason = null) {
        try {
            $this->db->query("START TRANSACTION");
            
            // Remove existing direct permissions
            $sql = "DELETE FROM user_permissions WHERE user_id = ?";
            $this->db->query($sql, [$user_id]);
            
            // Insert new permissions
            foreach ($permissions as $permission_id => $granted) {
                if ($granted) {
                    $sql = "INSERT INTO user_permissions 
                            (user_id, permission_id, granted, granted_at, granted_by, reason) 
                            VALUES (?, ?, TRUE, NOW(), ?, ?)";
                    $this->db->query($sql, [$user_id, $permission_id, $granted_by, $reason]);
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
     * Grant single direct permission
     */
    public function grantDirectPermission($user_id, $permission_id, $granted_by = null, $reason = null, $expires_at = null) {
        $sql = "INSERT INTO user_permissions 
                (user_id, permission_id, granted, granted_at, granted_by, reason, expires_at) 
                VALUES (?, ?, TRUE, NOW(), ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                granted = TRUE, granted_at = NOW(), granted_by = ?, reason = ?, expires_at = ?";
        $stmt = $this->db->query($sql, [
            $user_id, $permission_id, $granted_by, $reason, $expires_at,
            $granted_by, $reason, $expires_at
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Revoke single direct permission
     */
    public function revokeDirectPermission($user_id, $permission_id) {
        $sql = "DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_id]);
        return $stmt->rowCount() > 0;
    }
      
    /**
     * Get users with specific permission (from any source)
     */
    public function getUsersWithPermission($permission_code) {
        $sql = "SELECT DISTINCT u.user_id, u.username, u.email, 
                       GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_level DESC SEPARATOR ', ') as roles,
                       vup.permission_source
                FROM v_user_effective_permissions vup
                JOIN users u ON vup.user_id = u.user_id 
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id AND r.is_active = TRUE
                WHERE vup.permission_code = ? AND u.is_active = TRUE
                GROUP BY u.user_id, u.username, u.email, vup.permission_source
                ORDER BY u.username";
        $stmt = $this->db->query($sql, [$permission_code]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission distribution statistics
     */
    public function getPermissionDistribution() {
        $sql = "SELECT 
                    p.module,
                    p.category,
                    p.permission_code,
                    p.permission_name,
                    COUNT(DISTINCT CASE WHEN vup.permission_source = 'role' THEN vup.user_id END) as role_users,
                    COUNT(DISTINCT CASE WHEN vup.permission_source = 'direct' THEN vup.user_id END) as direct_users,
                    COUNT(DISTINCT vup.user_id) as total_users
                FROM permissions p
                LEFT JOIN v_user_effective_permissions vup ON p.permission_code = vup.permission_code
                GROUP BY p.permission_id, p.module, p.category, p.permission_code, p.permission_name
                ORDER BY total_users DESC, p.module, p.category";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission conflicts (users with conflicting permissions)
     */
    public function getPermissionConflicts($user_id = null) {
        $whereClause = $user_id ? 'WHERE u.user_id = ?' : '';
        $params = $user_id ? [$user_id] : [];
        
        $sql = "SELECT 
                    u.user_id,
                    u.username,
                    COUNT(DISTINCT vup.permission_code) as total_permissions,
                    COUNT(DISTINCT CASE WHEN vup.permission_source = 'role' THEN vup.permission_code END) as role_permissions,
                    COUNT(DISTINCT CASE WHEN vup.permission_source = 'direct' THEN vup.permission_code END) as direct_permissions
                FROM users u
                LEFT JOIN v_user_effective_permissions vup ON u.user_id = vup.user_id
                {$whereClause}
                GROUP BY u.user_id, u.username
                HAVING total_permissions > 0
                ORDER BY total_permissions DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
      
    /**
     * Get comprehensive permission statistics
     */
    public function getPermissionStats() {
        $sql = "SELECT 
                    p.module,
                    COUNT(DISTINCT p.permission_id) as total_permissions,
                    COUNT(DISTINCT vup.user_id) as users_with_module_permissions,
                    AVG(permissions_per_user.permission_count) as avg_permissions_per_user
                FROM permissions p
                LEFT JOIN v_user_effective_permissions vup ON p.permission_code = vup.permission_code
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as permission_count
                    FROM v_user_effective_permissions 
                    GROUP BY user_id
                ) permissions_per_user ON vup.user_id = permissions_per_user.user_id
                GROUP BY p.module
                ORDER BY users_with_module_permissions DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permission history for user (includes role changes)
     */
    public function getPermissionHistory($user_id, $limit = 50) {
        $sql = "SELECT 
                    'direct' as source_type,
                    p.permission_code,
                    p.permission_name,
                    up.granted,
                    up.granted_at as action_date,
                    granter.username as granted_by_username,
                    up.reason
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.permission_id
                LEFT JOIN users granter ON up.granted_by = granter.user_id
                WHERE up.user_id = ?
                
                UNION ALL
                
                SELECT 
                    'role_assignment' as source_type,
                    CONCAT('Role: ', r.role_name) as permission_code,
                    CONCAT('Role assigned: ', r.role_description) as permission_name,
                    ur.is_active as granted,
                    ur.assigned_at as action_date,
                    assigner.username as granted_by_username,
                    CONCAT('Department: ', COALESCE(d.department_name, 'None')) as reason
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN users assigner ON ur.assigned_by = assigner.user_id
                LEFT JOIN departments d ON ur.department_id = d.department_id
                WHERE ur.user_id = ?
                
                ORDER BY action_date DESC 
                LIMIT ?";
        $stmt = $this->db->query($sql, [$user_id, $user_id, $limit]);
        return $stmt->fetchAll();
    }
      
    /**
     * Bulk grant direct permissions to multiple users
     */
    public function bulkGrantDirectPermissions($user_ids, $permission_ids, $granted_by = null, $reason = null) {
        try {
            $this->db->query("START TRANSACTION");
            
            foreach ($user_ids as $user_id) {
                foreach ($permission_ids as $permission_id) {
                    $sql = "INSERT INTO user_permissions 
                            (user_id, permission_id, granted, granted_at, granted_by, reason) 
                            VALUES (?, ?, TRUE, NOW(), ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            granted = TRUE, granted_at = NOW(), granted_by = ?, reason = ?";
                    $this->db->query($sql, [
                        $user_id, $permission_id, $granted_by, $reason,
                        $granted_by, $reason
                    ]);
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
     * Bulk revoke direct permissions from multiple users
     */
    public function bulkRevokeDirectPermissions($user_ids, $permission_ids) {
        try {
            $this->db->query("START TRANSACTION");
            
            foreach ($user_ids as $user_id) {
                foreach ($permission_ids as $permission_id) {
                    $sql = "DELETE FROM user_permissions WHERE user_id = ? AND permission_id = ?";
                    $this->db->query($sql, [$user_id, $permission_id]);
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
     * Check if user can manage another user (based on role hierarchy)
     */
    public function canManageUser($manager_id, $target_user_id) {
        // Get manager's highest role level
        $sql = "SELECT MAX(r.role_level) as max_level
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND ur.is_active = TRUE AND r.is_active = TRUE";
        $stmt = $this->db->query($sql, [$manager_id]);
        $managerResult = $stmt->fetch();
        $managerLevel = $managerResult['max_level'] ?? 0;
        
        // Get target user's highest role level
        $stmt = $this->db->query($sql, [$target_user_id]);
        $targetResult = $stmt->fetch();
        $targetLevel = $targetResult['max_level'] ?? 0;
        
        // Manager can manage users with lower or equal role level
        return $managerLevel >= $targetLevel;
    }
}
