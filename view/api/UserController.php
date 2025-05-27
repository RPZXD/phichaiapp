<?php
require_once('../../config/Database.php');

use App\Database;

class UserController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function handleRequest() {
        // Set headers for JSON response
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');

        // Get the action from GET or POST
        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        try {            switch ($action) {
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
            
            // Build WHERE clause
            $whereConditions = [];
            $params = [];
            
            if (!empty($search)) {
                $whereConditions[] = "(username LIKE :search OR email LIKE :search OR phone_number LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if (!empty($role)) {
                $whereConditions[] = "role = :role";
                $params[':role'] = $role;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM users $whereClause";
            $countStmt = $this->conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get users with pagination
            $query = "SELECT user_id, username, email, role, phone_number, created_at, updated_at 
                     FROM users 
                     $whereClause
                     ORDER BY user_id DESC 
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
    }

    private function getUser() {
        try {
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                $this->sendResponse(false, 'ไม่พบ ID ผู้ใช้', null, 400);
                return;
            }

            $query = "SELECT user_id, username, email, role, phone_number, created_at, updated_at 
                     FROM users 
                     WHERE user_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $this->sendResponse(true, 'ดึงข้อมูลผู้ใช้สำเร็จ', $user);
            } else {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้: ' . $e->getMessage());
        }
    }

    private function createUser() {
        try {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';
            $phone_number = $_POST['phone_number'] ?? null;

            // Validate required fields
            if (empty($username) || empty($email) || empty($password) || empty($role)) {
                $this->sendResponse(false, 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', null, 400);
                return;
            }

            // Check if username already exists
            $checkQuery = "SELECT user_id FROM users WHERE username = :username";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $this->sendResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if email already exists
            $checkEmailQuery = "SELECT user_id FROM users WHERE email = :email";
            $checkEmailStmt = $this->conn->prepare($checkEmailQuery);
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->execute();
            
            if ($checkEmailStmt->rowCount() > 0) {
                $this->sendResponse(false, 'อีเมลนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $query = "INSERT INTO users (username, email, password_hash, role, phone_number, created_at, updated_at) 
                     VALUES (:username, :email, :password_hash, :role, :phone_number, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':phone_number', $phone_number);
            
            if ($stmt->execute()) {
                $this->sendResponse(true, 'เพิ่มผู้ใช้สำเร็จ', ['user_id' => $this->conn->lastInsertId()]);
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
            $role = $_POST['role'] ?? '';
            $phone_number = $_POST['phone_number'] ?? null;

            if (!$id || empty($username) || empty($email) || empty($role)) {
                $this->sendResponse(false, 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน', null, 400);
                return;
            }

            // Check if username already exists (excluding current user)
            $checkQuery = "SELECT user_id FROM users WHERE username = :username AND user_id != :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $this->sendResponse(false, 'ชื่อผู้ใช้นี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Check if email already exists (excluding current user)
            $checkEmailQuery = "SELECT user_id FROM users WHERE email = :email AND user_id != :id";
            $checkEmailStmt = $this->conn->prepare($checkEmailQuery);
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkEmailStmt->execute();
            
            if ($checkEmailStmt->rowCount() > 0) {
                $this->sendResponse(false, 'อีเมลนี้มีอยู่แล้ว', null, 400);
                return;
            }

            // Update user
            $query = "UPDATE users 
                     SET username = :username, email = :email, role = :role, 
                         phone_number = :phone_number, updated_at = NOW() 
                     WHERE user_id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
              if ($stmt->execute()) {
                $this->sendResponse(true, 'อัพเดทข้อมูลผู้ใช้สำเร็จ');
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
            $checkQuery = "SELECT user_id FROM users WHERE user_id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                $this->sendResponse(false, 'ไม่พบผู้ใช้', null, 404);
                return;
            }

            // Delete user
            $query = "DELETE FROM users WHERE user_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->sendResponse(true, 'ลบผู้ใช้สำเร็จ');
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage());
        }
    }

    private function bulkDeleteUsers() {
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
            
            // Create placeholders for IN clause
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            
            $query = "DELETE FROM users WHERE user_id IN ($placeholders)";
            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute($userIds)) {
                $deletedCount = $stmt->rowCount();
                $this->sendResponse(true, "ลบผู้ใช้สำเร็จ {$deletedCount} รายการ", ['deleted_count' => $deletedCount]);
            } else {
                $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage());
        }
    }

    private function getStatistics() {
        try {
            // Total users
            $totalQuery = "SELECT COUNT(*) as total FROM users";
            $totalStmt = $this->conn->prepare($totalQuery);
            $totalStmt->execute();
            $totalUsers = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Active users (assuming users created in last 30 days are active)
            $activeQuery = "SELECT COUNT(*) as active FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $activeStmt = $this->conn->prepare($activeQuery);
            $activeStmt->execute();
            $activeUsers = $activeStmt->fetch(PDO::FETCH_ASSOC)['active'];

            // Admin count
            $adminQuery = "SELECT COUNT(*) as admins FROM users WHERE role = 'admin'";
            $adminStmt = $this->conn->prepare($adminQuery);
            $adminStmt->execute();
            $adminCount = $adminStmt->fetch(PDO::FETCH_ASSOC)['admins'];

            // New users today
            $todayQuery = "SELECT COUNT(*) as today FROM users WHERE DATE(created_at) = CURDATE()";
            $todayStmt = $this->conn->prepare($todayQuery);
            $todayStmt->execute();
            $newUsersToday = $todayStmt->fetch(PDO::FETCH_ASSOC)['today'];

            $statistics = [
                'total_users' => (int)$totalUsers,
                'active_users' => (int)$activeUsers,
                'admin_count' => (int)$adminCount,
                'new_users_today' => (int)$newUsersToday
            ];

            $this->sendResponse(true, 'ดึงข้อมูลสถิติสำเร็จ', $statistics);
        } catch (Exception $e) {
            $this->sendResponse(false, 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ: ' . $e->getMessage());
        }
    }

    private function getRoleDistribution() {
        try {
            $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format role names
            $roleNames = [
                'student' => 'นักเรียน',
                'teacher' => 'ครู',
                'admin' => 'ผู้ดูแลระบบ',
                'officer' => 'เจ้าหน้าที่',
                'director' => 'ผู้อำนวยการ',
                'vp' => 'รองผู้อำนวยการ',
                'hod' => 'หัวหน้าแผนก',
                'parent' => 'ผู้ปกครอง'
            ];

            foreach ($distribution as &$item) {
                $item['role'] = $roleNames[$item['role']] ?? $item['role'];
            }            $this->sendResponse(true, 'ดึงข้อมูลการกระจายบทบาทสำเร็จ', $distribution);
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