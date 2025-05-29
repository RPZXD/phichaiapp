-- Sample Data for Enhanced Role and Permission Management System
-- This file contains basic roles, permissions, and their mappings

-- ================================================
-- 1. INSERT DEPARTMENTS
-- ================================================

INSERT INTO `departments` (`department_code`, `department_name`, `department_description`) VALUES
('ADMIN', 'ฝ่ายบริหาร', 'ฝ่ายบริหารจัดการองค์กร'),
('ACADEMIC', 'ฝ่ายวิชาการ', 'ฝ่ายจัดการด้านการเรียนการสอน'),
('STUDENT_AFFAIRS', 'ฝ่ายกิจการนักเรียน', 'ฝ่ายดูแลกิจกรรมและความเป็นอยู่ของนักเรียน'),
('IT', 'ฝ่ายเทคโนโลยีสารสนเทศ', 'ฝ่ายจัดการระบบคอมพิวเตอร์และเทคโนโลยี'),
('FINANCE', 'ฝ่ายการเงิน', 'ฝ่ายจัดการด้านการเงินและบัญชี'),
('HR', 'ฝ่ายทรัพยากรบุคคล', 'ฝ่ายจัดการบุคลากรและการพัฒนาบุคคล');

-- ================================================
-- 2. INSERT ROLES
-- ================================================

INSERT INTO `roles` (`role_code`, `role_name`, `role_description`, `role_level`, `is_active`) VALUES
-- High-level administrative roles
('admin', 'ผู้ดูแลระบบ', 'ผู้ดูแลระบบสูงสุด มีสิทธิ์เข้าถึงทุกฟังก์ชัน', 100, TRUE),
('director', 'ผู้อำนวยการ', 'ผู้บริหารสูงสุดของสถานศึกษา', 90, TRUE),
('vp', 'รองผู้อำนวยการ', 'รองผู้อำนวยการสถานศึกษา', 85, TRUE),
('hod', 'หัวหน้าแผนก', 'หัวหน้าแผนก/ฝ่าย', 80, TRUE),

-- Operational roles
('officer', 'เจ้าหน้าที่', 'เจ้าหน้าที่ปฏิบัติงานทั่วไป', 60, TRUE),
('teacher', 'ครู', 'ครูผู้สอน', 70, TRUE),
('head_teacher', 'หัวหน้าครู', 'หัวหน้าครูประจำชั้น/วิชา', 75, TRUE),

-- Student and parent roles
('student', 'นักเรียน', 'นักเรียนในสถานศึกษา', 10, TRUE),
('parent', 'ผู้ปกครอง', 'ผู้ปกครองนักเรียน', 15, TRUE),

-- Special roles
('guest', 'ผู้เยี่ยมชม', 'บุคคลภายนอกที่ได้รับอนุญาตเข้าดูข้อมูลบางส่วน', 5, TRUE),
('auditor', 'ผู้ตรวจสอบ', 'ผู้ตรวจสอบระบบและข้อมูล', 65, TRUE);

-- ================================================
-- 3. INSERT PERMISSIONS BY MODULE
-- ================================================

-- User Management Permissions
INSERT INTO `permissions` (`permission_code`, `permission_name`, `permission_description`, `module`, `category`, `is_system`) VALUES
('user.view', 'ดูข้อมูลผู้ใช้', 'สามารถดูรายชื่อและข้อมูลผู้ใช้', 'user_management', 'read', TRUE),
('user.create', 'เพิ่มผู้ใช้', 'สามารถเพิ่มผู้ใช้ใหม่', 'user_management', 'write', TRUE),
('user.edit', 'แก้ไขผู้ใช้', 'สามารถแก้ไขข้อมูลผู้ใช้', 'user_management', 'write', TRUE),
('user.delete', 'ลบผู้ใช้', 'สามารถลบผู้ใช้', 'user_management', 'delete', TRUE),
('user.manage_roles', 'จัดการบทบาทผู้ใช้', 'สามารถกำหนดและเปลี่ยนบทบาทผู้ใช้', 'user_management', 'admin', TRUE),
('user.manage_permissions', 'จัดการสิทธิ์ผู้ใช้', 'สามารถกำหนดสิทธิ์เฉพาะให้ผู้ใช้', 'user_management', 'admin', TRUE),
('user.reset_password', 'รีเซ็ตรหัสผ่าน', 'สามารถรีเซ็ตรหัสผ่านผู้ใช้', 'user_management', 'admin', FALSE),
('user.view_own', 'ดูข้อมูลของตนเอง', 'สามารถดูข้อมูลของตนเองเท่านั้น', 'user_management', 'self', TRUE),
('user.edit_own', 'แก้ไขข้อมูลของตนเอง', 'สามารถแก้ไขข้อมูลของตนเองเท่านั้น', 'user_management', 'self', TRUE),

