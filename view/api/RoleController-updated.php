<?php
require_once(__DIR__ . '/../../config/Database.php');

use App\Database;

/**
 * Enhanced Role Controller for 4-Department Permission System
 * Author: ICT Team @Phichai School
 * Date: December 2024
 */
class RoleController {
    private $db;
    private $conn;

    // กำหนดฝ่ายงาน 4 ฝ่าย
    private $departments = [
        'academic' => [
            'name' => 'งานวิชาการ',
            'modules' => [
                'curriculum' => 'หลักสูตร',
                'schedule' => 'ตารางเรียน',
                'assessment' => 'การประเมิน',
                'student_report' => 'รายงานนักเรียน',
                'teaching_materials' => 'สื่อการเรียน',
                'examination' => 'การสอบ'
            ]
        ],
        'budget' => [
            'name' => 'งานงบประมาณ',
            'modules' => [
                'budget_plan' => 'แผนงบประมาณ',
                'expenses' => 'รายจ่าย',
                'procurement' => 'จัดซื้อจัดจ้าง',
                'finance_report' => 'รายงานการเงิน',
                'invoice' => 'ใบแจ้งหนี้',
                'payment' => 'การจ่ายเงิน'
            ]
        ],
        'personnel' => [
            'name' => 'งานบุคคล',
            'modules' => [
                'staff_management' => 'จัดการบุคลากร',
                'attendance' => 'ลงเวลาทำงาน',
                'leave_management' => 'จัดการลาหยุด',
                'payroll' => 'เงินเดือน',
                'performance' => 'ประเมินผลงาน',
                'hr_report' => 'รายงานบุคลากร'
            ]
        ],
        'general' => [
            'name' => 'งานทั่วไป',
            'modules' => [
                'facility' => 'สิ่งอำนวยความสะดวก',
                'maintenance' => 'ซ่อมบำรุง',
                'inventory' => 'พัสดุ',
                'document' => 'เอกสาร',
                'communication' => 'ประชาสัมพันธ์',
                'general_report' => 'รายงานทั่วไป'
            ]
        ]
    ];

    // กำหนดบทบาทและระดับสิทธิ์ (5 = สูงสุด, 1 = ต่ำสุด)
    private $roles = [
        'admin' => ['name' => 'Admin', 'level' => 5, 'color' => 'purple'],
        'head' => ['name' => 'หัวหน้าฝ่าย', 'level' => 4, 'color' => 'green'],
        'officer' => ['name' => 'เจ้าหน้าที่', 'level' => 3, 'color' => 'blue'],
        'teacher' => ['name' => 'ครู', 'level' => 2, 'color' => 'orange'],
        'viewer' => ['name' => 'ผู้ชม', 'level' => 1, 'color' => 'red']
    ];

