<?php
// models/Authorization.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/UserPermission.php';
require_once __DIR__ . '/Role.php';

class Authorization {
    private $db;
    private $userPermission;
    private $role;
    private $cache = [];
    
    public function __construct() {
        $this->db = new \App\Database();
        $this->userPermission = new UserPermission();
        $this->role = new Role();
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($user_id, $permission_code) {
        $cacheKey = "user_{$user_id}_perm_{$permission_code}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $result = $this->userPermission->hasPermission($user_id, $permission_code);
        $this->cache[$cacheKey] = $result;
        return $result;
    }
    
    /**
     * Check multiple permissions at once
     */
    public function hasPermissions($user_id, $permission_codes) {
        return $this->userPermission->hasPermissions($user_id, $permission_codes);
    }
    
    /**
     * Check if user has any of the specified permissions
     */
    public function hasAnyPermission($user_id, $permission_codes) {
        foreach ($permission_codes as $permission_code) {
            if ($this->hasPermission($user_id, $permission_code)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has all specified permissions
     */
    public function hasAllPermissions($user_id, $permission_codes) {
        foreach ($permission_codes as $permission_code) {
            if (!$this->hasPermission($user_id, $permission_code)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if user can access module
     */
    public function canAccessModule($user_id, $module) {
        $sql = "SELECT COUNT(*) as cnt FROM v_user_effective_permissions 
                WHERE user_id = ? AND module = ?";
        $stmt = $this->db->query($sql, [$user_id, $module]);
        $result = $stmt->fetch();
        return $result && $result['cnt'] > 0;
    }
    
    /**
     * Check role hierarchy - if user can manage target user
     */
    public function canManageUser($manager_id, $target_user_id) {
        return $this->userPermission->canManageUser($manager_id, $target_user_id);
    }
    
    /**
     * Check if user can assign specific role
     */
    public function canAssignRole($assigner_id, $role_id) {
        // Get assigner's assignable roles
        $assignableRoles = $this->role->getAssignableRoles($assigner_id);
        foreach ($assignableRoles as $role) {
            if ($role['role_id'] == $role_id) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user can manage specific permission
     */
    public function canManagePermission($user_id, $permission_code) {
        // Only users with user.manage_permissions can manage permissions
        return $this->hasPermission($user_id, 'user.manage_permissions');
    }
    
    /**
     * Get user's effective permissions with details
     */
    public function getUserPermissions($user_id, $module = null) {
        return $this->userPermission->getUserPermissionsByModule($user_id, $module);
    }
    
    /**
     * Check department access
     */
    public function canAccessDepartment($user_id, $department_id) {
        // Users can access their own department and higher role levels can access all
        $sql = "SELECT 
                    u.department_id as user_dept,
                    MAX(r.role_level) as max_role_level
                FROM users u
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id AND r.is_active = TRUE
                WHERE u.user_id = ?
                GROUP BY u.user_id, u.department_id";
        $stmt = $this->db->query($sql, [$user_id]);
        $result = $stmt->fetch();
        
        if (!$result) return false;
        
        // Admin level (100) and Director level (90+) can access all departments
        if ($result['max_role_level'] >= 90) {
            return true;
        }
        
        // Users can access their own department
        return $result['user_dept'] == $department_id;
    }
    
    /**
     * Check if user can view/edit specific user data
     */
    public function canAccessUserData($accessor_id, $target_user_id, $action = 'view') {
        // Users can always access their own data
        if ($accessor_id == $target_user_id) {
            return true;
        }
        
        // Check if accessor can manage the target user
        if (!$this->canManageUser($accessor_id, $target_user_id)) {
            return false;
        }
        
        // Check specific permission based on action
        $permission_map = [
            'view' => 'user.view',
            'edit' => 'user.edit',
            'delete' => 'user.delete'
        ];
        
        if (isset($permission_map[$action])) {
            return $this->hasPermission($accessor_id, $permission_map[$action]);
        }
        
        return false;
    }
    
    /**
     * Check student data access (for parents/students)
     */
    public function canAccessStudentData($accessor_id, $student_id, $action = 'view') {
        // Check if accessor is the student themselves
        if ($accessor_id == $student_id) {
            return $this->hasPermission($accessor_id, 'student.view_own_data');
        }
        
        // Check if accessor is parent of the student
        if ($this->isParentOfStudent($accessor_id, $student_id)) {
            return $this->hasPermission($accessor_id, 'student.view_child_data');
        }
        
        // Check normal user permissions
        $permission_map = [
            'view' => 'student.view',
            'edit' => 'student.edit',
            'delete' => 'student.delete',
            'grades' => 'student.view_grades'
        ];
        
        if (isset($permission_map[$action])) {
            return $this->hasPermission($accessor_id, $permission_map[$action]);
        }
        
        return false;
    }
    
    /**
     * Check if user is parent of student (implement based on your parent-student relationship)
     */
    private function isParentOfStudent($parent_id, $student_id) {
        // This should be implemented based on your parent-student relationship table
        // For now, return false as placeholder
        // TODO: Implement parent-student relationship check
        return false;
    }
    
    /**
     * Get accessible departments for user
     */
    public function getAccessibleDepartments($user_id) {
        $sql = "SELECT DISTINCT d.*
                FROM departments d
                LEFT JOIN users u ON u.user_id = ?
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id AND r.is_active = TRUE
                WHERE d.is_active = TRUE
                  AND (
                    r.role_level >= 90 OR  -- Admin/Director level
                    d.department_id = u.department_id OR  -- Own department
                    d.department_id = ur.department_id  -- Role-specific department
                  )
                ORDER BY d.department_name";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check menu/feature access based on permissions
     */
    public function canAccessMenu($user_id, $menu_code) {
        // Define menu permission mappings
        $menu_permissions = [
            'users' => ['user.view'],
            'students' => ['student.view'],
            'reports' => ['report.view_basic'],
            'system' => ['system.view_logs', 'system.manage_settings'],
            'departments' => ['department.view']
        ];
        
        if (isset($menu_permissions[$menu_code])) {
            return $this->hasAnyPermission($user_id, $menu_permissions[$menu_code]);
        }
        
        return false;
    }
    
    /**
     * Get user's role hierarchy level
     */
    public function getUserMaxRoleLevel($user_id) {
        $sql = "SELECT MAX(r.role_level) as max_level
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND ur.is_active = TRUE AND r.is_active = TRUE";
        $stmt = $this->db->query($sql, [$user_id]);
        $result = $stmt->fetch();
        return $result['max_level'] ?? 0;
    }
    
    /**
     * Clear permission cache
     */
    public function clearCache($user_id = null) {
        if ($user_id) {
            // Clear cache for specific user
            foreach ($this->cache as $key => $value) {
                if (strpos($key, "user_{$user_id}_") === 0) {
                    unset($this->cache[$key]);
                }
            }
        } else {
            // Clear all cache
            $this->cache = [];
        }
    }
    
    /**
     * Check permission with detailed response
     */
    public function checkPermissionDetailed($user_id, $permission_code) {
        $sql = "SELECT 
                    permission_source,
                    source_detail,
                    permission_name,
                    module,
                    category
                FROM v_user_effective_permissions 
                WHERE user_id = ? AND permission_code = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        return $stmt->fetchAll();
    }
}
