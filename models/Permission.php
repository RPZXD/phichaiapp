<?php
// models/Permission.php
require_once __DIR__ . '/../config/Database.php';

class Permission {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Database();
    }
    
    /**
     * Get user permissions as array
     */
    public function getUserPermissions($user_id) {
        $sql = "SELECT permission_code FROM user_permissions WHERE user_id = ? AND granted = 1";
        $stmt = $this->db->query($sql, [$user_id]);
        $permissions = [];
        while ($row = $stmt->fetch()) {
            $permissions[] = $row['permission_code'];
        }
        return $permissions;
    }
    
    /**
     * Get detailed user permissions with metadata
     */
    public function getUserPermissionsDetailed($user_id) {
        $sql = "SELECT permission_code, granted, granted_at FROM user_permissions WHERE user_id = ?";
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if user has specific permission
     */
    public function hasPermission($user_id, $permission_code) {
        $sql = "SELECT COUNT(*) as count FROM user_permissions WHERE user_id = ? AND permission_code = ? AND granted = 1";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Grant permission to user
     */
    public function grantPermission($user_id, $permission_code) {
        $sql = "INSERT INTO user_permissions (user_id, permission_code, granted, granted_at) 
                VALUES (?, ?, 1, NOW()) 
                ON DUPLICATE KEY UPDATE granted = 1, granted_at = NOW()";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Revoke permission from user
     */
    public function revokePermission($user_id, $permission_code) {
        $sql = "DELETE FROM user_permissions WHERE user_id = ? AND permission_code = ?";
        $stmt = $this->db->query($sql, [$user_id, $permission_code]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get all available permission codes
     */
    public function getAllPermissionCodes() {
        return [
            // Student permissions
            'student.view', 'student.create', 'student.edit', 'student.delete',
            'student.cases.view', 'student.cases.create', 'student.cases.edit',
            'student.reports.view', 'student.reports.create',
            
            // Teacher permissions
            'teacher.view', 'teacher.create', 'teacher.edit', 'teacher.delete',
            'teacher.assignments.view', 'teacher.assignments.create', 'teacher.assignments.edit',
            
            // Report permissions
            'report.view', 'report.create', 'report.edit', 'report.delete',
            'analytics.view', 'statistics.view',
            
            // System permissions
            'system.settings.view', 'system.settings.edit',
            'system.backup.view', 'system.backup.create',
            'system.logs.view',
            
            // User management permissions
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'user.permissions.view', 'user.permissions.edit',
            
            // Admin permissions
            'admin.full.access', 'admin.system.manage'
        ];
    }
    
    /**
     * Get permission categories
     */
    public function getPermissionCategories() {
        return [
            'student' => [
                'name' => 'Student Management',
                'permissions' => [
                    'student.view', 'student.create', 'student.edit', 'student.delete',
                    'student.cases.view', 'student.cases.create', 'student.cases.edit',
                    'student.reports.view', 'student.reports.create'
                ]
            ],
            'teacher' => [
                'name' => 'Teacher Management',
                'permissions' => [
                    'teacher.view', 'teacher.create', 'teacher.edit', 'teacher.delete',
                    'teacher.assignments.view', 'teacher.assignments.create', 'teacher.assignments.edit'
                ]
            ],
            'report' => [
                'name' => 'Reports & Analytics',
                'permissions' => [
                    'report.view', 'report.create', 'report.edit', 'report.delete',
                    'analytics.view', 'statistics.view'
                ]
            ],
            'system' => [
                'name' => 'System Administration',
                'permissions' => [
                    'system.settings.view', 'system.settings.edit',
                    'system.backup.view', 'system.backup.create',
                    'system.logs.view'
                ]
            ],
            'user' => [
                'name' => 'User Management',
                'permissions' => [
                    'user.view', 'user.create', 'user.edit', 'user.delete',
                    'user.permissions.view', 'user.permissions.edit'
                ]
            ],
            'admin' => [
                'name' => 'Administrative',
                'permissions' => [
                    'admin.full.access', 'admin.system.manage'
                ]
            ]
        ];
    }
}
