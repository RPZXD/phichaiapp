<?php
// models/User.php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;
    private $conn;
    
    public function __construct($conn = null) {
        if ($conn) {
            $this->conn = $conn;
        } else {
            $this->db = new \App\Database();
            $this->conn = $this->db->getConnection();
        }
    }    public function findByUsernameOrEmail($usernameOrEmail) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function verifyPassword($user, $password) {
        // ป้องกัน error หาก password_hash ในฐานข้อมูลเป็น null หรือว่าง
        if (!isset($user['password_hash']) || empty($user['password_hash'])) {
            return false;
        }
        return password_verify($password, $user['password_hash']);
    }    public function getAllUsers() {        $sql = "SELECT u.user_id, u.username, u.email, u.role, u.phone_number, 
                       u.department_id, d.department_name, u.employee_id, u.is_active,
                       u.created_at, u.updated_at,
                       GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_level DESC SEPARATOR ', ') as roles
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.department_id
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id AND r.is_active = TRUE
                GROUP BY u.user_id
                ORDER BY u.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    public function getUserById($id) {        $sql = "SELECT u.user_id, u.username, u.email, u.role, u.phone_number, 
                       u.department_id, d.department_name, u.employee_id, u.is_active,
                       u.force_password_change, u.created_at, u.updated_at
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.department_id
                WHERE u.user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user with all their roles
     */
    public function getUserWithRoles($id) {
        $user = $this->getUserById($id);
        if ($user) {
            $user['roles'] = $this->getUserRoles($id);
        }
        return $user;
    }
      /**
     * Get user's roles
     */    public function getUserRoles($user_id) {
        $sql = "SELECT ur.user_role_id, ur.role_id, r.role_code, r.role_name, r.role_level,
                       ur.assigned_at
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND ur.is_active = TRUE AND r.is_active = TRUE
                ORDER BY r.role_level DESC, ur.assigned_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }public function createUser($data) {
        try {
            $this->conn->beginTransaction();
            
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate employee ID if not provided
            $employeeId = $data['employee_id'] ?? $this->generateEmployeeId();
            
            $sql = "INSERT INTO users (username, email, password_hash, role, phone_number, 
                                     department_id, employee_id, is_active, force_password_change, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['role'], // Keep for backward compatibility
                $data['phone_number'] ?? null,
                $data['department_id'] ?? null,
                $employeeId,
                $data['is_active'] ?? true,
                $data['force_password_change'] ?? false
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                $userId = $this->conn->lastInsertId();
                
                // Assign roles if provided
                if (isset($data['roles']) && is_array($data['roles'])) {
                    $this->assignUserRoles($userId, $data['roles'], $data['assigned_by'] ?? null);
                } elseif (isset($data['role']) && !empty($data['role'])) {
                    // Backward compatibility: assign single role
                    $this->assignRoleByCode($userId, $data['role'], true, $data['department_id'] ?? null);
                }
                
                $this->conn->commit();
                return $userId;
            }
            
            $this->conn->rollBack();
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }    public function updateUser($id, $data) {
        try {
            $this->conn->beginTransaction();
            
            // Update basic user info
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, password_hash = ?, role = ?, 
                               phone_number = ?, department_id = ?, employee_id = ?, is_active = ?, 
                               force_password_change = ?, updated_at = NOW() WHERE user_id = ?";
                $params = [
                    $data['username'], $data['email'], $data['password_hash'], $data['role'],
                    $data['phone_number'], $data['department_id'] ?? null, 
                    $data['employee_id'] ?? null, $data['is_active'] ?? true,
                    $data['force_password_change'] ?? false, $id
                ];
            } else {
                $sql = "UPDATE users SET username = ?, email = ?, role = ?, phone_number = ?, 
                               department_id = ?, employee_id = ?, is_active = ?, updated_at = NOW() WHERE user_id = ?";
                $params = [
                    $data['username'], $data['email'], $data['role'], $data['phone_number'], 
                    $data['department_id'] ?? null, $data['employee_id'] ?? null, 
                    $data['is_active'] ?? true, $id
                ];
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // Update roles if provided
            if (isset($data['roles']) && is_array($data['roles'])) {
                $this->updateUserRoles($id, $data['roles'], $data['assigned_by'] ?? null);
            }
            
            $this->conn->commit();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }public function deleteUser($id) {
        // Use soft delete instead of hard delete to maintain data integrity
        return $this->deactivateUser($id);
    }    public function findById($id) {
        $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE users SET password_hash = ?, force_password_change = 0, last_password_change = NOW(), updated_at = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hashedPassword, $userId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Assign roles to user
     */
    public function assignUserRoles($user_id, $roles, $assigned_by = null) {
        foreach ($roles as $roleData) {
            $this->assignRole(
                $user_id, 
                $roleData['role_id'], 
                $roleData['is_primary'] ?? false,
                $roleData['department_id'] ?? null,
                $assigned_by,
                $roleData['expires_at'] ?? null
            );
        }
    }
      /**
     * Assign single role to user
     */    public function assignRole($user_id, $role_id, $is_primary = false, $department_id = null, $assigned_by = null, $expires_at = null) {
        // Simplified to work with actual table structure
        $sql = "INSERT INTO user_roles (user_id, role_id, assigned_at, is_active)
                VALUES (?, ?, NOW(), TRUE)
                ON DUPLICATE KEY UPDATE assigned_at = NOW(), is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $role_id]);
        return $stmt->rowCount() > 0;
    }
      /**
     * Assign role by code (for backward compatibility)
     */
    public function assignRoleByCode($user_id, $role_code, $is_primary = false, $department_id = null, $assigned_by = null) {
        $sql = "SELECT role_id FROM roles WHERE role_code = ? AND is_active = TRUE LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role_code]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            return $this->assignRole($user_id, $role['role_id'], $is_primary, $department_id, $assigned_by);
        }
        return false;
    }
      /**
     * Remove role from user
     */
    public function removeUserRole($user_id, $role_id, $department_id = null) {
        $whereClause = $department_id ? 'AND department_id = ?' : 'AND department_id IS NULL';
        $params = $department_id ? [$user_id, $role_id, $department_id] : [$user_id, $role_id];
        
        $sql = "UPDATE user_roles SET is_active = FALSE WHERE user_id = ? AND role_id = ? {$whereClause}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update user roles (replace all existing roles)
     */
    public function updateUserRoles($user_id, $roles, $assigned_by = null) {
        // Deactivate all current roles
        $sql = "UPDATE user_roles SET is_active = FALSE WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        
        // Assign new roles
        $this->assignUserRoles($user_id, $roles, $assigned_by);
    }
      /**
     * Get user's primary role
     */    public function getUserPrimaryRole($user_id) {
        // DISABLED: is_primary column doesn't exist in user_roles table
        // Return the first role assigned to the user instead
        $sql = "SELECT r.role_id, r.role_code, r.role_name, r.role_level
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND ur.is_active = TRUE AND r.is_active = TRUE
                ORDER BY r.role_level DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
      /**
     * Check if user has role
     */    public function hasRole($user_id, $role_code, $department_id = null) {
        // Simplified since user_roles table doesn't have department_id column
        $sql = "SELECT COUNT(*) as cnt FROM user_roles ur
                JOIN roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = ? AND r.role_code = ? AND ur.is_active = TRUE AND r.is_active = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $role_code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['cnt'] > 0;
    }
      /**
     * Get users by role
     */    public function getUsersByRole($role_code, $department_id = null) {
        $whereClause = $department_id ? 'AND u.department_id = ?' : '';
        $params = $department_id ? [$role_code, $department_id] : [$role_code];
        
        $sql = "SELECT DISTINCT u.user_id, u.username, u.email, u.phone_number, 
                       d.department_name, ur.assigned_at
                FROM users u
                JOIN user_roles ur ON u.user_id = ur.user_id
                JOIN roles r ON ur.role_id = r.role_id
                LEFT JOIN departments d ON u.department_id = d.department_id
                WHERE r.role_code = ? AND ur.is_active = TRUE AND r.is_active = TRUE AND u.is_active = TRUE {$whereClause}
                ORDER BY ur.assigned_at DESC, u.username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      /**
     * Generate employee ID
     */
    private function generateEmployeeId() {
        $sql = "SELECT MAX(CAST(SUBSTRING(employee_id, 4) AS UNSIGNED)) as max_num 
                FROM users WHERE employee_id LIKE 'EMP%'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'EMP' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }
      /**
     * Get user statistics
     */
    public function getUserStatistics() {        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN is_active = TRUE THEN 1 END) as active_users,
                    COUNT(CASE WHEN is_active = FALSE THEN 1 END) as inactive_users,
                    COUNT(CASE WHEN force_password_change = TRUE THEN 1 END) as users_need_password_change
                FROM users";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
      /**
     * Get users by department
     */
    public function getUsersByDepartment($department_id = null) {
        $whereClause = $department_id ? 'WHERE u.department_id = ?' : '';
        $params = $department_id ? [$department_id] : [];
          $sql = "SELECT u.user_id, u.username, u.email, u.phone_number, u.employee_id,
                       d.department_name, u.is_active,
                       GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_level DESC SEPARATOR ', ') as roles
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.department_id
                LEFT JOIN user_roles ur ON u.user_id = ur.user_id AND ur.is_active = TRUE
                LEFT JOIN roles r ON ur.role_id = r.role_id AND r.is_active = TRUE
                {$whereClause}
                GROUP BY u.user_id
                ORDER BY d.department_name, u.username";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    /**
     * Update last login - DISABLED: last_login column doesn't exist
     */
    public function updateLastLogin($user_id) {
        // $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        // $stmt = $this->conn->prepare($sql);
        // $stmt->execute([$user_id]);
        // return $stmt->rowCount() > 0;
        return true; // Return true to maintain compatibility
    }
      /**
     * Soft delete user (deactivate)
     */
    public function deactivateUser($id) {
        try {
            $this->conn->beginTransaction();
            
            // Deactivate user
            $sql = "UPDATE users SET is_active = FALSE, updated_at = NOW() WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            
            // Deactivate all user roles
            $sql = "UPDATE user_roles SET is_active = FALSE WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