-- Student Management Permissions
('student.view', 'ดูข้อมูลนักเรียน', 'สามารถดูข้อมูลนักเรียน', 'student_management', 'read', FALSE),
('student.create', 'เพิ่มนักเรียน', 'สามารถเพิ่มข้อมูลนักเรียนใหม่', 'student_management', 'write', FALSE),
('student.edit', 'แก้ไขข้อมูลนักเรียน', 'สามารถแก้ไขข้อมูลนักเรียน', 'student_management', 'write', FALSE),
('student.delete', 'ลบข้อมูลนักเรียน', 'สามารถลบข้อมูลนักเรียน', 'student_management', 'delete', FALSE),
('student.view_grades', 'ดูเกรดนักเรียน', 'สามารถดูเกรดและผลการเรียน', 'student_management', 'academic', FALSE),
('student.edit_grades', 'แก้ไขเกรดนักเรียน', 'สามารถแก้ไขเกรดและผลการเรียน', 'student_management', 'academic', FALSE),
('student.view_own_data', 'ดูข้อมูลการเรียนของตนเอง', 'นักเรียนสามารถดูข้อมูลการเรียนของตนเอง', 'student_management', 'self', FALSE),
('student.view_child_data', 'ดูข้อมูลการเรียนของบุตร', 'ผู้ปกครองสามารถดูข้อมูลการเรียนของบุตร', 'student_management', 'family', FALSE),

-- Academic Management Permissions
('academic.view_curriculum', 'ดูหลักสูตร', 'สามารถดูข้อมูลหลักสูตร', 'academic', 'read', FALSE),
('academic.manage_curriculum', 'จัดการหลักสูตร', 'สามารถจัดการและแก้ไขหลักสูตร', 'academic', 'admin', FALSE),
('academic.view_schedule', 'ดูตารางเรียน', 'สามารถดูตารางเรียน', 'academic', 'read', FALSE),
('academic.manage_schedule', 'จัดการตารางเรียน', 'สามารถจัดการและแก้ไขตารางเรียน', 'academic', 'admin', FALSE),
('academic.view_attendance', 'ดูการเข้าเรียน', 'สามารถดูข้อมูลการเข้าเรียน', 'academic', 'read', FALSE),
('academic.manage_attendance', 'จัดการการเข้าเรียน', 'สามารถบันทึกและแก้ไขการเข้าเรียน', 'academic', 'write', FALSE),

-- Report Permissions
('report.view_basic', 'ดูรายงานพื้นฐาน', 'สามารถดูรายงานทั่วไป', 'reports', 'read', FALSE),
('report.view_advanced', 'ดูรายงานขั้นสูง', 'สามารถดูรายงานขั้นสูงและรายละเอียด', 'reports', 'advanced', FALSE),
('report.export', 'ส่งออกรายงาน', 'สามารถส่งออกรายงานเป็นไฟล์', 'reports', 'export', FALSE),
('report.view_financial', 'ดูรายงานการเงิน', 'สามารถดูรายงานด้านการเงิน', 'reports', 'financial', FALSE),

-- System Administration Permissions
('system.view_logs', 'ดูบันทึกระบบ', 'สามารถดูบันทึกการใช้งานระบบ', 'system', 'admin', TRUE),
('system.manage_settings', 'จัดการการตั้งค่า', 'สามารถจัดการการตั้งค่าระบบ', 'system', 'admin', TRUE),
('system.backup', 'สำรองข้อมูล', 'สามารถสำรองข้อมูลระบบ', 'system', 'admin', TRUE),
('system.restore', 'คืนค่าข้อมูล', 'สามารถคืนค่าข้อมูลระบบ', 'system', 'admin', TRUE),
('system.maintenance', 'ปรับปรุงระบบ', 'สามารถเข้าสู่โหมดปรับปรุงระบบ', 'system', 'admin', TRUE),

-- Department Management Permissions
('department.view', 'ดูข้อมูลแผนก', 'สามารถดูข้อมูลแผนก/ฝ่าย', 'department', 'read', FALSE),
('department.manage', 'จัดการแผนก', 'สามารถจัดการข้อมูลแผนก/ฝ่าย', 'department', 'admin', FALSE),
('department.assign_users', 'มอบหมายผู้ใช้ให้แผนก', 'สามารถมอบหมายผู้ใช้เข้าแผนก', 'department', 'admin', FALSE);

-- ================================================
-- 4. ASSIGN PERMISSIONS TO ROLES
-- ================================================

