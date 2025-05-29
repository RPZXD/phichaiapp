<?php
require_once('../../config/Database.php');
require_once('../../models/User.php');
require_once('../../models/Role.php');
require_once('../../models/Department.php');
// require_once('../../models/Authorization.php');
// require_once('../../models/PermissionManager.php');

use App\Database;

class UserController {
    private $db;
    private $conn;
    private $userModel;
    private $roleModel;
    private $departmentModel;
    // private $authModel;
    // private $permissionModel;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->userModel = new User($this->conn);
        $this->roleModel = new Role($this->conn);
        $this->departmentModel = new Department($this->conn);
        // $this->authModel = new Authorization($this->conn);
        // $this->permissionModel = new PermissionManager($this->conn);
    }

    public function handleRequest() {
        // Set headers for JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');

        // Get the action from GET or POST
        $action = $_GET['action'] ?? $_POST['action'] ?? '';        try {            switch ($action) {
                case 'getAll':
                    $this->getAllUsers();
                    break;
                case 'get':
                    $this->getUser();
                    break;
                case 'create':
                    $this->createUser();
                    break;
                case 'update':
                    $this->updateUser();
                    break;
                case 'delete':
                    $this->deleteUser();
                    break;
                case 'bulkDelete':
                    $this->bulkDeleteUsers();
                    break;
                case 'resetPassword':
                    $this->resetPassword();
                    break;
                case 'getStatistics':
                    $this->getStatistics();
                    break;
                case 'getRoleDistribution':
                    $this->getRoleDistribution();
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
                case 'updateUserRoles':
                    $this->updateUserRoles();
                    break;
                case 'getAssignableRoles':
                    $this->getAssignableRoles();
                    break;
                case 'activateUser':
                    $this->activateUser();
                    break;
                case 'deactivateUser':
                    $this->deactivateUser();
                    break;
                default:
                    $this->sendResponse(false, 'Invalid action', null, 400);
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาด: ' . $e->getMessage(), null, 500);
        }
    }    private function getAllUsers() {
        try {
            // Get pagination parameters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 25;
            $offset = ($page - 1) * $limit;
            
            // Get search parameter
            $search = $_GET['search'] ?? '';
            
            // Get role filter
            $role = $_GET['role'] ?? '';
            
            // Get department filter
            $department = $_GET['department'] ?? '';
            
            // Get status filter
            $status = $_GET['status'] ?? '';
            
            // Build WHERE clause for users view
            $whereConditions = [];
            $params = [];
            
            if (!empty($search)) {
                $whereConditions[] = "(u.username LIKE :search OR u.email LIKE :search OR u.phone_number LIKE :search OR u.employee_id LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if (!empty($role)) {
                $whereConditions[] = "EXISTS (SELECT 1 FROM user_roles ur JOIN roles r ON ur.role_id = r.role_id WHERE ur.user_id = u.user_id AND r.role_name = :role AND ur.is_active = 1)";
                $params[':role'] = $role;
            }
            
            if (!empty($department)) {
                $whereConditions[] = "d.department_name = :department";
                $params[':department'] = $department;
            }
            
            if (!empty($status)) {
                if ($status === 'active') {
                    $whereConditions[] = "u.is_active = 1";
                } else {
                    $whereConditions[] = "u.is_active = 0";
                }
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(DISTINCT u.user_id) as total 
                          FROM users u 
                          LEFT JOIN departments d ON u.department_id = d.department_id 
                          $whereClause";
            $countStmt = $this->conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
              // Get users with pagination and role information
            $query = "SELECT DISTINCT 
                        u.user_id, u.username, u.email, u.employee_id, u.phone_number, 
                        u.is_active, u.created_at, u.updated_at,
                        d.department_name,
                        GROUP_CONCAT(r.role_name ORDER BY r.role_level DESC SEPARATOR ', ') as roles,
                        COUNT(ur.role_id) as role_count
                     FROM users u 
                     LEFT JOIN departments d ON u.department_id = d.department_id
                     LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = 1
                     LEFT JOIN roles r ON ur.role_id = r.role_id
                     $whereClause
                     GROUP BY u.user_id, u.username, u.email, u.employee_id, u.phone_number, 
                              u.is_active, u.created_at, u.updated_at, d.department_name
                     ORDER BY u.user_id DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind search and filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the roles and add additional information
            foreach ($users as &$user) {
                $user['roles'] = $user['roles'] ? explode(', ', $user['roles']) : [];
                $user['is_active'] = (bool)$user['is_active'];
                $user['role_count'] = (int)$user['role_count'];
            }
            
            // Calculate pagination info
            $totalPages = ceil($totalRecords / $limit);
            
            $response = [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords,
                    'limit' => $limit,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ];
            
            $this->sendResponse(true, 'ดึงข้อมูลผู้ใช้สำเร็จ', $response);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: ' . $e->getMessage());
        }
    }    private function getUser() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }

            // Get user data using the enhanced User model
            $user = $this->userModel->getUserById($id);
            
            if ($user) {
                // Get user roles
                $userRoles = $this->userModel->getUserRoles($id);
                $user['roles'] = $userRoles;
                  // Get department information
                if ($user['department_id']) {
                    $deptQuery = "SELECT department_name, department_description FROM departments WHERE department_id = :dept_id";
                    $deptStmt = $this->conn->prepare($deptQuery);
                    $deptStmt->bindParam(':dept_id', $user['department_id']);
                    $deptStmt->execute();
                    $department = $deptStmt->fetch(PDO::FETCH_ASSOC);
                    $user['department'] = $department;
                }
                  // Get effective permissions for this user
                // $permissions = $this->authModel->getUserEffectivePermissions($id);
                // $user['permissions'] = $permissions;
                
                $this->sendResponse(true, 'ดึงข้อมูลผู้ใช้สำเร็จ', $user);
            } else {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: ' . $e->getMessage());
        }
    }    private function createUser() {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $roles = $_POST['roles'] ?? []; // Array of role IDs
            $departmentId = $_POST['department_id'] ?? null;
            $phone_number = $_POST['phone_number'] ?? null;
            $employeeId = $_POST['employee_id'] ?? null;

            // Validate required fields
            if (empty($username) || empty($email) || empty($password)) {
                $this->sendResponse(false, 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (ชื่อผู้ใช้, อีเมล, รหัสผ่าน)', null, 400);
                return;
            }

            // Validate roles
            if (empty($roles) || !is_array($roles)) {
                $this->sendResponse(false, 'กรุณาเลือกบทบาทอย่างน้อย 1 บทบาท', null, 400);
                return;
            }

            // Validate password length
            if (strlen($password) < 6) {
                $this->sendResponse(false, 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร', null, 400);
                return;
            }

            // Check if username already exists
            if ($this->userModel->usernameExists($username)) {
                $this->sendResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if email already exists
            if ($this->userModel->emailExists($email)) {
                $this->sendResponse(false, 'อีเมลนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if employee ID already exists (if provided)
            if ($employeeId && $this->userModel->employeeIdExists($employeeId)) {
                $this->sendResponse(false, 'รหัสพนักงานนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Validate department exists
            if ($departmentId) {
                $deptQuery = "SELECT department_id FROM departments WHERE department_id = :dept_id";
                $deptStmt = $this->conn->prepare($deptQuery);
                $deptStmt->bindParam(':dept_id', $departmentId);
                $deptStmt->execute();
                if ($deptStmt->rowCount() === 0) {
                    $this->sendResponse(false, 'แผนกที่เลือกไม่ถูกต้อง', null, 400);
                    return;
                }
            }

            // Validate roles exist and are assignable
            $validRoles = [];
            foreach ($roles as $roleId) {
                $role = $this->roleModel->getRoleById($roleId);
                if (!$role) {
                    $this->sendResponse(false, "บทบาท ID $roleId ไม่ถูกต้อง", null, 400);
                    return;
                }
                if (!$role['is_active']) {
                    $this->sendResponse(false, "บทบาท {$role['role_name']} ไม่ได้เปิดใช้งาน", null, 400);
                    return;
                }
                $validRoles[] = $role;
            }

            // Prepare user data
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'department_id' => $departmentId,
                'phone_number' => $phone_number,
                'employee_id' => $employeeId,
                'is_active' => true
            ];

            // Create user using the enhanced User model
            $userId = $this->userModel->createUser($userData);
            
            if ($userId) {
                // Assign roles to the user
                $roleAssignmentSuccess = true;
                $assignedRoles = [];
                
                foreach ($validRoles as $role) {
                    $result = $this->userModel->assignRole($userId, $role['role_id']);
                    if ($result) {
                        $assignedRoles[] = $role['role_name'];
                    } else {
                        $roleAssignmentSuccess = false;
                        break;
                    }
                }

                if ($roleAssignmentSuccess) {
                    $response = [
                        'user_id' => $userId,
                        'username' => $username,
                        'assigned_roles' => $assignedRoles
                    ];
                    $this->sendResponse(true, 'เพิ่มผู้ใช้และกำหนดบทบาทสำเร็จ', $response);
                } else {
                    // If role assignment failed, we should clean up the user
                    $this->userModel->deleteUser($userId);
                    $this->sendResponse(false, 'เกิดข้อผิดพลาดในการกำหนดบทบาท');
                }
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้: ' . $e->getMessage());
        }
    }    private function updateUser() {
        try {
            $id = $_POST['user_id'] ?? $_POST['id'] ?? null;
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $roles = $_POST['roles'] ?? []; // Array of role IDs
            $departmentId = $_POST['department_id'] ?? null;
            $phone_number = $_POST['phone_number'] ?? null;
            $employeeId = $_POST['employee_id'] ?? null;
            $isActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;

            if (!$id || empty($username) || empty($email)) {
                $this->sendResponse(false, 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', null, 400);
                return;
            }

            // Check if user exists
            $existingUser = $this->userModel->getUserById($id);
            if (!$existingUser) {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
                return;
            }

            // Check if username already exists (excluding current user)
            if ($username !== $existingUser['username'] && $this->userModel->usernameExists($username, $id)) {
                $this->sendResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if email already exists (excluding current user)
            if ($email !== $existingUser['email'] && $this->userModel->emailExists($email, $id)) {
                $this->sendResponse(false, 'อีเมลนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if employee ID already exists (excluding current user)
            if ($employeeId && $employeeId !== $existingUser['employee_id'] && $this->userModel->employeeIdExists($employeeId, $id)) {
                $this->sendResponse(false, 'รหัสพนักงานนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Validate department exists if provided
            if ($departmentId) {
                $deptQuery = "SELECT department_id FROM departments WHERE department_id = :dept_id";
                $deptStmt = $this->conn->prepare($deptQuery);
                $deptStmt->bindParam(':dept_id', $departmentId);
                $deptStmt->execute();
                if ($deptStmt->rowCount() === 0) {
                    $this->sendResponse(false, 'แผนกที่เลือกไม่ถูกต้อง', null, 400);
                    return;
                }
            }

            // Validate roles if provided
            $validRoles = [];
            if (!empty($roles) && is_array($roles)) {
                foreach ($roles as $roleId) {
                    $role = $this->roleModel->getRoleById($roleId);
                    if (!$role) {
                        $this->sendResponse(false, "บทบาท ID $roleId ไม่ถูกต้อง", null, 400);
                        return;
                    }
                    if (!$role['is_active']) {
                        $this->sendResponse(false, "บทบาท {$role['role_name']} ไม่ได้เปิดใช้งาน", null, 400);
                        return;
                    }
                    $validRoles[] = $role;
                }
            }

            // Prepare update data
            $updateData = [
                'username' => $username,
                'email' => $email,
                'department_id' => $departmentId,
                'phone_number' => $phone_number,
                'employee_id' => $employeeId,
                'is_active' => $isActive
            ];

            // Update user using the enhanced User model
            $updateResult = $this->userModel->updateUser($id, $updateData);
            
            if ($updateResult) {
                $response = ['user_id' => $id];
                
                // Update roles if provided
                if (!empty($validRoles)) {
                    // Get current roles
                    $currentRoles = $this->userModel->getUserRoles($id);
                    $currentRoleIds = array_column($currentRoles, 'role_id');
                    $newRoleIds = array_column($validRoles, 'role_id');
                    
                    // Remove roles that are no longer assigned
                    foreach ($currentRoleIds as $currentRoleId) {
                        if (!in_array($currentRoleId, $newRoleIds)) {
                            $this->userModel->removeRole($id, $currentRoleId);
                        }
                    }
                    
                    // Add new roles
                    $assignedRoles = [];
                    foreach ($validRoles as $role) {
                        if (!in_array($role['role_id'], $currentRoleIds)) {
                            $this->userModel->assignRole($id, $role['role_id']);
                        }
                        $assignedRoles[] = $role['role_name'];
                    }
                    
                    $response['assigned_roles'] = $assignedRoles;
                }
                
                $this->sendResponse(true, 'อัพเดทข้อมูลผู้ใช้สำเร็จ', $response);
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการอัพเดทข้อมูลผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการอัพเดทข้อมูลผู้ใช้: ' . $e->getMessage());
        }
    }    private function deleteUser() {
        try {
            $id = $_POST['user_id'] ?? $_POST['id'] ?? null;

            if (!$id) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }

            // Check if user exists
            $user = $this->userModel->getUserById($id);
            if (!$user) {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
                return;
            }

            // Use soft delete from the enhanced User model
            $deleteResult = $this->userModel->deleteUser($id);
            
            if ($deleteResult) {
                $this->sendResponse(true, 'ลบผู้ใช้สำเร็จ (ระบบจะเก็บข้อมูลไว้สำหรับการตรวจสอบ)');
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage());
        }
    }    private function bulkDeleteUsers() {
        try {
            $userIds = $_POST['user_ids'] ?? [];
            
            if (empty($userIds) || !is_array($userIds)) {
                $this->sendResponse(false, 'ไม่พบรายการผู้ใช้ที่ต้องการลบ', null, 400);
                return;
            }
            
            // Validate user IDs
            $userIds = array_filter(array_map('intval', $userIds));
            if (empty($userIds)) {
                $this->sendResponse(false, 'รายการผู้ใช้ไม่ถูกต้อง', null, 400);
                return;
            }
            
            $deletedCount = 0;
            $errors = [];
            
            // Use soft delete for each user
            foreach ($userIds as $userId) {
                try {
                    $result = $this->userModel->deleteUser($userId);
                    if ($result) {
                        $deletedCount++;
                    } else {
                        $errors[] = "ไม่สามารถลบผู้ใช้ ID $userId ได้";
                    }
                } catch (Exception $e) {
                    $errors[] = "ข้อผิดพลาดในการลบผู้ใช้ ID $userId: " . $e->getMessage();
                }
            }
            
            if ($deletedCount > 0) {
                $message = "ลบผู้ใช้สำเร็จ {$deletedCount} รายการ";
                if (!empty($errors)) {
                    $message .= " (มีข้อผิดพลาด " . count($errors) . " รายการ)";
                }
                $this->sendResponse(true, $message, [
                    'deleted_count' => $deletedCount,
                    'errors' => $errors
                ]);
            } else {
                $this->sendResponse(false, 'ไม่สามารถลบผู้ใช้ได้', ['errors' => $errors]);
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage());        }
    }    private function getDepartments() {
        try {
            $query = "SELECT department_id, department_code, department_name, department_description, is_active 
                     FROM departments 
                     WHERE is_active = 1 
                     ORDER BY department_name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->sendResponse(true, 'ดึงข้อมูลแผนกสำเร็จ', $departments);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลแผนก: ' . $e->getMessage());
        }
    }

    private function getRoles() {
        try {
            $includeInactive = $_GET['include_inactive'] ?? false;
            
            $roles = $this->roleModel->getAllRoles($includeInactive);
            
            $this->sendResponse(true, 'ดึงข้อมูลบทบาทสำเร็จ', $roles);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลบทบาท: ' . $e->getMessage());
        }
    }

    private function getUserRoles() {
        try {
            $userId = $_GET['user_id'] ?? null;
            
            if (!$userId) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }
            
            $userRoles = $this->userModel->getUserRoles($userId);
            
            $this->sendResponse(true, 'ดึงข้อมูลบทบาทของผู้ใช้สำเร็จ', $userRoles);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลบทบาทของผู้ใช้: ' . $e->getMessage());
        }
    }

    private function updateUserRoles() {
        try {
            $userId = $_POST['user_id'] ?? null;
            $roleIds = $_POST['role_ids'] ?? [];
            
            if (!$userId) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }
            
            if (!is_array($roleIds)) {
                $this->sendResponse(false, 'รูปแบบข้อมูลบทบาทไม่ถูกต้อง', null, 400);
                return;
            }
            
            // Check if user exists
            $user = $this->userModel->getUserById($userId);
            if (!$user) {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
                return;
            }
            
            // Validate all roles exist and are active
            $validRoles = [];
            foreach ($roleIds as $roleId) {
                $role = $this->roleModel->getRoleById($roleId);
                if (!$role || !$role['is_active']) {
                    $this->sendResponse(false, "บทบาท ID $roleId ไม่ถูกต้องหรือไม่ได้เปิดใช้งาน", null, 400);
                    return;
                }
                $validRoles[] = $role;
            }
            
            // Get current roles
            $currentRoles = $this->userModel->getUserRoles($userId);
            $currentRoleIds = array_column($currentRoles, 'role_id');
            
            // Remove roles that are no longer assigned
            foreach ($currentRoleIds as $currentRoleId) {
                if (!in_array($currentRoleId, $roleIds)) {
                    $this->userModel->removeRole($userId, $currentRoleId);
                }
            }
            
            // Add new roles
            $assignedRoles = [];
            foreach ($validRoles as $role) {
                if (!in_array($role['role_id'], $currentRoleIds)) {
                    $this->userModel->assignRole($userId, $role['role_id']);
                }
                $assignedRoles[] = $role['role_name'];
            }
            
            $response = [
                'user_id' => $userId,
                'assigned_roles' => $assignedRoles
            ];
            
            $this->sendResponse(true, 'อัพเดทบทบาทของผู้ใช้สำเร็จ', $response);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการอัพเดทบทบาทของผู้ใช้: ' . $e->getMessage());
        }
    }

    private function getAssignableRoles() {
        try {
            $currentUserId = $_SESSION['user']['user_id'] ?? null;
            
            if (!$currentUserId) {
                $this->sendResponse(false, 'ไม่สามารถระบุผู้ใช้ปัจจุบันได้', null, 401);
                return;
            }
            
            $assignableRoles = $this->roleModel->getAssignableRoles($currentUserId);
            
            $this->sendResponse(true, 'ดึงข้อมูลบทบาทที่สามารถกำหนดได้สำเร็จ', $assignableRoles);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลบทบาทที่สามารถกำหนดได้: ' . $e->getMessage());
        }
    }

    private function activateUser() {
        try {
            $userId = $_POST['user_id'] ?? null;
            
            if (!$userId) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }
            
            $result = $this->userModel->activateUser($userId);
            
            if ($result) {
                $this->sendResponse(true, 'เปิดใช้งานผู้ใช้สำเร็จ');
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการเปิดใช้งานผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการเปิดใช้งานผู้ใช้: ' . $e->getMessage());
        }
    }

    private function deactivateUser() {
        try {
            $userId = $_POST['user_id'] ?? null;
            
            if (!$userId) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }
            
            $result = $this->userModel->deactivateUser($userId);
            
            if ($result) {
                $this->sendResponse(true, 'ปิดใช้งานผู้ใช้สำเร็จ');
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการปิดใช้งานผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการปิดใช้งานผู้ใช้: ' . $e->getMessage());
        }
    }    private function getStatistics() {
        try {
            // Get statistics using the enhanced User model
            $statistics = $this->userModel->getUserStatistics();
            
            // Additional department statistics
            $deptQuery = "SELECT COUNT(DISTINCT department_id) as total_departments FROM departments WHERE is_active = 1";
            $deptStmt = $this->conn->prepare($deptQuery);
            $deptStmt->execute();
            $statistics['total_departments'] = (int)$deptStmt->fetch(PDO::FETCH_ASSOC)['total_departments'];
            
            // Role statistics
            $roleQuery = "SELECT COUNT(*) as total_roles FROM roles WHERE is_active = 1";
            $roleStmt = $this->conn->prepare($roleQuery);
            $roleStmt->execute();
            $statistics['total_roles'] = (int)$roleStmt->fetch(PDO::FETCH_ASSOC)['total_roles'];

            $this->sendResponse(true, 'ดึงข้อมูลสถิติสำเร็จ', $statistics);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ: ' . $e->getMessage());
        }
    }    private function getRoleDistribution() {
        try {
            // Get role distribution from the new system
            $query = "SELECT 
                        r.role_name,
                        r.display_name,
                        COUNT(DISTINCT ur.user_id) as user_count
                     FROM roles r
                     LEFT JOIN user_roles ur ON r.role_id = ur.role_id AND ur.is_active = 1
                     LEFT JOIN users u ON ur.user_id = u.user_id AND u.is_active = 1
                     WHERE r.is_active = 1
                     GROUP BY r.role_id, r.role_name, r.display_name
                     ORDER BY user_count DESC, r.hierarchy_level DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format the data
            foreach ($distribution as &$item) {
                $item['user_count'] = (int)$item['user_count'];
                $item['role'] = $item['display_name'] ?: $item['role_name'];
            }

            $this->sendResponse(true, 'ดึงข้อมูลการกระจายบทบาทสำเร็จ', $distribution);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลการกระจายบทบาท: ' . $e->getMessage());
        }
    }

    private function resetPassword() {
        try {
            $userId = $_POST['user_id'] ?? '';
            $resetType = $_POST['reset_type'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';

            if (empty($userId) || empty($resetType)) {
                $this->sendResponse(false, 'ข้อมูลไม่ครบถ้วน', null, 400);
                return;
            }

            // Validate user exists
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->sendResponse(false, 'ไม่พบผู้ใช้ที่ระบุ', null, 404);
                return;
            }

            $updateFields = [];
            $updateValues = [];
            $message = '';

            switch ($resetType) {
                case 'force_change':
                    // Set flag to force password change on next login
                    $updateFields[] = "force_password_change = 1";
                    $updateFields[] = "password_reset_at = NOW()";
                    $message = 'ผู้ใช้จะถูกบังคับให้เปลี่ยนรหัสผ่านเมื่อเข้าสู่ระบบครั้งถัดไป';
                    break;                case 'set_default':
                    // Set password to default "123456"
                    $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
                    $updateFields[] = "password_hash = ?";
                    $updateFields[] = "force_password_change = 1";
                    $updateFields[] = "password_reset_at = NOW()";
                    $updateValues[] = $defaultPassword;
                    $message = 'รหัสผ่านถูกตั้งเป็น "123456" และผู้ใช้จะถูกบังคับให้เปลี่ยนรหัสผ่านเมื่อเข้าสู่ระบบ';
                    break;

                case 'custom':
                    if (empty($newPassword) || strlen($newPassword) < 6) {
                        $this->sendResponse(false, 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร', null, 400);
                        return;
                    }
                    // Set custom password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateFields[] = "password_hash = ?";
                    $updateFields[] = "password_reset_at = NOW()";
                    $updateValues[] = $hashedPassword;
                    $message = 'รหัสผ่านใหม่ถูกตั้งเรียบร้อยแล้ว';
                    break;

                default:
                    $this->sendResponse(false, 'ประเภทการรีเซ็ตไม่ถูกต้อง', null, 400);
                    return;
            }

            // Add user_id to values for WHERE clause
            $updateValues[] = $userId;

            // Build and execute update query
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt->execute($updateValues)) {
                // Log the password reset action
                $this->logPasswordReset($userId, $resetType, $user['username']);
                
                $this->sendResponse(true, $message);
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน');
            }

        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    private function logPasswordReset($userId, $resetType, $username) {
        try {
            // Add to database column if needed to track password reset requests
            $adminUserId = $_SESSION['user']['user_id'] ?? 0;
            $resetTypeText = '';
            
            switch ($resetType) {
                case 'force_change':
                    $resetTypeText = 'บังคับให้เปลี่ยนรหัสผ่าน';
                    break;
                case 'set_default':
                    $resetTypeText = 'ตั้งรหัสผ่านเริ่มต้น';
                    break;
                case 'custom':
                    $resetTypeText = 'ตั้งรหัสผ่านใหม่';
                    break;
            }

            // Log can be added to a separate audit table if exists
            // For now, we just record the reset timestamp in the users table
            
        } catch (Exception $e) {
            // Silent fail for logging - don't break the main operation
            error_log("Password reset logging failed: " . $e->getMessage());
        }
    }

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
$controller = new UserController();
$controller->handleRequest();
?>