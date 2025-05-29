<?php
// models/Department.php
require_once __DIR__ . '/../config/Database.php';

class Department {
    private $conn;
    
    public function __construct($conn = null) {
        if ($conn) {
            $this->conn = $conn;
        } else {
            $db = new \App\Database();
            $this->conn = $db->getConnection();
        }
    }
    
    /**
     * Get all active departments
     */
    public function getAllDepartments($includeInactive = false) {
        $whereClause = $includeInactive ? '' : 'WHERE is_active = TRUE';
        $sql = "SELECT * FROM departments {$whereClause} ORDER BY department_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get department by ID
     */
    public function getDepartmentById($departmentId) {
        $sql = "SELECT * FROM departments WHERE department_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get department by code
     */
    public function getDepartmentByCode($departmentCode) {
        $sql = "SELECT * FROM departments WHERE department_code = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$departmentCode]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new department
     */
    public function createDepartment($data) {
        $sql = "INSERT INTO departments (department_code, department_name, department_description, is_active) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['department_code'],
            $data['department_name'],
            $data['department_description'] ?? null,
            $data['is_active'] ?? true
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update department
     */
    public function updateDepartment($departmentId, $data) {
        $sql = "UPDATE departments 
                SET department_code = ?, department_name = ?, department_description = ?, is_active = ?
                WHERE department_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['department_code'],
            $data['department_name'],
            $data['department_description'] ?? null,
            $data['is_active'] ?? true,
            $departmentId
        ]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete department (soft delete by setting inactive)
     */
    public function deleteDepartment($departmentId) {
        $sql = "UPDATE departments SET is_active = FALSE WHERE department_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get department statistics
     */
    public function getDepartmentStatistics() {
        $sql = "SELECT 
                    d.department_id,
                    d.department_code,
                    d.department_name,
                    COUNT(u.user_id) as user_count
                FROM departments d
                LEFT JOIN users u ON d.department_id = u.department_id AND u.is_active = TRUE
                WHERE d.is_active = TRUE
                GROUP BY d.department_id, d.department_code, d.department_name
                ORDER BY d.department_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
