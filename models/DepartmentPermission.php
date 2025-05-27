<?php
// models/DepartmentPermission.php
require_once __DIR__ . '/../config/Database.php';

class DepartmentPermission {
    private $db;
    
    public function __construct() {
        $this->db = new \App\Database();
    }
    
    /**
     * ตรวจสอบสิทธิ์ของผู้ใช้ตามฝ่ายและตำแหน่ง
     */
    public function hasPermission($user_id, $module_name, $action_code) {
        $sql = "SELECT has_permission FROM user_permissions_view 
                WHERE user_id = ? AND module_name = ? AND action_code = ? 
                ORDER BY permission_source DESC LIMIT 1";
        
        $stmt = $this->db->query($sql, [$user_id, $module_name, $action_code]);
        $result = $stmt->fetch();
        
        return $result ? (bool)$result['has_permission'] : false;
    }
    
    /**
     * ดึงสิทธิ์ทั้งหมดของผู้ใช้
     */
    public function getUserPermissions($user_id) {
        $sql = "SELECT module_name, action_code, has_permission, permission_source 
                FROM user_permissions_view 
                WHERE user_id = ? AND has_permission = TRUE
                ORDER BY module_name, action_code";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงข้อมูลฝ่ายทั้งหมด
     */
    public function getAllDepartments() {
        $sql = "SELECT * FROM departments WHERE is_active = TRUE ORDER BY name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงข้อมูลตำแหน่งในฝ่าย
     */
    public function getDepartmentRoles($department_id = null) {
        $sql = "SELECT dr.*, d.name as department_name 
                FROM department_roles dr 
                JOIN departments d ON dr.department_id = d.id 
                WHERE dr.is_active = TRUE";
        $params = [];
        
        if ($department_id) {
            $sql .= " AND dr.department_id = ?";
            $params[] = $department_id;
        }
        
        $sql .= " ORDER BY d.name, dr.role_level";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงโมดูลทั้งหมด
     */
    public function getAllModules() {
        $sql = "SELECT * FROM permission_modules WHERE is_active = TRUE ORDER BY sort_order, module_name_th";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงการกระทำทั้งหมด
     */
    public function getAllActions() {
        $sql = "SELECT * FROM permission_actions WHERE is_active = TRUE ORDER BY action_code";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ดึงสิทธิ์ของฝ่าย
     */
    public function getDepartmentPermissions($department_id) {
        $sql = "SELECT dp.*, pm.module_name, pm.module_name_th, 
                       pa.action_code, pa.action_name_th,
                       dr.role_name, dr.role_level
                FROM department_permissions dp
                JOIN permission_modules pm ON dp.module_id = pm.id
                JOIN permission_actions pa ON dp.action_id = pa.id
                LEFT JOIN department_roles dr ON dp.department_id = dr.department_id 
                    AND dp.role_level_required = dr.role_level
                WHERE dp.department_id = ?
                ORDER BY pm.sort_order, pa.action_code";
        
        $stmt = $this->db->query($sql, [$department_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * อัปเดตสิทธิ์ของฝ่าย
     */
    public function updateDepartmentPermission($department_id, $module_id, $action_id, $role_level_required, $is_granted, $conditions = null) {
        $sql = "INSERT INTO department_permissions 
                (department_id, module_id, action_id, role_level_required, is_granted, conditions)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                is_granted = VALUES(is_granted),
                conditions = VALUES(conditions),
                updated_at = CURRENT_TIMESTAMP";
        
        $params = [$department_id, $module_id, $action_id, $role_level_required, $is_granted, 
                  $conditions ? json_encode($conditions) : null];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * กำหนดฝ่ายให้ผู้ใช้
     */
    public function assignUserToDepartment($user_id, $department_id, $role_id, $is_primary = true, $start_date = null) {
        // ถ้าเป็นฝ่ายหลัก ให้ปรับปรุงฝ่ายอื่นให้ไม่เป็นหลัก
        if ($is_primary) {
            $sql = "UPDATE user_departments SET is_primary = FALSE WHERE user_id = ?";
            $this->db->query($sql, [$user_id]);
        }
        
        $sql = "INSERT INTO user_departments 
                (user_id, department_id, role_id, is_primary, start_date, is_active)
                VALUES (?, ?, ?, ?, ?, TRUE)
                ON DUPLICATE KEY UPDATE 
                role_id = VALUES(role_id),
                is_primary = VALUES(is_primary),
                start_date = VALUES(start_date),
                is_active = TRUE,
                updated_at = CURRENT_TIMESTAMP";
        
        $params = [$user_id, $department_id, $role_id, $is_primary, $start_date ?: date('Y-m-d')];
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * ดึงข้อมูลฝ่ายของผู้ใช้
     */
    public function getUserDepartments($user_id) {
        $sql = "SELECT ud.*, d.name as department_name, d.name_en, d.color,
                       dr.role_name, dr.role_level, dr.description as role_description
                FROM user_departments ud
                JOIN departments d ON ud.department_id = d.id
                JOIN department_roles dr ON ud.role_id = dr.id
                WHERE ud.user_id = ? AND ud.is_active = TRUE
                ORDER BY ud.is_primary DESC, d.name";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * กำหนดสิทธิ์พิเศษให้ผู้ใช้
     */
    public function grantCustomPermission($user_id, $module_id, $action_id, $is_granted, $granted_by, $expires_at = null, $reason = null) {
        $sql = "INSERT INTO user_custom_permissions 
                (user_id, module_id, action_id, is_granted, granted_by, expires_at, reason)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                is_granted = VALUES(is_granted),
                granted_by = VALUES(granted_by),
                granted_at = CURRENT_TIMESTAMP,
                expires_at = VALUES(expires_at),
                reason = VALUES(reason),
                is_active = TRUE";
        
        $stmt = $this->db->query($sql, [$user_id, $module_id, $action_id, $is_granted, $granted_by, $expires_at, $reason]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * ดึงสิทธิ์พิเศษของผู้ใช้
     */
    public function getUserCustomPermissions($user_id) {
        $sql = "SELECT ucp.*, pm.module_name_th, pa.action_name_th,
                       u.username as granted_by_username
                FROM user_custom_permissions ucp
                JOIN permission_modules pm ON ucp.module_id = pm.id
                JOIN permission_actions pa ON ucp.action_id = pa.id
                LEFT JOIN users u ON ucp.granted_by = u.user_id
                WHERE ucp.user_id = ? AND ucp.is_active = TRUE
                ORDER BY ucp.granted_at DESC";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * สร้างเทมเพลตสิทธิ์ตามฝ่าย
     */
    public function createDepartmentPermissionTemplate($department_name) {
        $templates = $this->getPermissionTemplates();
        
        if (!isset($templates[$department_name])) {
            return false;
        }
        
        $template = $templates[$department_name];
        $department = $this->getDepartmentByName($department_name);
        
        if (!$department) {
            return false;
        }
        
        $modules = $this->getAllModules();
        $actions = $this->getAllActions();
        
        // สร้าง mapping
        $moduleMap = [];
        foreach ($modules as $module) {
            $moduleMap[$module['module_name']] = $module['id'];
        }
        
        $actionMap = [];
        foreach ($actions as $action) {
            $actionMap[$action['action_code']] = $action['id'];
        }
        
        // สร้างสิทธิ์ตามเทมเพลต
        foreach ($template as $module => $permissions) {
            if (!isset($moduleMap[$module])) continue;
            
            foreach ($permissions as $action => $levels) {
                if (!isset($actionMap[$action])) continue;
                
                foreach ($levels as $level => $granted) {
                    if ($granted) {
                        $this->updateDepartmentPermission(
                            $department['id'],
                            $moduleMap[$module],
                            $actionMap[$action],
                            $level,
                            true
                        );
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * ดึงข้อมูลฝ่ายจากชื่อ
     */
    private function getDepartmentByName($name) {
        $sql = "SELECT * FROM departments WHERE name = ? LIMIT 1";
        $stmt = $this->db->query($sql, [$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * เทมเพลตสิทธิ์สำหรับแต่ละฝ่าย
     */
    private function getPermissionTemplates() {
        return [
            'งานวิชาการ' => [
                'student_management' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true],
                    'delete' => [2 => true, 3 => true],
                    'export' => [1 => true, 2 => true, 3 => true]
                ],
                'teacher_management' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [2 => true, 3 => true],
                    'edit' => [2 => true, 3 => true],
                    'delete' => [3 => true]
                ],
                'booking_system' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true],
                    'approve' => [2 => true, 3 => true]
                ]
            ],
            'งานงบประมาณ' => [
                'financial_reports' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true],
                    'delete' => [2 => true, 3 => true],
                    'approve' => [2 => true, 3 => true],
                    'export' => [1 => true, 2 => true, 3 => true]
                ],
                'booking_system' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'approve' => [2 => true, 3 => true]
                ],
                'car_booking' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'approve' => [2 => true, 3 => true]
                ]
            ],
            'งานบุคคล' => [
                'personnel_reports' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true],
                    'delete' => [2 => true, 3 => true],
                    'export' => [1 => true, 2 => true, 3 => true]
                ],
                'teacher_management' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true]
                ],
                'user_management' => [
                    'view' => [2 => true, 3 => true],
                    'create' => [2 => true, 3 => true],
                    'edit' => [2 => true, 3 => true]
                ]
            ],
            'งานทั่วไป' => [
                'repair_reports' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'create' => [1 => true, 2 => true, 3 => true],
                    'edit' => [1 => true, 2 => true, 3 => true],
                    'delete' => [2 => true, 3 => true],
                    'approve' => [2 => true, 3 => true]
                ],
                'booking_system' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'manage' => [2 => true, 3 => true]
                ],
                'car_booking' => [
                    'view' => [1 => true, 2 => true, 3 => true],
                    'manage' => [2 => true, 3 => true]
                ],
                'system_settings' => [
                    'view' => [2 => true, 3 => true],
                    'edit' => [3 => true]
                ]
            ]
        ];
    }
    
    /**
     * ตรวจสอบสิทธิ์แบบรวดเร็ว
     */
    public function checkPermission($user_id, $module_action) {
        list($module, $action) = explode('.', $module_action, 2);
        return $this->hasPermission($user_id, $module, $action);
    }
    
    /**
     * ดึงเมนูที่ผู้ใช้มีสิทธิ์เข้าถึง
     */
    public function getUserAccessibleMenus($user_id) {
        $sql = "SELECT DISTINCT pm.module_name, pm.module_name_th, pm.icon
                FROM user_permissions_view upv
                JOIN permission_modules pm ON upv.module_name = pm.module_name
                WHERE upv.user_id = ? AND upv.has_permission = TRUE 
                AND upv.action_code = 'view'
                ORDER BY pm.sort_order";
        
        $stmt = $this->db->query($sql, [$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