    // กำหนดสิทธิ์ตามบทบาท (ระดับสูงกว่าเข้าถึงระดับต่ำกว่าได้)
    private $rolePermissions = [
        'admin' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
        'head' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
        'officer' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
        'teacher' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
        'viewer' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false]
    ];

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->createTablesIfNotExists();
    }

    public function handleRequest() {
        // Set headers for JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'getUserPermissions':
                    $this->getUserPermissions();
                    break;
                case 'setUserRole':
                    $this->setUserRole($input);
                    break;
                case 'saveUserPermissions':
                    $this->saveUserPermissions($input);
                    break;
                case 'checkPermission':
                    $this->checkPermission($input);
                    break;
                case 'checkRole':
                    $this->checkRole($input);
                    break;
                case 'getDepartments':
                    $this->getDepartments();
                    break;
                case 'getRoles':
                    $this->getRoles();
                    break;
                case 'getUserRoles':
                    $this->getUserRoles();
                    break;
                case 'getUsers':
                    $this->getUsers();
                    break;
                case 'getUserStatistics':
                    $this->getUserStatistics();
                    break;
                case 'deleteUserRole':
                    $this->deleteUserRole($input);
                    break;
                case 'getPermissionLogs':
                    $this->getPermissionLogs();
                    break;
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * สร้างตารางฐานข้อมูลหากยังไม่มี
     */
    private function createTablesIfNotExists() {
        // ตารางสำหรับเก็บบทบาทของผู้ใช้ในแต่ละฝ่าย
        $createUserRoles = "
            CREATE TABLE IF NOT EXISTS user_roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                department VARCHAR(50) NOT NULL,
                role VARCHAR(50) NOT NULL,
                assigned_by INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_dept (user_id, department),
                INDEX idx_user_dept (user_id, department),
                INDEX idx_role (role)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // ตารางสำหรับเก็บสิทธิ์เฉพาะของผู้ใช้
        $createUserPermissions = "
            CREATE TABLE IF NOT EXISTS user_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                department VARCHAR(50) NOT NULL,
                module VARCHAR(50) NOT NULL,
                permissions JSON NOT NULL,
                assigned_by INT NOT NULL,
                assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_dept_module (user_id, department, module),
                INDEX idx_user_dept_module (user_id, department, module)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // ตารางสำหรับ log การเปลี่ยนแปลงสิทธิ์
        $createPermissionLogs = "
            CREATE TABLE IF NOT EXISTS permission_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                department VARCHAR(50) NOT NULL,
                module VARCHAR(50),
                action VARCHAR(50) NOT NULL,
                old_value JSON,
                new_value JSON,
                changed_by INT NOT NULL,
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ip_address VARCHAR(45),
                user_agent TEXT,
                INDEX idx_user_id (user_id),
                INDEX idx_department (department),
                INDEX idx_changed_by (changed_by),
                INDEX idx_changed_at (changed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // ตารางสำหรับกำหนดโมดูลของแต่ละฝ่าย
        $createDepartmentModules = "
            CREATE TABLE IF NOT EXISTS department_modules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                department VARCHAR(50) NOT NULL,
                module VARCHAR(50) NOT NULL,
                module_name VARCHAR(100) NOT NULL,
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_dept_module (department, module),
                INDEX idx_department (department),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // ตารางสำหรับกำหนดบทบาทและสิทธิ์
        $createRoleDefinitions = "
            CREATE TABLE IF NOT EXISTS role_definitions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role VARCHAR(50) NOT NULL UNIQUE,
                role_name VARCHAR(100) NOT NULL,
                level INT NOT NULL,
                permissions JSON NOT NULL,
                description TEXT,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_role (role),
                INDEX idx_level (level),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

        // สร้าง View สำหรับดูข้อมูลสิทธิ์แบบรวม
        $createUserPermissionsView = "
            CREATE OR REPLACE VIEW v_user_permissions AS
            SELECT 
                u.id as user_id,
                u.name as user_name,
                u.email as user_email,
                ur.department,
                ur.role,
                rd.role_name,
                rd.level as role_level,
                rd.permissions as role_permissions,
                up.module,
                up.permissions as module_permissions,
                CASE 
                    WHEN ur.role IS NOT NULL THEN 'active'
                    ELSE 'inactive'
                END as status
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN role_definitions rd ON ur.role = rd.role
            LEFT JOIN user_permissions up ON u.id = up.user_id 
                AND ur.department = up.department
            WHERE u.status = 'active'
        ";

        // Execute queries
        $queries = [
            $createUserRoles,
            $createUserPermissions,
            $createPermissionLogs,
            $createDepartmentModules,
            $createRoleDefinitions
        ];

        foreach ($queries as $query) {
            if (!$this->conn->query($query)) {
                throw new Exception("Error creating tables: " . $this->conn->error);
            }
        }

        // Execute view creation
        if (!$this->conn->query($createUserPermissionsView)) {
            error_log("Warning: Could not create view: " . $this->conn->error);
        }

        // Insert default data
        $this->insertDefaultData();
    }

    /**
     * เพิ่มข้อมูลเริ่มต้น
     */
    private function insertDefaultData() {
        // เพิ่มข้อมูลบทบาท
        foreach ($this->roles as $roleKey => $roleData) {
            $permissions = json_encode($this->rolePermissions[$roleKey]);
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO role_definitions (role, role_name, level, permissions, description) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $description = "บทบาท " . $roleData['name'] . " (ระดับ " . $roleData['level'] . ")";
            $stmt->bind_param("ssiss", $roleKey, $roleData['name'], $roleData['level'], $permissions, $description);
            $stmt->execute();
        }

        // เพิ่มข้อมูลโมดูลของแต่ละฝ่าย
        foreach ($this->departments as $deptKey => $deptData) {
            foreach ($deptData['modules'] as $moduleKey => $moduleName) {
                $stmt = $this->conn->prepare("
                    INSERT IGNORE INTO department_modules (department, module, module_name, description) 
                    VALUES (?, ?, ?, ?)
                ");
                $description = "โมดูล " . $moduleName . " ของ" . $deptData['name'];
                $stmt->bind_param("ssss", $deptKey, $moduleKey, $moduleName, $description);
                $stmt->execute();
            }
        }
    }

    /**
     * ดึงข้อมูลสิทธิ์ของผู้ใช้ทั้งหมด
     */
    public function getUserPermissions() {
        $userId = $_GET['userId'] ?? null;
        
        if ($userId) {
            // ดึงข้อมูลสิทธิ์ของผู้ใช้คนเดียว
            $userPermissions = $this->getUserPermissionsByUserId($userId);
            echo json_encode([
                'success' => true,
                'data' => $userPermissions
            ]);
        } else {
            // ดึงข้อมูลสิทธิ์ของผู้ใช้ทั้งหมด
            $stmt = $this->conn->prepare("
                SELECT user_id, department, role, assigned_at, updated_at
                FROM user_roles 
                ORDER BY user_id, department
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $permissions = [];
            while ($row = $result->fetch_assoc()) {
                $userId = $row['user_id'];
                $department = $row['department'];
                
                if (!isset($permissions[$userId])) {
                    $permissions[$userId] = [];
                }
                
                $permissions[$userId][$department] = [
                    'role' => $row['role'],
                    'assigned_at' => $row['assigned_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $permissions
            ]);
        }
    }

    /**
     * ดึงข้อมูลสิทธิ์ของผู้ใช้คนหนึ่ง
     */
    private function getUserPermissionsByUserId($userId) {
        $permissions = [];
        
        // ดึงบทบาทในแต่ละฝ่าย
        $stmt = $this->conn->prepare("
            SELECT department, role 
            FROM user_roles 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $permissions[$row['department']] = [
                'role' => $row['role'],
                'permissions' => []
            ];
        }
        
        // ดึงสิทธิ์เฉพาะของแต่ละโมดูล
        $stmt = $this->conn->prepare("
            SELECT department, module, permissions 
            FROM user_permissions 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            if (!isset($permissions[$row['department']])) {
                $permissions[$row['department']] = ['role' => '', 'permissions' => []];
            }
            $permissions[$row['department']]['permissions'][$row['module']] = 
                json_decode($row['permissions'], true);
        }
        
        return $permissions;
    }

    /**
     * กำหนดบทบาทให้ผู้ใช้ในฝ่ายต่างๆ
     */
    public function setUserRole($input) {
        $userId = $input['userId'] ?? null;
        $department = $input['department'] ?? null;
        $role = $input['role'] ?? null;
        $assignedBy = $input['assignedBy'] ?? 1; // Default admin

        if (!$userId || !$department) {
            throw new Exception('User ID and department are required');
        }

        $this->conn->begin_transaction();

        try {
            if ($role) {
                // เพิ่มหรืออัพเดทบทบาท
                $stmt = $this->conn->prepare("
                    INSERT INTO user_roles (user_id, department, role, assigned_by) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    role = VALUES(role), 
                    assigned_by = VALUES(assigned_by),
                    updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->bind_param("issi", $userId, $department, $role, $assignedBy);
                $stmt->execute();

                // Log การเปลี่ยนแปลง
                $this->logPermissionChange($userId, $department, null, 'set_role', null, $role, $assignedBy);
            } else {
                // ลบบทบาท
                $stmt = $this->conn->prepare("DELETE FROM user_roles WHERE user_id = ? AND department = ?");
                $stmt->bind_param("is", $userId, $department);
                $stmt->execute();

                // Log การเปลี่ยนแปลง
                $this->logPermissionChange($userId, $department, null, 'remove_role', $role, null, $assignedBy);
            }

            $this->conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Role updated successfully'
            ]);

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * บันทึกสิทธิ์ของผู้ใช้
     */
    public function saveUserPermissions($input) {
        $permissions = $input['permissions'] ?? [];
        $assignedBy = $input['assignedBy'] ?? 1;

        if (empty($permissions)) {
            throw new Exception('No permissions provided');
        }

        $this->conn->begin_transaction();

        try {
            foreach ($permissions as $userId => $userPermissions) {
                foreach ($userPermissions as $department => $deptData) {
                    // บันทึกบทบาท
                    if (!empty($deptData['role'])) {
                        $stmt = $this->conn->prepare("
                            INSERT INTO user_roles (user_id, department, role, assigned_by) 
                            VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            role = VALUES(role), 
                            assigned_by = VALUES(assigned_by),
                            updated_at = CURRENT_TIMESTAMP
                        ");
                        $stmt->bind_param("issi", $userId, $department, $deptData['role'], $assignedBy);
                        $stmt->execute();
                    }

                    // บันทึกสิทธิ์เฉพาะ
                    if (!empty($deptData['permissions'])) {
                        foreach ($deptData['permissions'] as $module => $modulePermissions) {
                            $permissionsJson = json_encode($modulePermissions);
                            
                            $stmt = $this->conn->prepare("
                                INSERT INTO user_permissions (user_id, department, module, permissions, assigned_by) 
                                VALUES (?, ?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE 
                                permissions = VALUES(permissions), 
                                assigned_by = VALUES(assigned_by),
                                updated_at = CURRENT_TIMESTAMP
                            ");
                            $stmt->bind_param("isssi", $userId, $department, $module, $permissionsJson, $assignedBy);
                            $stmt->execute();

                            // Log การเปลี่ยนแปลง
                            $this->logPermissionChange($userId, $department, $module, 'update_permissions', 
                                null, $modulePermissions, $assignedBy);
                        }
                    }
                }
            }

            $this->conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'All permissions saved successfully'
            ]);

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * ตรวจสอบสิทธิ์ของผู้ใช้
     */
    public function checkPermission($input) {
        $userId = $input['userId'] ?? $_GET['userId'] ?? null;
        $department = $input['department'] ?? $_GET['department'] ?? null;
        $module = $input['module'] ?? $_GET['module'] ?? null;
        $permission = $input['permission'] ?? $_GET['permission'] ?? null;

        if (!$userId || !$department || !$module || !$permission) {
            throw new Exception('All parameters are required');
        }

        $hasPermission = $this->hasPermission($userId, $department, $module, $permission);
        
        echo json_encode([
            'success' => true,
            'hasPermission' => $hasPermission
        ]);
    }

    /**
     * ตรวจสอบบทบาทของผู้ใช้
     */
    public function checkRole($input) {
        $userId = $input['userId'] ?? $_GET['userId'] ?? null;
        $department = $input['department'] ?? $_GET['department'] ?? null;
        $minLevel = $input['minLevel'] ?? $_GET['minLevel'] ?? null;

        if (!$userId || !$department || !$minLevel) {
            throw new Exception('All parameters are required');
        }

        $hasRole = $this->hasMinimumRole($userId, $department, $minLevel);
        
        echo json_encode([
            'success' => true,
            'hasRole' => $hasRole
        ]);
    }

    /**
     * ดึงข้อมูลฝ่ายงาน
     */
    public function getDepartments() {
        echo json_encode([
            'success' => true,
            'data' => $this->departments
        ]);
    }

    /**
     * ดึงข้อมูลบทบาท
     */
    public function getRoles() {
        echo json_encode([
            'success' => true,
            'data' => $this->roles
        ]);
    }

    /**
     * ดึงข้อมูลบทบาทของผู้ใช้
     */
    public function getUserRoles() {
        $userId = $_GET['userId'] ?? null;

        if (!$userId) {
            throw new Exception('User ID is required');
        }

        $stmt = $this->conn->prepare("
            SELECT ur.department, ur.role, rd.role_name, rd.level 
            FROM user_roles ur
            JOIN role_definitions rd ON ur.role = rd.role
            WHERE ur.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[$row['department']] = [
                'role' => $row['role'],
                'role_name' => $row['role_name'],
                'level' => $row['level']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * ดึงข้อมูลผู้ใช้
     */
    public function getUsers() {
        $stmt = $this->conn->prepare("
            SELECT id, name, email, status 
            FROM users 
            WHERE status = 'active'
            ORDER BY name
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * ดึงสถิติผู้ใช้
     */
    public function getUserStatistics() {
        // นับผู้ใช้ทั้งหมด
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $stmt->execute();
        $totalUsers = $stmt->get_result()->fetch_assoc()['total'];

        // นับผู้ใช้ในแต่ละฝ่าย
        $departmentStats = [];
        foreach (array_keys($this->departments) as $department) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT user_id) as count 
                FROM user_roles 
                WHERE department = ?
            ");
            $stmt->bind_param("s", $department);
            $stmt->execute();
            $departmentStats[$department] = $stmt->get_result()->fetch_assoc()['count'];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'totalUsers' => $totalUsers,
                'departments' => $departmentStats
            ]
        ]);
    }

    /**
     * ลบบทบาทของผู้ใช้
     */
    public function deleteUserRole($input) {
        $userId = $input['userId'] ?? null;
        $department = $input['department'] ?? null;
        $deletedBy = $input['deletedBy'] ?? 1;

        if (!$userId || !$department) {
            throw new Exception('User ID and department are required');
        }

        $stmt = $this->conn->prepare("DELETE FROM user_roles WHERE user_id = ? AND department = ?");
        $stmt->bind_param("is", $userId, $department);
        $stmt->execute();

        // Log การลบ
        $this->logPermissionChange($userId, $department, null, 'delete_role', null, null, $deletedBy);

        echo json_encode([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * ดึง log การเปลี่ยนแปลงสิทธิ์
     */
    public function getPermissionLogs() {
        $userId = $_GET['userId'] ?? null;
        $department = $_GET['department'] ?? null;
        $limit = $_GET['limit'] ?? 50;

        $sql = "
            SELECT pl.*, u1.name as user_name, u2.name as changed_by_name
            FROM permission_logs pl
            LEFT JOIN users u1 ON pl.user_id = u1.id
            LEFT JOIN users u2 ON pl.changed_by = u2.id
            WHERE 1=1
        ";
        $params = [];
        $types = "";

        if ($userId) {
            $sql .= " AND pl.user_id = ?";
            $params[] = $userId;
            $types .= "i";
        }

        if ($department) {
            $sql .= " AND pl.department = ?";
            $params[] = $department;
            $types .= "s";
        }

        $sql .= " ORDER BY pl.changed_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์หรือไม่
     */
    private function hasPermission($userId, $department, $module, $permission) {
        // ตรวจสอบสิทธิ์เฉพาะก่อน
        $stmt = $this->conn->prepare("
            SELECT permissions 
            FROM user_permissions 
            WHERE user_id = ? AND department = ? AND module = ?
        ");
        $stmt->bind_param("iss", $userId, $department, $module);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $permissions = json_decode($row['permissions'], true);
            if (isset($permissions[$permission]) && $permissions[$permission]) {
                return true;
            }
        }

        // ตรวจสอบสิทธิ์ตามบทบาท
        $stmt = $this->conn->prepare("
            SELECT ur.role, rd.permissions 
            FROM user_roles ur
            JOIN role_definitions rd ON ur.role = rd.role
            WHERE ur.user_id = ? AND ur.department = ?
        ");
        $stmt->bind_param("is", $userId, $department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $rolePermissions = json_decode($row['permissions'], true);
            return isset($rolePermissions[$permission]) && $rolePermissions[$permission];
        }

        return false;
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีบทบาทในระดับที่กำหนดหรือไม่
     */
    private function hasMinimumRole($userId, $department, $minLevel) {
        $stmt = $this->conn->prepare("
            SELECT rd.level 
            FROM user_roles ur
            JOIN role_definitions rd ON ur.role = rd.role
            WHERE ur.user_id = ? AND ur.department = ?
        ");
        $stmt->bind_param("is", $userId, $department);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['level'] >= $minLevel;
        }

        return false;
    }

    /**
     * บันทึก log การเปลี่ยนแปลงสิทธิ์
     */
    private function logPermissionChange($userId, $department, $module, $action, $oldValue, $newValue, $changedBy) {
        $stmt = $this->conn->prepare("
            INSERT INTO permission_logs (user_id, department, module, action, old_value, new_value, changed_by, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $oldValueJson = $oldValue ? json_encode($oldValue) : null;
        $newValueJson = $newValue ? json_encode($newValue) : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt->bind_param("isssssiss", $userId, $department, $module, $action, 
            $oldValueJson, $newValueJson, $changedBy, $ipAddress, $userAgent);
        $stmt->execute();
    }

    /**
     * Utility function สำหรับตรวจสอบสิทธิ์ (static method)
     */
    public static function checkUserPermission($userId, $department, $module, $permission) {
        $controller = new self();
        return $controller->hasPermission($userId, $department, $module, $permission);
    }

    /**
     * Utility function สำหรับตรวจสอบบทบาท (static method)
     */
    public static function checkUserRole($userId, $department, $minLevel) {
        $controller = new self();
        return $controller->hasMinimumRole($userId, $department, $minLevel);
    }
}

// Handle request if called directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new RoleController();
    $controller->handleRequest();
}
?>
