<?php
// models/Role.php
require_once __DIR__ . '/../config/Database.php';

class Role {
    private $conn;
    
    public function __construct($conn = null) {
        if ($conn) {
            $this->conn = $conn;
        } else {
            $db = new \App\Database();
            $this->conn = $db->getConnection();
        }
    }
      /**
     * Get all active roles
     */
    public function getAllRoles($includeInactive = false) {
        $whereClause = $includeInactive ? '' : 'WHERE is_active = TRUE';
        $sql = "SELECT * FROM roles {$whereClause} ORDER BY role_level DESC, role_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Get role by ID
     */
    public function getRoleById($roleId) {
        $sql = "SELECT * FROM roles WHERE role_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get role by code
     */
    public function getRoleByCode($roleCode) {
        $sql = "SELECT * FROM roles WHERE role_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$roleCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
      /**
     * Create new role
     */
    public function createRole($data) {
        $sql = "INSERT INTO roles (role_code, role_name, role_description, role_level, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['role_code'],
            $data['role_name'],
            $data['role_description'] ?? null,
            $data['role_level'] ?? 1,
            $data['is_active'] ?? true
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update role
     */
    public function updateRole($roleId, $data) {
        $sql = "UPDATE roles 
                SET role_code = ?, role_name = ?, role_description = ?, 
                    role_level = ?, is_active = ?, updated_at = NOW()
                WHERE role_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['role_code'],
            $data['role_name'],
            $data['role_description'] ?? null,
            $data['role_level'] ?? 1,
            $data['is_active'] ?? true,
            $roleId
        ]);
        return $stmt->rowCount() > 0;
    }
      /**
     * Delete role (soft delete by setting inactive)
     */
    public function deleteRole($roleId) {
        $sql = "UPDATE roles SET is_active = FALSE, updated_at = NOW() WHERE role_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get roles available for assignment (based on assigner's role level)
     */
    public function getAssignableRoles($assignerUserId) {
        // Get assigner's highest role level
        $sql = "SELECT MAX(r.role_level) as max_level
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND ur.is_active = TRUE AND r.is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$assignerUserId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxLevel = $result['max_level'] ?? 0;
        
        // Return roles with level less than or equal to assigner's level
        $sql = "SELECT * FROM roles 
                WHERE role_level <= ? AND is_active = TRUE 
                ORDER BY role_level DESC, role_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$maxLevel]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get role permissions
     */
    public function getRolePermissions($roleId) {
        $sql = "SELECT p.*, rp.granted, rp.granted_at
                FROM permissions p
                LEFT JOIN role_permissions rp ON p.permission_id = rp.permission_id                AND rp.role_id = ? AND rp.granted = TRUE
                ORDER BY p.module, p.category, p.permission_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Set role permissions
     */
    public function setRolePermissions($roleId, $permissions, $grantedBy = null) {
        try {
            $this->conn->beginTransaction();
            
            // Remove existing permissions
            $sql = "DELETE FROM role_permissions WHERE role_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$roleId]);
            
            // Insert new permissions
            foreach ($permissions as $permissionId => $granted) {
                if ($granted) {
                    $sql = "INSERT INTO role_permissions (role_id, permission_id, granted, granted_at, granted_by) 
                            VALUES (?, ?, TRUE, NOW(), ?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$roleId, $permissionId, $grantedBy]);
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
      /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $permissionCode) {
        $sql = "SELECT COUNT(*) as cnt
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.permission_id
                WHERE rp.role_id = ? AND p.permission_code = ? AND rp.granted = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$roleId, $permissionCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['cnt'] > 0;
    }
    
    /**
     * Get role hierarchy (roles that can manage other roles)
     */
    public function getRoleHierarchy() {
        $sql = "SELECT r1.role_id as parent_role_id, r1.role_name as parent_role_name,
                       r2.role_id as child_role_id, r2.role_name as child_role_name
                FROM roles r1, roles r2
                WHERE r1.role_level > r2.role_level
                  AND r1.is_active = TRUE AND r2.is_active = TRUE
                ORDER BY r1.role_level DESC, r2.role_level DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Get roles by permission
     */
    public function getRolesByPermission($permissionCode) {
        $sql = "SELECT DISTINCT r.*
                FROM roles r
                JOIN role_permissions rp ON r.role_id = rp.role_id
                JOIN permissions p ON rp.permission_id = p.permission_id
                WHERE p.permission_code = ? AND rp.granted = TRUE AND r.is_active = TRUE
                ORDER BY r.role_level DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$permissionCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get role statistics
     */
    public function getRoleStatistics() {
        $sql = "SELECT 
                    r.role_id,
                    r.role_code,
                    r.role_name,
                    r.role_level,
                    COUNT(DISTINCT ur.user_id) as user_count,
                    COUNT(DISTINCT rp.permission_id) as permission_count,
                    COUNT(CASE WHEN ur.is_primary = TRUE THEN 1 END) as primary_assignments
                FROM roles r
                LEFT JOIN user_roles ur ON r.role_id = ur.role_id AND ur.is_active = TRUE
                LEFT JOIN role_permissions rp ON r.role_id = rp.role_id AND rp.granted = TRUE
                WHERE r.is_active = TRUE
                GROUP BY r.role_id, r.role_code, r.role_name, r.role_level
                ORDER BY r.role_level DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
