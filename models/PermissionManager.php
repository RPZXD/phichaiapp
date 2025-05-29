<?php
// models/PermissionManager.php
require_once __DIR__ . '/../config/Database.php';

class PermissionManager {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Database();
    }
    
    /**
     * Get all permissions
     */
    public function getAllPermissions($module = null, $category = null) {
        $whereConditions = [];
        $params = [];
        
        if ($module) {
            $whereConditions[] = "module = ?";
            $params[] = $module;
        }
        
        if ($category) {
            $whereConditions[] = "category = ?";
            $params[] = $category;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "SELECT * FROM permissions {$whereClause} 
                ORDER BY module, category, permission_name";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get permissions grouped by module
     */
    public function getPermissionsByModule() {
        $sql = "SELECT * FROM permissions ORDER BY module, category, permission_name";
        $stmt = $this->db->query($sql);
        $permissions = $stmt->fetchAll();
        
        $grouped = [];
        foreach ($permissions as $permission) {
            $module = $permission['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }
        
        return $grouped;
    }
    
    /**
     * Get permission by ID
     */
    public function getPermissionById($permissionId) {
        $sql = "SELECT * FROM permissions WHERE permission_id = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$permissionId]);
        return $stmt->fetch();
    }
    
    /**
     * Get permission by code
     */
    public function getPermissionByCode($permissionCode) {
        $sql = "SELECT * FROM permissions WHERE permission_code = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$permissionCode]);
        return $stmt->fetch();
    }
    
    /**
     * Create new permission
     */
    public function createPermission($data) {
        $sql = "INSERT INTO permissions (permission_code, permission_name, permission_description, 
                module, category, is_system) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->query($sql, [
            $data['permission_code'],
            $data['permission_name'],
            $data['permission_description'] ?? null,
            $data['module'],
            $data['category'] ?? 'general',
            $data['is_system'] ?? false
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update permission
     */
    public function updatePermission($permissionId, $data) {
        $sql = "UPDATE permissions 
                SET permission_code = ?, permission_name = ?, permission_description = ?, 
                    module = ?, category = ?, updated_at = NOW()
                WHERE permission_id = ? AND is_system = FALSE";
        $stmt = $this->db->query($sql, [
            $data['permission_code'],
            $data['permission_name'],
            $data['permission_description'] ?? null,
            $data['module'],
            $data['category'] ?? 'general',
            $permissionId
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete permission (only non-system permissions)
     */
    public function deletePermission($permissionId) {
        $sql = "DELETE FROM permissions WHERE permission_id = ? AND is_system = FALSE";
        $stmt = $this->db->query($sql, [$permissionId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get available modules
     */
    public function getModules() {
        $sql = "SELECT DISTINCT module FROM permissions ORDER BY module";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get available categories
     */
    public function getCategories($module = null) {
        $whereClause = $module ? 'WHERE module = ?' : '';
        $params = $module ? [$module] : [];
        
        $sql = "SELECT DISTINCT category FROM permissions {$whereClause} ORDER BY category";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get permission usage report
     */
    public function getPermissionUsageReport() {
        $sql = "SELECT 
                    p.permission_code,
                    p.permission_name,
                    p.module,
                    p.category,
                    COUNT(DISTINCT rp.role_id) as roles_count,
                    COUNT(DISTINCT up.user_id) as direct_users_count,
                    GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_level DESC SEPARATOR ', ') as assigned_roles
                FROM permissions p
                LEFT JOIN role_permissions rp ON p.permission_id = rp.permission_id AND rp.granted = TRUE
                LEFT JOIN roles r ON rp.role_id = r.role_id AND r.is_active = TRUE
                LEFT JOIN user_permissions up ON p.permission_id = up.permission_id AND up.granted = TRUE
                GROUP BY p.permission_id, p.permission_code, p.permission_name, p.module, p.category
                ORDER BY roles_count DESC, direct_users_count DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
