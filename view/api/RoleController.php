<?php
require_once('../../config/Database.php');

use App\Database;

/**
 * Role Controller for Managing Department-based User Permissions
 * Author: ICT Team @Phichai School
 * Date: May 27, 2025
 */
class RoleController {
    private $db;
    private $conn;

    // กำหนดฝ่ายงานและโมดูล
    private $departments = [
        'academic' => [
            'name' => 'งานวิชาการ',
            'modules' => ['curriculum', 'schedule', 'assessment', 'academic_report']
        ],
        'budget' => [
            'name' => 'งานงบประมาณ',
            'modules' => ['budget_plan', 'expenses', 'procurement', 'finance_report']
        ],
        'personnel' => [
            'name' => 'งานบุคคล',
            'modules' => ['staff_management', 'attendance', 'leave_management', 'hr_report']
        ],
        'general' => [
            'name' => 'งานทั่วไป',
            'modules' => ['facility', 'maintenance', 'inventory', 'general_report']
        ]
    ];

    // กำหนดบทบาทและระดับสิทธิ์
    private $roles = [
        'admin' => ['name' => 'Admin', 'level' => 5],
        'head' => ['name' => 'หัวหน้าฝ่าย', 'level' => 4],
        'officer' => ['name' => 'เจ้าหน้าที่', 'level' => 3],
        'teacher' => ['name' => 'ครู', 'level' => 2],
        'viewer' => ['name' => 'ผู้ชม', 'level' => 1]
    ];

    // กำหนดสิทธิ์ตามบทบาท
    private $rolePermissions = [
        'admin' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true],
        'head' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
        'officer' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
        'teacher' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
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
                    $this->checkPermission();
                    break;
                case 'checkRole':
                    $this->checkRole();
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
                case 'bulkAssignRoles':
                    $this->bulkAssignRoles($input);
                    break;
                default:
                    $this->sendResponse(false, 'Invalid action', null, 400);
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * สร้างตารางฐานข้อมูลถ้าไม่มี
     */
    private function createTablesIfNotExists() {
        try {
            // ตาราง user_roles - เก็บบทบาทของผู้ใช้ในแต่ละฝ่าย
            $userRolesTable = "
                CREATE TABLE IF NOT EXISTS user_roles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    department VARCHAR(50) NOT NULL,
                    role VARCHAR(50) NOT NULL,
                    assigned_by INT,
                    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user_dept (user_id, department),
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                    FOREIGN KEY (assigned_by) REFERENCES users(user_id)
                )
            ";

            // ตาราง user_permissions - เก็บสิทธิ์เฉพาะของผู้ใช้
            $userPermissionsTable = "
                CREATE TABLE IF NOT EXISTS user_permissions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    department VARCHAR(50) NOT NULL,
                    module VARCHAR(50) NOT NULL,
                    permission VARCHAR(20) NOT NULL,
                    granted BOOLEAN DEFAULT TRUE,
                    granted_by INT,
                    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_permission (user_id, department, module, permission),
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                    FOREIGN KEY (granted_by) REFERENCES users(user_id)
                )
            ";

