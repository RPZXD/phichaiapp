<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/Database.php';
require_once '../models/DepartmentPermission.php';
require_once '../models/User.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $departmentPermission = new DepartmentPermission($db);
    $user = new User($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            handleGetRequest($departmentPermission, $user);
            break;
        case 'POST':
            handlePostRequest($departmentPermission, $user, $input);
            break;
        case 'PUT':
            handlePutRequest($departmentPermission, $user, $input);
            break;
        case 'DELETE':
            handleDeleteRequest($departmentPermission, $input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

function handleGetRequest($departmentPermission, $user) {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'departments':
            getDepartments($departmentPermission);
            break;
        case 'roles':
            getRoles($departmentPermission, $_GET['department_id'] ?? null);
            break;
        case 'permissions':
            getPermissions($departmentPermission);
            break;
        case 'user_permissions':
            getUserPermissions($departmentPermission, $_GET['user_id'] ?? null);
            break;
        case 'department_permissions':
            getDepartmentPermissions($departmentPermission, $_GET['department_id'] ?? null);
            break;
        case 'users':
            getUsers($user, $_GET['department_id'] ?? null);
            break;
        case 'permission_matrix':
            getPermissionMatrix($departmentPermission, $_GET['department_id'] ?? null);
            break;
        case 'user_accessible_menus':
            getUserAccessibleMenus($departmentPermission, $_GET['user_id'] ?? null);
            break;
        case 'audit_logs':
            getAuditLogs($departmentPermission, $_GET);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function handlePostRequest($departmentPermission, $user, $input) {
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'assign_user_department':
            assignUserDepartment($departmentPermission, $input);
            break;
        case 'create_department_template':
            createDepartmentTemplate($departmentPermission, $input);
            break;
        case 'update_user_permissions':
            updateUserPermissions($departmentPermission, $input);
            break;
        case 'bulk_assign_permissions':
            bulkAssignPermissions($departmentPermission, $input);
            break;
        case 'check_permission':
            checkPermission($departmentPermission, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function handlePutRequest($departmentPermission, $user, $input) {
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'update_department_permission':
            updateDepartmentPermission($departmentPermission, $input);
            break;
        case 'update_user_role':
            updateUserRole($departmentPermission, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function handleDeleteRequest($departmentPermission, $input) {
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'remove_user_department':
            removeUserDepartment($departmentPermission, $input);
            break;
        case 'remove_user_permission':
            removeUserPermission($departmentPermission, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

// GET Functions
function getDepartments($departmentPermission) {
    try {
        $departments = [
            ['id' => 1, 'name' => 'งานวิชาการ', 'name_en' => 'Academic', 'color' => 'blue'],
            ['id' => 2, 'name' => 'งานงบประมาณ', 'name_en' => 'Budget', 'color' => 'green'],
            ['id' => 3, 'name' => 'งานบุคคล', 'name_en' => 'Personnel', 'color' => 'purple'],
            ['id' => 4, 'name' => 'งานทั่วไป', 'name_en' => 'General', 'color' => 'orange']
        ];
        echo json_encode(['success' => true, 'data' => $departments]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getRoles($departmentPermission, $departmentId) {
    try {
        $roles = [
            ['id' => 1, 'name' => 'เจ้าหน้าที่', 'level' => 1],
            ['id' => 2, 'name' => 'หัวหน้าฝ่าย', 'level' => 2],
            ['id' => 3, 'name' => 'รองผู้อำนวยการ', 'level' => 3]
        ];
        echo json_encode(['success' => true, 'data' => $roles]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getPermissions($departmentPermission) {
    try {
        $permissions = [
            ['id' => 1, 'module' => 'student_management', 'name' => 'จัดการข้อมูลนักศึกษา'],
            ['id' => 2, 'module' => 'teacher_management', 'name' => 'จัดการข้อมูลครู'],
            ['id' => 3, 'module' => 'course_management', 'name' => 'จัดการหลักสูตร'],
            ['id' => 4, 'module' => 'enrollment_management', 'name' => 'จัดการการลงทะเบียน'],
            ['id' => 5, 'module' => 'grade_management', 'name' => 'จัดการเกรด'],
            ['id' => 6, 'module' => 'budget_management', 'name' => 'จัดการงบประมาณ'],
            ['id' => 7, 'module' => 'user_management', 'name' => 'จัดการผู้ใช้'],
            ['id' => 8, 'module' => 'report_management', 'name' => 'จัดการรายงาน'],
            ['id' => 9, 'module' => 'system_settings', 'name' => 'ตั้งค่าระบบ']
        ];
        echo json_encode(['success' => true, 'data' => $permissions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getUserPermissions($departmentPermission, $userId) {
    try {
        if (!$userId) {
            throw new Exception('User ID is required');
        }
        
        $permissions = $departmentPermission->getUserPermissions($userId);
        echo json_encode(['success' => true, 'data' => $permissions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getDepartmentPermissions($departmentPermission, $departmentId) {
    try {
        if (!$departmentId) {
            throw new Exception('Department ID is required');
        }
        
        $permissions = $departmentPermission->getDepartmentPermissions($departmentId);
        echo json_encode(['success' => true, 'data' => $permissions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getUsers($user, $departmentId = null) {
    try {
        $query = "SELECT u.*, 
                         ud.department_id, 
                         ud.role_id,
                         CASE ud.department_id
                             WHEN 1 THEN 'งานวิชาการ'
                             WHEN 2 THEN 'งานงบประมาณ'
                             WHEN 3 THEN 'งานบุคคล'
                             WHEN 4 THEN 'งานทั่วไป'
                             ELSE 'ไม่ได้กำหนด'
                         END as department_name,
                         CASE ud.role_id
                             WHEN 1 THEN 'เจ้าหน้าที่'
                             WHEN 2 THEN 'หัวหน้าฝ่าย'
                             WHEN 3 THEN 'รองผู้อำนวยการ'
                             ELSE 'ไม่ได้กำหนด'
                         END as role_name
                  FROM users u 
                  LEFT JOIN user_departments ud ON u.user_id = ud.user_id";
        
        if ($departmentId) {
            $query .= " WHERE ud.department_id = :department_id";
        }
        
        $query .= " ORDER BY u.user_id";
        
        $stmt = $user->db->prepare($query);
        if ($departmentId) {
            $stmt->bindParam(':department_id', $departmentId);
        }
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $users]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getPermissionMatrix($departmentPermission, $departmentId) {
    try {
        if (!$departmentId) {
            throw new Exception('Department ID is required');
        }
        
        $permissions = $departmentPermission->getDepartmentPermissions($departmentId);
        echo json_encode(['success' => true, 'data' => $permissions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getUserAccessibleMenus($departmentPermission, $userId) {
    try {
        if (!$userId) {
            throw new Exception('User ID is required');
        }
        
        $menus = $departmentPermission->getUserAccessibleMenus($userId);
        echo json_encode(['success' => true, 'data' => $menus]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getAuditLogs($departmentPermission, $params) {
    try {
        $userId = $params['user_id'] ?? null;
        $action = $params['log_action'] ?? null;
        $startDate = $params['start_date'] ?? null;
        $endDate = $params['end_date'] ?? null;
        $limit = $params['limit'] ?? 50;
        $offset = $params['offset'] ?? 0;
        
        $query = "SELECT al.*, u.username, u.first_name, u.last_name
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.user_id
                  WHERE 1=1";
        
        $conditions = [];
        $params_array = [];
        
        if ($userId) {
            $conditions[] = "al.user_id = :user_id";
            $params_array[':user_id'] = $userId;
        }
        
        if ($action) {
            $conditions[] = "al.action = :action";
            $params_array[':action'] = $action;
        }
        
        if ($startDate) {
            $conditions[] = "al.created_at >= :start_date";
            $params_array[':start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $conditions[] = "al.created_at <= :end_date";
            $params_array[':end_date'] = $endDate . ' 23:59:59';
        }
        
        if (!empty($conditions)) {
            $query .= " AND " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $departmentPermission->db->prepare($query);
        
        foreach ($params_array as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $logs]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// POST Functions
function assignUserDepartment($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $departmentId = $input['department_id'] ?? null;
        $roleId = $input['role_id'] ?? null;
        $assignedBy = $input['assigned_by'] ?? null;
        
        if (!$userId || !$departmentId || !$roleId) {
            throw new Exception('User ID, Department ID, and Role ID are required');
        }
        
        $result = $departmentPermission->assignUserToDepartment($userId, $departmentId, $roleId, $assignedBy);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User assigned to department successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to assign user to department']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function createDepartmentTemplate($departmentPermission, $input) {
    try {
        $departmentId = $input['department_id'] ?? null;
        $permissions = $input['permissions'] ?? [];
        $createdBy = $input['created_by'] ?? null;
        
        if (!$departmentId || empty($permissions)) {
            throw new Exception('Department ID and permissions are required');
        }
        
        $result = $departmentPermission->createDepartmentPermissionTemplate($departmentId, $permissions, $createdBy);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Department template created successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create department template']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateUserPermissions($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $permissions = $input['permissions'] ?? [];
        $updatedBy = $input['updated_by'] ?? null;
        
        if (!$userId || !is_array($permissions)) {
            throw new Exception('User ID and permissions array are required');
        }
        
        $result = $departmentPermission->updateUserCustomPermissions($userId, $permissions, $updatedBy);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User permissions updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user permissions']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function bulkAssignPermissions($departmentPermission, $input) {
    try {
        $userIds = $input['user_ids'] ?? [];
        $permissions = $input['permissions'] ?? [];
        $assignedBy = $input['assigned_by'] ?? null;
        
        if (empty($userIds) || empty($permissions)) {
            throw new Exception('User IDs and permissions are required');
        }
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($userIds as $userId) {
            $result = $departmentPermission->updateUserCustomPermissions($userId, $permissions, $assignedBy);
            if ($result) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Bulk assignment completed. Success: $successCount, Failed: $failCount",
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function checkPermission($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $module = $input['module'] ?? null;
        $action = $input['action'] ?? null;
        
        if (!$userId || !$module || !$action) {
            throw new Exception('User ID, module, and action are required');
        }
        
        $hasPermission = $departmentPermission->hasPermission($userId, $module, $action);
        
        echo json_encode([
            'success' => true, 
            'has_permission' => $hasPermission,
            'user_id' => $userId,
            'module' => $module,
            'action' => $action
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// PUT Functions
function updateDepartmentPermission($departmentPermission, $input) {
    try {
        $departmentId = $input['department_id'] ?? null;
        $roleId = $input['role_id'] ?? null;
        $permissionId = $input['permission_id'] ?? null;
        $actions = $input['actions'] ?? [];
        $updatedBy = $input['updated_by'] ?? null;
        
        if (!$departmentId || !$roleId || !$permissionId || !is_array($actions)) {
            throw new Exception('Department ID, Role ID, Permission ID, and actions are required');
        }
        
        $result = $departmentPermission->updateDepartmentPermission($departmentId, $roleId, $permissionId, $actions, $updatedBy);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Department permission updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update department permission']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function updateUserRole($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $departmentId = $input['department_id'] ?? null;
        $roleId = $input['role_id'] ?? null;
        $updatedBy = $input['updated_by'] ?? null;
        
        if (!$userId || !$departmentId || !$roleId) {
            throw new Exception('User ID, Department ID, and Role ID are required');
        }
        
        $query = "UPDATE user_departments 
                  SET role_id = :role_id, updated_by = :updated_by, updated_at = NOW() 
                  WHERE user_id = :user_id AND department_id = :department_id";
        
        $stmt = $departmentPermission->db->prepare($query);
        $stmt->bindParam(':role_id', $roleId);
        $stmt->bindParam(':updated_by', $updatedBy);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':department_id', $departmentId);
        
        $result = $stmt->execute();
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User role updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update user role']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// DELETE Functions
function removeUserDepartment($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $departmentId = $input['department_id'] ?? null;
        $removedBy = $input['removed_by'] ?? null;
        
        if (!$userId || !$departmentId) {
            throw new Exception('User ID and Department ID are required');
        }
        
        // Log the removal before deleting
        $logQuery = "INSERT INTO audit_logs (user_id, action, details, created_by, created_at) 
                     VALUES (:user_id, 'remove_department', :details, :created_by, NOW())";
        
        $logStmt = $departmentPermission->db->prepare($logQuery);
        $logDetails = json_encode([
            'removed_user_id' => $userId,
            'department_id' => $departmentId,
            'action' => 'removed from department'
        ]);
        
        $logStmt->bindParam(':user_id', $removedBy);
        $logStmt->bindParam(':details', $logDetails);
        $logStmt->bindParam(':created_by', $removedBy);
        $logStmt->execute();
        
        // Remove user from department
        $query = "DELETE FROM user_departments 
                  WHERE user_id = :user_id AND department_id = :department_id";
        
        $stmt = $departmentPermission->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':department_id', $departmentId);
        
        $result = $stmt->execute();
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'User removed from department successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove user from department']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function removeUserPermission($departmentPermission, $input) {
    try {
        $userId = $input['user_id'] ?? null;
        $permissionId = $input['permission_id'] ?? null;
        $action = $input['action'] ?? null;
        $removedBy = $input['removed_by'] ?? null;
        
        if (!$userId || !$permissionId || !$action) {
            throw new Exception('User ID, Permission ID, and action are required');
        }
        
        $query = "DELETE FROM user_custom_permissions 
                  WHERE user_id = :user_id AND permission_id = :permission_id AND action = :action";
        
        $stmt = $departmentPermission->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':permission_id', $permissionId);
        $stmt->bindParam(':action', $action);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Log the removal
            $logQuery = "INSERT INTO audit_logs (user_id, action, details, created_by, created_at) 
                         VALUES (:user_id, 'remove_permission', :details, :created_by, NOW())";
            
            $logStmt = $departmentPermission->db->prepare($logQuery);
            $logDetails = json_encode([
                'target_user_id' => $userId,
                'permission_id' => $permissionId,
                'action' => $action,
                'operation' => 'removed permission'
            ]);
            
            $logStmt->bindParam(':user_id', $removedBy);
            $logStmt->bindParam(':details', $logDetails);
            $logStmt->bindParam(':created_by', $removedBy);
            $logStmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'User permission removed successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove user permission']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
