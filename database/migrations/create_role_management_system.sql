-- ===================================================
-- Role Management System Database Schema
-- Author: ICT Team @Phichai School
-- Date: May 27, 2025
-- Description: ระบบจัดการสิทธิ์ตามฝ่ายงาน 4 ฝ่าย
-- ===================================================

-- สร้างตาราง user_roles สำหรับเก็บบทบาทของผู้ใช้ในแต่ละฝ่าย
CREATE TABLE IF NOT EXISTS user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'รหัสผู้ใช้',
    department VARCHAR(50) NOT NULL COMMENT 'ฝ่ายงาน (academic, budget, personnel, general)',
    role VARCHAR(50) NOT NULL COMMENT 'บทบาท (admin, head, officer, teacher, viewer)',
    assigned_by INT COMMENT 'ผู้กำหนดบทบาท',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่กำหนด',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่อัพเดท',
    
    -- สร้าง unique constraint เพื่อป้องกันการซ้ำซ้อน
    UNIQUE KEY unique_user_dept (user_id, department),
    
    -- สร้าง foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(user_id) ON DELETE SET NULL,
    
    -- สร้าง indexes เพื่อเพิ่มประสิทธิภาพ
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_role (role),
    INDEX idx_assigned_at (assigned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางบทบาทผู้ใช้ในแต่ละฝ่าย';

-- สร้างตาราง user_permissions สำหรับเก็บสิทธิ์เฉพาะของผู้ใช้
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'รหัสผู้ใช้',
    department VARCHAR(50) NOT NULL COMMENT 'ฝ่ายงาน',
    module VARCHAR(50) NOT NULL COMMENT 'โมดูล',
    permission VARCHAR(20) NOT NULL COMMENT 'สิทธิ์ (view, create, edit, delete)',
    granted BOOLEAN DEFAULT TRUE COMMENT 'อนุญาตหรือไม่',
    granted_by INT COMMENT 'ผู้อนุญาต',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่อนุญาต',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'วันที่อัพเดท',
    
    -- สร้าง unique constraint เพื่อป้องกันการซ้ำซ้อน
    UNIQUE KEY unique_permission (user_id, department, module, permission),
    
    -- สร้าง foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(user_id) ON DELETE SET NULL,
    
    -- สร้าง indexes
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_module (module),
    INDEX idx_permission (permission),
    INDEX idx_granted (granted),
    INDEX idx_granted_at (granted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางสิทธิ์เฉพาะของผู้ใช้';

-- สร้างตาราง permission_logs สำหรับบันทึกการเปลี่ยนแปลงสิทธิ์
CREATE TABLE IF NOT EXISTS permission_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'รหัสผู้ใช้ที่ถูกเปลี่ยนแปลงสิทธิ์',
    department VARCHAR(50) COMMENT 'ฝ่ายงาน',
    module VARCHAR(50) COMMENT 'โมดูล',
    permission VARCHAR(20) COMMENT 'สิทธิ์',
    action VARCHAR(50) NOT NULL COMMENT 'การกระทำ (role_change, permission_grant, permission_revoke, etc.)',
    old_value JSON COMMENT 'ค่าเดิม',
    new_value JSON COMMENT 'ค่าใหม่',
    changed_by INT COMMENT 'ผู้เปลี่ยนแปลง',
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'วันที่เปลี่ยนแปลง',
    ip_address VARCHAR(45) COMMENT 'IP Address ของผู้เปลี่ยนแปลง',
    user_agent TEXT COMMENT 'User Agent',
    
    -- สร้าง foreign key constraints
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    
    -- สร้าง indexes
    INDEX idx_user_id (user_id),
    INDEX idx_department (department),
    INDEX idx_action (action),
    INDEX idx_changed_by (changed_by),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางบันทึกการเปลี่ยนแปลงสิทธิ์';

-- สร้างตาราง department_modules สำหรับเก็บข้อมูลโมดูลของแต่ละฝ่าย (เผื่อขยายระบบ)
CREATE TABLE IF NOT EXISTS department_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(50) NOT NULL COMMENT 'ฝ่ายงาน',
    module_key VARCHAR(50) NOT NULL COMMENT 'รหัสโมดูล',
    module_name VARCHAR(100) NOT NULL COMMENT 'ชื่อโมดูล',
    module_description TEXT COMMENT 'คำอธิบายโมดูล',
    module_icon VARCHAR(50) COMMENT 'ไอคอนโมดูล',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'เปิดใช้งานหรือไม่',
    sort_order INT DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- สร้าง unique constraint
    UNIQUE KEY unique_dept_module (department, module_key),
    
    -- สร้าง indexes
    INDEX idx_department (department),
    INDEX idx_module_key (module_key),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางโมดูลของแต่ละฝ่าย';

-- เพิ่มข้อมูลโมดูลเริ่มต้น
INSERT INTO department_modules (department, module_key, module_name, module_description, module_icon, sort_order) VALUES
-- ฝ่ายวิชาการ
('academic', 'curriculum', 'หลักสูตร', 'จัดการหลักสูตรการเรียนการสอน', 'fa-book', 1),
('academic', 'schedule', 'ตารางเรียน', 'จัดการตารางเรียนและตารางสอน', 'fa-calendar-alt', 2),
('academic', 'assessment', 'การประเมิน', 'จัดการการประเมินผลการเรียน', 'fa-chart-line', 3),
('academic', 'academic_report', 'รายงานวิชาการ', 'รายงานผลการเรียนและสถิติ', 'fa-file-alt', 4),

-- ฝ่ายงบประมาณ
('budget', 'budget_plan', 'แผนงบประมาณ', 'วางแผนและจัดสรรงบประมาณ', 'fa-chart-pie', 1),
('budget', 'expenses', 'รายจ่าย', 'บันทึกและติดตามรายจ่าย', 'fa-money-bill-wave', 2),
('budget', 'procurement', 'จัดซื้อจัดจ้าง', 'จัดการการจัดซื้อจัดจ้าง', 'fa-shopping-cart', 3),
('budget', 'finance_report', 'รายงานการเงิน', 'รายงานทางการเงินและงบประมาณ', 'fa-calculator', 4),

-- ฝ่ายบุคคล
('personnel', 'staff_management', 'จัดการบุคลากร', 'จัดการข้อมูลบุคลากร', 'fa-user-tie', 1),
('personnel', 'attendance', 'ลงเวลาทำงาน', 'ระบบลงเวลาเข้า-ออกงาน', 'fa-clock', 2),
('personnel', 'leave_management', 'จัดการลาหยุด', 'จัดการการลาและวันหยุด', 'fa-calendar-times', 3),
('personnel', 'hr_report', 'รายงานบุคลากร', 'รายงานและสถิติบุคลากร', 'fa-users-cog', 4),

-- ฝ่ายทั่วไป
('general', 'facility', 'สิ่งอำนวยความสะดวก', 'จัดการสิ่งอำนวยความสะดวก', 'fa-building', 1),
('general', 'maintenance', 'ซ่อมบำรุง', 'จัดการการซ่อมบำรุง', 'fa-tools', 2),
('general', 'inventory', 'พัสดุ', 'จัดการพัสดุและครุภัณฑ์', 'fa-boxes', 3),
('general', 'general_report', 'รายงานทั่วไป', 'รายงานงานทั่วไปและสนับสนุน', 'fa-clipboard-list', 4);

-- สร้างตาราง role_definitions สำหรับเก็บนิยามบทบาท (เผื่อขยายระบบ)
CREATE TABLE IF NOT EXISTS role_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(50) NOT NULL COMMENT 'รหัสบทบาท',
    role_name VARCHAR(100) NOT NULL COMMENT 'ชื่อบทบาท',
    role_description TEXT COMMENT 'คำอธิบายบทบาท',
    role_level INT NOT NULL DEFAULT 1 COMMENT 'ระดับสิทธิ์ (1-5)',
    default_permissions JSON COMMENT 'สิทธิ์เริ่มต้น',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'เปิดใช้งานหรือไม่',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- สร้าง unique constraint
    UNIQUE KEY unique_role_key (role_key),
    
    -- สร้าง indexes
    INDEX idx_role_level (role_level),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางนิยามบทบาท';

-- เพิ่มข้อมูลบทบาทเริ่มต้น
INSERT INTO role_definitions (role_key, role_name, role_description, role_level, default_permissions) VALUES
('admin', 'Admin', 'ผู้ดูแลระบบ มีสิทธิ์สูงสุด', 5, '{"view": true, "create": true, "edit": true, "delete": true, "admin": true}'),
('head', 'หัวหน้าฝ่าย', 'หัวหน้าฝ่ายงาน สามารถจัดการในฝ่ายได้', 4, '{"view": true, "create": true, "edit": true, "delete": false}'),
('officer', 'เจ้าหน้าที่', 'เจ้าหน้าที่ประจำฝ่าย ดำเนินงานหลัก', 3, '{"view": true, "create": true, "edit": false, "delete": false}'),
('teacher', 'ครู', 'บุคลากรทางการศึกษา', 2, '{"view": true, "create": false, "edit": false, "delete": false}'),
('viewer', 'ผู้ชม', 'สามารถดูข้อมูลได้เท่านั้น', 1, '{"view": true, "create": false, "edit": false, "delete": false}');

-- สร้าง Views สำหรับการใช้งานที่สะดวก
-- View สำหรับดูข้อมูลสิทธิ์ผู้ใช้แบบรวม
CREATE OR REPLACE VIEW user_permissions_summary AS
SELECT 
    u.user_id,
    u.username,
    u.email,
    ur.department,
    ur.role,
    rd.role_name,
    rd.role_level,
    GROUP_CONCAT(
        CONCAT(dm.module_name, ':', 
               IF(up.granted IS NOT NULL, 
                  CONCAT(up.permission, '=', up.granted), 
                  'default'
               )
        ) SEPARATOR '; '
    ) as permissions_detail,
    ur.assigned_at,
    ur.updated_at
FROM users u
LEFT JOIN user_roles ur ON u.user_id = ur.user_id
LEFT JOIN role_definitions rd ON ur.role = rd.role_key
LEFT JOIN department_modules dm ON ur.department = dm.department
LEFT JOIN user_permissions up ON (
    u.user_id = up.user_id 
    AND ur.department = up.department 
    AND dm.module_key = up.module
)
WHERE u.user_id IS NOT NULL
GROUP BY u.user_id, ur.department, ur.role
ORDER BY u.username, ur.department;

-- View สำหรับดูสถิติการใช้งานสิทธิ์
CREATE OR REPLACE VIEW permission_statistics AS
SELECT 
    department,
    role,
    COUNT(DISTINCT user_id) as user_count,
    COUNT(*) as total_assignments,
    MIN(assigned_at) as first_assigned,
    MAX(updated_at) as last_updated
FROM user_roles 
GROUP BY department, role
ORDER BY department, role;

-- สร้าง Stored Procedures สำหรับการใช้งานที่ซับซ้อน

DELIMITER ;;

-- Procedure สำหรับตรวจสอบสิทธิ์แบบละเอียด
CREATE PROCEDURE CheckUserPermission(
    IN p_user_id INT,
    IN p_department VARCHAR(50),
    IN p_module VARCHAR(50),
    IN p_action VARCHAR(20),
    OUT p_has_permission BOOLEAN,
    OUT p_source VARCHAR(50)
)
BEGIN
    DECLARE v_explicit_permission BOOLEAN DEFAULT NULL;
    DECLARE v_role VARCHAR(50) DEFAULT NULL;
    DECLARE v_role_permission BOOLEAN DEFAULT FALSE;
    
    -- ตรวจสอบสิทธิ์เฉพาะก่อน
    SELECT granted INTO v_explicit_permission
    FROM user_permissions 
    WHERE user_id = p_user_id 
      AND department = p_department 
      AND module = p_module 
      AND permission = p_action
    LIMIT 1;
    
    IF v_explicit_permission IS NOT NULL THEN
        SET p_has_permission = v_explicit_permission;
        SET p_source = 'explicit';
    ELSE
        -- ตรวจสอบตามบทบาท
        SELECT role INTO v_role
        FROM user_roles 
        WHERE user_id = p_user_id AND department = p_department
        LIMIT 1;
        
        IF v_role IS NOT NULL THEN
            SELECT JSON_EXTRACT(default_permissions, CONCAT('$.', p_action)) INTO v_role_permission
            FROM role_definitions 
            WHERE role_key = v_role;
            
            SET p_has_permission = COALESCE(v_role_permission, FALSE);
            SET p_source = 'role';
        ELSE
            SET p_has_permission = FALSE;
            SET p_source = 'none';
        END IF;
    END IF;
END;;

-- Procedure สำหรับกำหนดสิทธิ์แบบกลุ่ม
CREATE PROCEDURE BulkAssignPermissions(
    IN p_user_ids JSON,
    IN p_department VARCHAR(50),
    IN p_role VARCHAR(50),
    IN p_assigned_by INT
)
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE v_user_id INT;
    DECLARE v_count INT;
    
    SET v_count = JSON_LENGTH(p_user_ids);
    
    START TRANSACTION;
    
    WHILE i < v_count DO
        SET v_user_id = JSON_EXTRACT(p_user_ids, CONCAT('$[', i, ']'));
        
        -- Insert or update role
        INSERT INTO user_roles (user_id, department, role, assigned_by)
        VALUES (v_user_id, p_department, p_role, p_assigned_by)
        ON DUPLICATE KEY UPDATE 
            role = p_role, 
            assigned_by = p_assigned_by,
            updated_at = CURRENT_TIMESTAMP;
        
        -- Log the change
        INSERT INTO permission_logs (user_id, department, action, new_value, changed_by)
        VALUES (v_user_id, p_department, 'bulk_role_assign', 
                JSON_OBJECT('role', p_role), p_assigned_by);
        
        SET i = i + 1;
    END WHILE;
    
    COMMIT;
END;;

DELIMITER ;

-- สร้าง Indexes เพิ่มเติมเพื่อเพิ่มประสิทธิภาพ
CREATE INDEX idx_user_permissions_lookup ON user_permissions (user_id, department, module, permission);
CREATE INDEX idx_permission_logs_user_date ON permission_logs (user_id, changed_at);
CREATE INDEX idx_user_roles_dept_role ON user_roles (department, role);

-- สร้าง Triggers สำหรับ audit trail
DELIMITER ;;

CREATE TRIGGER user_roles_audit_insert
AFTER INSERT ON user_roles
FOR EACH ROW
BEGIN
    INSERT INTO permission_logs (user_id, department, action, new_value, changed_by, changed_at)
    VALUES (NEW.user_id, NEW.department, 'role_assigned', 
            JSON_OBJECT('role', NEW.role), NEW.assigned_by, NEW.assigned_at);
END;;

CREATE TRIGGER user_roles_audit_update
AFTER UPDATE ON user_roles
FOR EACH ROW
BEGIN
    IF OLD.role != NEW.role THEN
        INSERT INTO permission_logs (user_id, department, action, old_value, new_value, changed_by)
        VALUES (NEW.user_id, NEW.department, 'role_changed',
                JSON_OBJECT('role', OLD.role),
                JSON_OBJECT('role', NEW.role),
                NEW.assigned_by);
    END IF;
END;;

CREATE TRIGGER user_permissions_audit_insert
AFTER INSERT ON user_permissions
FOR EACH ROW
BEGIN
    INSERT INTO permission_logs (user_id, department, module, permission, action, new_value, changed_by)
    VALUES (NEW.user_id, NEW.department, NEW.module, NEW.permission, 'permission_granted',
            JSON_OBJECT('granted', NEW.granted), NEW.granted_by);
END;;

DELIMITER ;

-- เพิ่ม Comments สำหรับ documentation
ALTER TABLE user_roles COMMENT = 'ตารางเก็บบทบาทของผู้ใช้ในแต่ละฝ่ายงาน - รองรับการมีหลายบทบาทในหลายฝ่าย';
ALTER TABLE user_permissions COMMENT = 'ตารางเก็บสิทธิ์เฉพาะของผู้ใช้ - สามารถ override สิทธิ์จากบทบาทได้';
ALTER TABLE permission_logs COMMENT = 'ตารางบันทึกการเปลี่ยนแปลงสิทธิ์ทั้งหมด - สำหรับ audit trail';
ALTER TABLE department_modules COMMENT = 'ตารางเก็บโมดูลของแต่ละฝ่าย - สำหรับการขยายระบบ';
ALTER TABLE role_definitions COMMENT = 'ตารางนิยามบทบาทและสิทธิ์เริ่มต้น - สำหรับการจัดการแบบยืดหยุ่น';

-- สร้างผู้ใช้ตัวอย่างและกำหนดสิทธิ์ (สำหรับทดสอบ)
-- INSERT INTO user_roles (user_id, department, role, assigned_by) VALUES
-- (1, 'academic', 'head', 1),
-- (1, 'general', 'teacher', 1),
-- (2, 'budget', 'officer', 1),
-- (3, 'personnel', 'viewer', 1);

-- แสดงข้อมูลสถิติหลังสร้างตาราง
SELECT 'Role Management Tables Created Successfully!' as Status;
SELECT TABLE_NAME, TABLE_COMMENT 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN ('user_roles', 'user_permissions', 'permission_logs', 'department_modules', 'role_definitions');

-- แสดงข้อมูลโมดูลที่สร้างไว้
SELECT department, COUNT(*) as module_count, GROUP_CONCAT(module_name SEPARATOR ', ') as modules
FROM department_modules 
GROUP BY department;

-- แสดงข้อมูลบทบาทที่สร้างไว้
SELECT role_key, role_name, role_level, default_permissions 
FROM role_definitions 
ORDER BY role_level DESC;