-- Admin Role - Has all permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`, `granted_at`)
SELECT r.role_id, p.permission_id, TRUE, NOW()
FROM roles r, permissions p
WHERE r.role_code = 'admin';

-- Director Role - High-level management permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'director'
AND p.permission_code IN (
    'user.view', 'user.create', 'user.edit', 'user.manage_roles',
    'student.view', 'student.create', 'student.edit', 'student.view_grades',
    'academic.view_curriculum', 'academic.manage_curriculum', 'academic.view_schedule', 'academic.manage_schedule',
    'academic.view_attendance', 'academic.manage_attendance',
    'report.view_basic', 'report.view_advanced', 'report.export', 'report.view_financial',
    'department.view', 'department.manage', 'department.assign_users',
    'system.view_logs', 'system.manage_settings'
);

-- VP Role - Deputy management permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'vp'
AND p.permission_code IN (
    'user.view', 'user.create', 'user.edit',
    'student.view', 'student.create', 'student.edit', 'student.view_grades',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.manage_schedule',
    'academic.view_attendance', 'academic.manage_attendance',
    'report.view_basic', 'report.view_advanced', 'report.export',
    'department.view', 'department.manage'
);

-- Head of Department Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'hod'
AND p.permission_code IN (
    'user.view', 'user.edit',
    'student.view', 'student.edit', 'student.view_grades', 'student.edit_grades',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.view_attendance', 'academic.manage_attendance',
    'report.view_basic', 'report.view_advanced',
    'department.view'
);

-- Head Teacher Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'head_teacher'
AND p.permission_code IN (
    'user.view', 'user.view_own', 'user.edit_own',
    'student.view', 'student.edit', 'student.view_grades', 'student.edit_grades',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.view_attendance', 'academic.manage_attendance',
    'report.view_basic'
);

-- Teacher Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'teacher'
AND p.permission_code IN (
    'user.view_own', 'user.edit_own',
    'student.view', 'student.view_grades', 'student.edit_grades',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.view_attendance', 'academic.manage_attendance',
    'report.view_basic'
);

-- Officer Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'officer'
AND p.permission_code IN (
    'user.view', 'user.view_own', 'user.edit_own',
    'student.view', 'student.create', 'student.edit',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.view_attendance',
    'report.view_basic',
    'department.view'
);

-- Student Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'student'
AND p.permission_code IN (
    'user.view_own', 'user.edit_own',
    'student.view_own_data',
    'academic.view_schedule'
);

-- Parent Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'parent'
AND p.permission_code IN (
    'user.view_own', 'user.edit_own',
    'student.view_child_data',
    'academic.view_schedule'
);

-- Auditor Role
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'auditor'
AND p.permission_code IN (
    'user.view',
    'student.view', 'student.view_grades',
    'academic.view_curriculum', 'academic.view_schedule', 'academic.view_attendance',
    'report.view_basic', 'report.view_advanced', 'report.export',
    'system.view_logs',
    'department.view'
);

-- Guest Role - Very limited permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted`)
SELECT r.role_id, p.permission_id, TRUE
FROM roles r, permissions p
WHERE r.role_code = 'guest'
AND p.permission_code IN (
    'academic.view_schedule'
);

-- ================================================
-- 5. CREATE SAMPLE USER ROLE ASSIGNMENTS
-- ================================================

-- Note: These are examples. You'll need to adjust user_id values based on your actual users table
-- Assuming you have users with IDs 1-10

-- Example: User 1 is admin
-- INSERT INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `assigned_at`)
-- SELECT 1, role_id, TRUE, NOW() FROM roles WHERE role_code = 'admin';

-- Example: User 2 is director
-- INSERT INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `assigned_at`)
-- SELECT 2, role_id, TRUE, NOW() FROM roles WHERE role_code = 'director';

-- Example: User 3 is both teacher and hod for different departments
-- INSERT INTO `user_roles` (`user_id`, `role_id`, `department_id`, `is_primary`, `assigned_at`)
-- VALUES 
-- (3, (SELECT role_id FROM roles WHERE role_code = 'teacher'), 2, TRUE, NOW()),
-- (3, (SELECT role_id FROM roles WHERE role_code = 'hod'), 2, FALSE, NOW());

-- ================================================
-- 6. INITIAL DATA VERIFICATION QUERIES
-- ================================================

-- Check roles created
-- SELECT * FROM roles ORDER BY role_level DESC;

-- Check permissions by module
-- SELECT module, COUNT(*) as permission_count FROM permissions GROUP BY module;

-- Check role-permission assignments
-- SELECT r.role_name, COUNT(rp.permission_id) as permission_count
-- FROM roles r
-- LEFT JOIN role_permissions rp ON r.role_id = rp.role_id AND rp.granted = TRUE
-- GROUP BY r.role_id, r.role_name
-- ORDER BY r.role_level DESC;
