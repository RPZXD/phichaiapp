<?php
// controllers/AuthController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Permission.php';
require_once __DIR__ . '/../models/UserLog.php';
require_once __DIR__ . '/../vendor/autoload.php'; // For JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $userModel;
    private $permissionModel;
    private $jwtSecret = 'Pch@iApp2025!#%$@1x9z7y'; // เปลี่ยนเป็นค่าสุ่มที่เดายาก
    private $jwtExpire = 3600; // 1 hour

    public function __construct() {
        $this->userModel = new User();
        $this->permissionModel = new Permission();
    }    public function login($username, $password) {
        $user = $this->userModel->findByUsernameOrEmail($username);
        $userLog = new UserLog();
        if (!$user || !$this->userModel->verifyPassword($user, $password)) {
            if ($user) {
                $userLog->log($user['user_id'], 'login_failed', 'รหัสผ่านไม่ถูกต้อง');
            }
            return ["success" => false, "message" => "ชื่อผู้ใช้, อีเมล หรือรหัสผ่านไม่ถูกต้อง"];
        }

        // Check if user is forced to change password
        if (isset($user['force_password_change']) && $user['force_password_change'] == 1) {
            $userLog->log($user['user_id'], 'login_force_password_change', 'เข้าสู่ระบบต้องเปลี่ยนรหัสผ่าน');
            return [
                "success" => true,
                "force_password_change" => true,
                "user_id" => $user['user_id'],
                "username" => $user['username'],
                "message" => "คุณต้องเปลี่ยนรหัสผ่านก่อนเข้าใช้งานระบบ"
            ];
        }

        $permissions = $this->permissionModel->getUserPermissions($user['user_id']);
        $payload = [
            'iss' => 'phichaiapp',
            'iat' => time(),
            'exp' => time() + $this->jwtExpire,
            'sub' => $user['user_id'],
            'role' => $user['role'],
            'permissions' => $permissions
        ];
        $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');
        $userLog->log($user['user_id'], 'login_success', 'เข้าสู่ระบบสำเร็จ');
        return [
            "success" => true,
            "token" => $jwt,
            "user" => [
                "user_id" => $user['user_id'],
                "username" => $user['username'],
                "role" => $user['role'],
                "permissions" => $permissions
            ]
        ];
    }    public function verifyToken($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($this->jwtSecret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }

    public function changePassword($userId, $newPassword, $currentPassword = null) {
        try {
            $user = $this->userModel->findById($userId);
            if (!$user) {
                return ["success" => false, "message" => "ไม่พบผู้ใช้"];
            }

            // If current password is provided, verify it (for normal password change)
            if ($currentPassword !== null && !$this->userModel->verifyPassword($user, $currentPassword)) {
                return ["success" => false, "message" => "รหัสผ่านปัจจุบันไม่ถูกต้อง"];
            }

            // Validate new password
            if (strlen($newPassword) < 6) {
                return ["success" => false, "message" => "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร"];
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password and remove force change flag
            $result = $this->userModel->updatePassword($userId, $hashedPassword);
            
            if ($result) {
                $userLog = new UserLog();
                $userLog->log($userId, 'password_changed', 'เปลี่ยนรหัสผ่านสำเร็จ');
                return ["success" => true, "message" => "เปลี่ยนรหัสผ่านสำเร็จ"];
            } else {
                return ["success" => false, "message" => "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน"];
            }

        } catch (Exception $e) {
            return ["success" => false, "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()];
        }
    }
}