            // ตาราง permission_logs - บันทึกการเปลี่ยนแปลงสิทธิ์
            $permissionLogsTable = "
                CREATE TABLE IF NOT EXISTS permission_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    department VARCHAR(50),
                    module VARCHAR(50),
                    permission VARCHAR(20),
                    action VARCHAR(50) NOT NULL,
                    old_value JSON,
                    new_value JSON,
                    changed_by INT,
                    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
                    FOREIGN KEY (changed_by) REFERENCES users(user_id)
                )
            ";

            $this->conn->exec($userRolesTable);
            $this->conn->exec($userPermissionsTable);
            $this->conn->exec($permissionLogsTable);

        } catch (Exception $e) {
            error_log("Error creating role management tables: " . $e->getMessage());
        }
    }

    /**
     * ดึงสิทธิ์ของผู้ใช้
     */
    private function getUserPermissions() {
        try {
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                $this->sendResponse(false, 'กรุณาระบุ user_id');
                return;
            }

            // ดึงบทบาทของผู้ใช้ในแต่ละฝ่าย
            $rolesQuery = "SELECT department, role FROM user_roles WHERE user_id = :user_id";
            $rolesStmt = $this->conn->prepare($rolesQuery);
            $rolesStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $rolesStmt->execute();
            $userRoles = $rolesStmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // ดึงสิทธิ์เฉพาะของผู้ใช้
            $permsQuery = "SELECT department, module, permission, granted FROM user_permissions WHERE user_id = :user_id";
            $permsStmt = $this->conn->prepare($permsQuery);
            $permsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $permsStmt->execute();
            $userPermissions = $permsStmt->fetchAll(PDO::FETCH_ASSOC);

            // จัดรูปแบบสิทธิ์
            $permissions = [];
            foreach ($userPermissions as $perm) {
                $permissions[$perm['department']][$perm['module']][$perm['permission']] = (bool)$perm['granted'];
            }

            $response = [
                'roles' => $userRoles,
                'permissions' => $permissions
            ];

            $this->sendResponse(true, 'ดึงข้อมูลสิทธิ์สำเร็จ', $response);

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลสิทธิ์: ' . $e->getMessage());
        }
    }

    /**
     * กำหนดบทบาทของผู้ใช้ในฝ่าย
     */
    private function setUserRole($input) {
        try {
            $userId = $input['user_id'] ?? null;
            $department = $input['department'] ?? null;
            $role = $input['role'] ?? null;
            $assignedBy = $_SESSION['user']['user_id'] ?? null;

            if (!$userId || !$department) {
                $this->sendResponse(false, 'กรุณาระบุ user_id และ department');
                return;
            }

            // ตรวจสอบว่าฝ่ายและบทบาทถูกต้อง
            if (!isset($this->departments[$department])) {
                $this->sendResponse(false, 'ฝ่ายงานไม่ถูกต้อง');
                return;
            }

            if ($role && !isset($this->roles[$role])) {
                $this->sendResponse(false, 'บทบาทไม่ถูกต้อง');
                return;
            }

            // บันทึกการเปลี่ยนแปลง
            $this->logPermissionChange($userId, $department, null, null, 'role_change', 
                ['old_role' => $this->getUserRole($userId, $department)], 
                ['new_role' => $role], $assignedBy);

            if ($role) {
                // เพิ่มหรืออัพเดทบทบาท
                $query = "REPLACE INTO user_roles (user_id, department, role, assigned_by) VALUES (:user_id, :department, :role, :assigned_by)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':department', $department);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
            } else {
                // ลบบทบาท
                $query = "DELETE FROM user_roles WHERE user_id = :user_id AND department = :department";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':department', $department);
            }

            $stmt->execute();
            $this->sendResponse(true, 'กำหนดบทบาทสำเร็จ');

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการกำหนดบทบาท: ' . $e->getMessage());
        }
    }

    /**
     * บันทึกสิทธิ์ของผู้ใช้
     */
    private function saveUserPermissions($input) {
        try {
            $userId = $input['user_id'] ?? null;
            $permissions = $input['permissions'] ?? [];
            $grantedBy = $_SESSION['user']['user_id'] ?? null;

            if (!$userId) {
                $this->sendResponse(false, 'กรุณาระบุ user_id');
                return;
            }

            $this->conn->beginTransaction();

            // ลบสิทธิ์เดิมทั้งหมด
            $deleteQuery = "DELETE FROM user_permissions WHERE user_id = :user_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // เพิ่มสิทธิ์ใหม่
            if (isset($permissions['permissions'])) {
                $insertQuery = "INSERT INTO user_permissions (user_id, department, module, permission, granted, granted_by) VALUES (:user_id, :department, :module, :permission, :granted, :granted_by)";
                $insertStmt = $this->conn->prepare($insertQuery);

                foreach ($permissions['permissions'] as $department => $modules) {
                    foreach ($modules as $module => $perms) {
                        foreach ($perms as $permission => $granted) {
                            $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                            $insertStmt->bindParam(':department', $department);
                            $insertStmt->bindParam(':module', $module);
                            $insertStmt->bindParam(':permission', $permission);
                            $insertStmt->bindParam(':granted', $granted, PDO::PARAM_BOOL);
                            $insertStmt->bindParam(':granted_by', $grantedBy, PDO::PARAM_INT);
                            $insertStmt->execute();
                        }
                    }
                }
            }

            // บันทึกการเปลี่ยนแปลง
            $this->logPermissionChange($userId, null, null, null, 'permissions_update', 
                null, $permissions, $grantedBy);

            $this->conn->commit();
            $this->sendResponse(true, 'บันทึกสิทธิ์สำเร็จ');

        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการบันทึกสิทธิ์: ' . $e->getMessage());
        }
    }

    /**
     * ตรวจสอบสิทธิ์ของผู้ใช้
     */
    private function checkPermission() {
        try {
            $userId = $_GET['user_id'] ?? null;
            $department = $_GET['department'] ?? null;
            $module = $_GET['module'] ?? null;
            $action = $_GET['action'] ?? null;

            if (!$userId || !$department || !$module || !$action) {
                $this->sendResponse(false, 'กรุณาระบุพารามิเตอร์ที่จำเป็น');
                return;
            }

            $hasPermission = $this->userHasPermission($userId, $department, $module, $action);
            
            $this->sendResponse(true, 'ตรวจสอบสิทธิ์สำเร็จ', ['hasPermission' => $hasPermission]);

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์: ' . $e->getMessage());
        }
    }

    /**
     * ตรวจสอบบทบาทของผู้ใช้
     */
    private function checkRole() {
        try {
            $userId = $_GET['user_id'] ?? null;
            $department = $_GET['department'] ?? null;
            $minRole = $_GET['min_role'] ?? 'viewer';

            if (!$userId || !$department) {
                $this->sendResponse(false, 'กรุณาระบุ user_id และ department');
                return;
            }

            $hasRole = $this->userHasRole($userId, $department, $minRole);
            
            $this->sendResponse(true, 'ตรวจสอบบทบาทสำเร็จ', ['hasRole' => $hasRole]);

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการตรวจสอบบทบาท: ' . $e->getMessage());
        }
    }

    /**
     * ดึงข้อมูลฝ่ายงาน
     */
    private function getDepartments() {
        $this->sendResponse(true, 'ดึงข้อมูลฝ่ายงานสำเร็จ', $this->departments);
    }

    /**
     * ดึงข้อมูลบทบาท
     */
    private function getRoles() {
        $this->sendResponse(true, 'ดึงข้อมูลบทบาทสำเร็จ', $this->roles);
    }

    /**
     * ดึงบทบาทของผู้ใช้ทั้งหมด
     */
    private function getUserRoles() {
        try {
            $userId = $_GET['user_id'] ?? null;
            
            if ($userId) {
                // ดึงบทบาทของผู้ใช้คนเดียว
                $query = "SELECT ur.*, u.username, d.name as department_name 
                         FROM user_roles ur 
                         JOIN users u ON ur.user_id = u.user_id 
                         WHERE ur.user_id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            } else {
                // ดึงบทบาทของผู้ใช้ทั้งหมด
                $query = "SELECT ur.*, u.username 
                         FROM user_roles ur 
                         JOIN users u ON ur.user_id = u.user_id 
                         ORDER BY u.username, ur.department";
                $stmt = $this->conn->prepare($query);
            }
            
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendResponse(true, 'ดึงข้อมูลบทบาทผู้ใช้สำเร็จ', $roles);

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลบทบาทผู้ใช้: ' . $e->getMessage());
        }
    }

    /**
     * กำหนดบทบาทหลายคนพร้อมกัน
     */
    private function bulkAssignRoles($input) {
        try {
            $assignments = $input['assignments'] ?? [];
            $assignedBy = $_SESSION['user']['user_id'] ?? null;

            if (empty($assignments)) {
                $this->sendResponse(false, 'ไม่มีข้อมูลการกำหนดบทบาท');
                return;
            }

            $this->conn->beginTransaction();

            $query = "REPLACE INTO user_roles (user_id, department, role, assigned_by) VALUES (:user_id, :department, :role, :assigned_by)";
            $stmt = $this->conn->prepare($query);

            foreach ($assignments as $assignment) {
                $userId = $assignment['user_id'] ?? null;
                $department = $assignment['department'] ?? null;
                $role = $assignment['role'] ?? null;

                if ($userId && $department && $role) {
                    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $stmt->bindParam(':department', $department);
                    $stmt->bindParam(':role', $role);
                    $stmt->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
                    $stmt->execute();

                    // บันทึกการเปลี่ยนแปลง
                    $this->logPermissionChange($userId, $department, null, null, 'bulk_role_assign', 
                        null, ['role' => $role], $assignedBy);
                }
            }

            $this->conn->commit();
            $this->sendResponse(true, 'กำหนดบทบาทหลายคนสำเร็จ');

        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการกำหนดบทบาทหลายคน: ' . $e->getMessage());
        }
    }

    // ===== Helper Functions =====

    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์หรือไม่
     */
    private function userHasPermission($userId, $department, $module, $action) {
        // ตรวจสอบสิทธิ์เฉพาะก่อน
        $explicitQuery = "SELECT granted FROM user_permissions WHERE user_id = :user_id AND department = :department AND module = :module AND permission = :action";
        $explicitStmt = $this->conn->prepare($explicitQuery);
        $explicitStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $explicitStmt->bindParam(':department', $department);
        $explicitStmt->bindParam(':module', $module);
        $explicitStmt->bindParam(':action', $action);
        $explicitStmt->execute();
        
        $explicitResult = $explicitStmt->fetch(PDO::FETCH_ASSOC);
        if ($explicitResult !== false) {
            return (bool)$explicitResult['granted'];
        }

        // ตรวจสอบตามบทบาท
        $userRole = $this->getUserRole($userId, $department);
        if (!$userRole) {
            return false;
        }

        return $this->rolePermissions[$userRole][$action] ?? false;
    }

    /**
     * ตรวจสอบว่าผู้ใช้มีบทบาทในระดับที่กำหนดหรือไม่
     */
    private function userHasRole($userId, $department, $minRole) {
        $userRole = $this->getUserRole($userId, $department);
        if (!$userRole) {
            return false;
        }

        $userLevel = $this->roles[$userRole]['level'] ?? 0;
        $minLevel = $this->roles[$minRole]['level'] ?? 0;

        return $userLevel >= $minLevel;
    }

    /**
     * ดึงบทบาทของผู้ใช้ในฝ่าย
     */
    private function getUserRole($userId, $department) {
        $query = "SELECT role FROM user_roles WHERE user_id = :user_id AND department = :department";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':department', $department);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['role'] : null;
    }

    /**
     * บันทึกการเปลี่ยนแปลงสิทธิ์
     */
    private function logPermissionChange($userId, $department, $module, $permission, $action, $oldValue, $newValue, $changedBy) {
        try {
            $query = "INSERT INTO permission_logs (user_id, department, module, permission, action, old_value, new_value, changed_by) VALUES (:user_id, :department, :module, :permission, :action, :old_value, :new_value, :changed_by)";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':department', $department);
            $stmt->bindParam(':module', $module);
            $stmt->bindParam(':permission', $permission);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':old_value', json_encode($oldValue));
            $stmt->bindParam(':new_value', json_encode($newValue));
            $stmt->bindParam(':changed_by', $changedBy, PDO::PARAM_INT);
            
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error logging permission change: " . $e->getMessage());
        }
    }

    /**
     * ส่งผลลัพธ์ JSON
     */
    private function sendResponse($success, $message, $data = null, $httpCode = 200) {
        http_response_code($httpCode);
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
}

// Initialize and handle the request
$controller = new RoleController();
$controller->handleRequest();
?>
