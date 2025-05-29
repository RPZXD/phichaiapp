-- Migration Script: Transform Single-Role to Multi-Role System
-- This script migrates existing users from single role column to the new multi-role system

-- ================================================
-- 1. BACKUP EXISTING DATA (RECOMMENDED TO RUN FIRST)
-- ================================================

-- Create backup table for existing users
CREATE TABLE IF NOT EXISTS `users_backup_migration` AS 
SELECT * FROM `users` WHERE 1;

-- ================================================
-- 2. MIGRATE EXISTING USER ROLES
-- ================================================

-- Insert user role assignments based on existing role column
INSERT INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `assigned_at`, `is_active`)
SELECT 
    u.user_id,
    r.role_id,
    TRUE, -- Set as primary role
    u.created_at, -- Use user creation date as assignment date
    u.is_active
FROM `users` u
INNER JOIN `roles` r ON u.role = r.role_code
WHERE u.role IS NOT NULL 
  AND u.role != ''
  AND NOT EXISTS (
      SELECT 1 FROM `user_roles` ur 
      WHERE ur.user_id = u.user_id AND ur.role_id = r.role_id
  );

-- ================================================
-- 3. ASSIGN DEPARTMENTS BASED ON ROLE
-- ================================================

-- Update users' department based on their role (customize as needed)
UPDATE `users` u
SET `department_id` = (
    CASE 
        WHEN u.role IN ('admin', 'director', 'vp') THEN 
            (SELECT department_id FROM departments WHERE department_code = 'ADMIN')
        WHEN u.role IN ('teacher', 'head_teacher') THEN 
            (SELECT department_id FROM departments WHERE department_code = 'ACADEMIC')
        WHEN u.role = 'student' THEN 
            (SELECT department_id FROM departments WHERE department_code = 'STUDENT_AFFAIRS')
        WHEN u.role = 'officer' THEN 
            (SELECT department_id FROM departments WHERE department_code = 'ADMIN')
        ELSE NULL
    END
)
WHERE u.department_id IS NULL;

-- ================================================
-- 4. MIGRATE EXISTING USER PERMISSIONS
-- ================================================

-- Update user_permissions table to link with new permission IDs
UPDATE `user_permissions` up
INNER JOIN `permissions` p ON up.permission_code = p.permission_code
SET up.permission_id = p.permission_id
WHERE up.permission_id IS NULL;

-- Clean up orphaned permissions (permissions that don't exist in new permissions table)
DELETE FROM `user_permissions` 
WHERE permission_id IS NULL 
  AND permission_code NOT IN (SELECT permission_code FROM permissions);

-- ================================================
-- 5. SET DEFAULT VALUES FOR NEW COLUMNS
-- ================================================

-- Set default values for new user columns
UPDATE `users` 
SET 
    `is_active` = TRUE 
WHERE `is_active` IS NULL;

UPDATE `users` 
SET 
    `force_password_change` = FALSE 
WHERE `force_password_change` IS NULL;

-- Generate employee IDs for existing users (customize format as needed)
UPDATE `users` 
SET `employee_id` = CONCAT('EMP', LPAD(user_id, 6, '0'))
WHERE `employee_id` IS NULL OR `employee_id` = '';

-- ================================================
-- 6. CREATE SPECIAL CASE ROLE ASSIGNMENTS
-- ================================================

-- Example: Assign additional roles for specific scenarios
-- Users with admin role also get system maintenance permissions
INSERT INTO `user_roles` (`user_id`, `role_id`, `is_primary`, `assigned_at`)
SELECT DISTINCT
    ur.user_id,
    r.role_id,
    FALSE, -- Not primary
    NOW()
FROM `user_roles` ur
INNER JOIN `roles` r_admin ON ur.role_id = r_admin.role_id AND r_admin.role_code = 'admin'
INNER JOIN `roles` r ON r.role_code = 'auditor' -- Add auditor role to admins
WHERE NOT EXISTS (
    SELECT 1 FROM `user_roles` ur2 
    WHERE ur2.user_id = ur.user_id AND ur2.role_id = r.role_id
);

-- ================================================
-- 7. VALIDATION QUERIES
-- ================================================

-- Check migration results
SELECT 
    'Migration Summary' as report_type,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM user_roles) as total_role_assignments,
    (SELECT COUNT(DISTINCT user_id) FROM user_roles) as users_with_roles,
    (SELECT COUNT(*) FROM user_permissions WHERE permission_id IS NOT NULL) as migrated_permissions;

-- Check users without role assignments
SELECT 
    'Users without roles' as report_type,
    user_id, username, email, role
FROM users 
WHERE user_id NOT IN (SELECT DISTINCT user_id FROM user_roles WHERE is_active = TRUE);

-- Check role distribution after migration
SELECT 
    r.role_name,
    r.role_code,
    COUNT(ur.user_id) as user_count,
    COUNT(CASE WHEN ur.is_primary = TRUE THEN 1 END) as primary_assignments
FROM roles r
LEFT JOIN user_roles ur ON r.role_id = ur.role_id AND ur.is_active = TRUE
GROUP BY r.role_id, r.role_name, r.role_code
ORDER BY user_count DESC;

-- Check permission assignments
SELECT 
    p.module,
    p.category,
    COUNT(DISTINCT rp.role_id) as roles_with_permission,
    COUNT(DISTINCT up.user_id) as users_with_direct_permission
FROM permissions p
LEFT JOIN role_permissions rp ON p.permission_id = rp.permission_id AND rp.granted = TRUE
LEFT JOIN user_permissions up ON p.permission_id = up.permission_id AND up.granted = TRUE
GROUP BY p.module, p.category
ORDER BY p.module, p.category;

-- ================================================
-- 8. CLEANUP OLD ROLE COLUMN (OPTIONAL - RUN AFTER VERIFICATION)
-- ================================================

-- WARNING: Only run this after verifying the migration was successful
-- You may want to keep the old role column for a while for reference

-- Add a comment to the old role column to indicate it's deprecated
-- ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(50) COMMENT 'DEPRECATED: Use user_roles table instead';

-- Or rename the column to indicate it's deprecated
-- ALTER TABLE `users` CHANGE COLUMN `role` `role_deprecated` VARCHAR(50);

-- Or drop the column entirely (only after thorough testing)
-- ALTER TABLE `users` DROP COLUMN `role`;

-- ================================================
-- 9. POST-MIGRATION RECOMMENDED ACTIONS
-- ================================================

-- 1. Update application code to use new role system
-- 2. Test all permission checks with new system
-- 3. Train administrators on new role assignment interface
-- 4. Update documentation and user guides
-- 5. Monitor system logs for any permission-related issues

-- Create a migration log entry
INSERT INTO `user_logs` (`user_id`, `action`, `details`, `log_time`, `ip_address`)
VALUES (1, 'system_migration', 'Migrated from single-role to multi-role permission system', NOW(), '127.0.0.1');
