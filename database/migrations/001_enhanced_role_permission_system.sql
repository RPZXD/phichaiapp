-- Enhanced Role and Permission Management System
-- This migration transforms the single-role system into a flexible multi-role system

-- ================================================
-- 1. CREATE NEW TABLES FOR FLEXIBLE ROLE SYSTEM
-- ================================================

-- Roles table - Define available roles in the system
CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_code` VARCHAR(50) UNIQUE NOT NULL,
    `role_name` VARCHAR(100) NOT NULL,
    `role_description` TEXT,
    `role_level` INT DEFAULT 1, -- Higher number = higher authority
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_role_code` (`role_code`),
    INDEX `idx_role_level` (`role_level`)
);

-- User-Role mapping - Allow multiple roles per user
CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_role_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `department_id` INT NULL, -- Optional: Role specific to department
    `is_primary` BOOLEAN DEFAULT FALSE, -- One role can be marked as primary
    `assigned_by` INT NULL, -- User ID who assigned this role
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` DATETIME NULL, -- Optional expiration
    `is_active` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_user_role_dept` (`user_id`, `role_id`, `department_id`),
    INDEX `idx_user_roles_user` (`user_id`),
    INDEX `idx_user_roles_role` (`role_id`),
    INDEX `idx_user_roles_active` (`is_active`)
);

-- Permissions table - Define available permissions
CREATE TABLE IF NOT EXISTS `permissions` (
    `permission_id` INT PRIMARY KEY AUTO_INCREMENT,
    `permission_code` VARCHAR(100) UNIQUE NOT NULL,
    `permission_name` VARCHAR(150) NOT NULL,
    `permission_description` TEXT,
    `module` VARCHAR(50) NOT NULL, -- Module/feature this permission belongs to
    `category` VARCHAR(50) DEFAULT 'general', -- Category for grouping
    `is_system` BOOLEAN DEFAULT FALSE, -- System permissions that cannot be deleted
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_permission_code` (`permission_code`),
    INDEX `idx_permission_module` (`module`),
    INDEX `idx_permission_category` (`category`)
);

-- Role-Permission mapping - Assign permissions to roles
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_permission_id` INT PRIMARY KEY AUTO_INCREMENT,
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    `granted` BOOLEAN DEFAULT TRUE,
    `granted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `granted_by` INT NULL, -- User ID who granted this permission
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE,
    FOREIGN KEY (`granted_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
    INDEX `idx_role_permissions_role` (`role_id`),
    INDEX `idx_role_permissions_permission` (`permission_id`)
);

-- Departments table - For organizational structure
CREATE TABLE IF NOT EXISTS `departments` (
    `department_id` INT PRIMARY KEY AUTO_INCREMENT,
    `department_code` VARCHAR(50) UNIQUE NOT NULL,
    `department_name` VARCHAR(100) NOT NULL,
    `department_description` TEXT,
    `parent_department_id` INT NULL,
    `head_user_id` INT NULL, -- Department head
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_department_id`) REFERENCES `departments`(`department_id`) ON DELETE SET NULL,
    FOREIGN KEY (`head_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_department_code` (`department_code`),
    INDEX `idx_department_parent` (`parent_department_id`)
);

-- ================================================
-- 2. MODIFY EXISTING USERS TABLE
-- ================================================

-- Add new columns to users table
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `department_id` INT NULL AFTER `role`,
ADD COLUMN IF NOT EXISTS `employee_id` VARCHAR(50) NULL AFTER `department_id`,
ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE AFTER `phone_number`,
ADD COLUMN IF NOT EXISTS `last_login` TIMESTAMP NULL AFTER `is_active`,
ADD COLUMN IF NOT EXISTS `force_password_change` BOOLEAN DEFAULT FALSE AFTER `last_login`,
ADD COLUMN IF NOT EXISTS `last_password_change` TIMESTAMP NULL AFTER `force_password_change`;

-- Add foreign key for department
ALTER TABLE `users` 
ADD CONSTRAINT `fk_users_department` 
FOREIGN KEY (`department_id`) REFERENCES `departments`(`department_id`) ON DELETE SET NULL;

-- Add indexes for better performance
ALTER TABLE `users` 
ADD INDEX IF NOT EXISTS `idx_users_department` (`department_id`),
ADD INDEX IF NOT EXISTS `idx_users_employee_id` (`employee_id`),
ADD INDEX IF NOT EXISTS `idx_users_active` (`is_active`),
ADD INDEX IF NOT EXISTS `idx_users_role` (`role`);

-- ================================================
-- 3. ENHANCE USER_PERMISSIONS TABLE
-- ================================================

-- Modify user_permissions table to work with new system
ALTER TABLE `user_permissions` 
ADD COLUMN IF NOT EXISTS `permission_id` INT NULL AFTER `user_id`,
ADD COLUMN IF NOT EXISTS `granted_by` INT NULL AFTER `granted`,
ADD COLUMN IF NOT EXISTS `expires_at` DATETIME NULL AFTER `granted_at`,
ADD COLUMN IF NOT EXISTS `reason` TEXT NULL AFTER `expires_at`;

-- Add foreign keys
ALTER TABLE `user_permissions` 
ADD CONSTRAINT `fk_user_permissions_permission` 
FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk_user_permissions_granted_by` 
FOREIGN KEY (`granted_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL;

-- Add indexes
ALTER TABLE `user_permissions` 
ADD INDEX IF NOT EXISTS `idx_user_permissions_permission_id` (`permission_id`),
ADD INDEX IF NOT EXISTS `idx_user_permissions_granted_by` (`granted_by`),
ADD INDEX IF NOT EXISTS `idx_user_permissions_expires` (`expires_at`);

-- ================================================
-- 4. CREATE VIEWS FOR EASY ACCESS
-- ================================================

-- View to get user roles with role details
CREATE OR REPLACE VIEW `v_user_roles` AS
SELECT 
    ur.user_role_id,
    ur.user_id,
    u.username,
    u.email,
    r.role_id,
    r.role_code,
    r.role_name,
    r.role_level,
    ur.department_id,
    d.department_name,
    ur.is_primary,
    ur.assigned_at,
    ur.expires_at,
    ur.is_active
FROM user_roles ur
JOIN users u ON ur.user_id = u.user_id
JOIN roles r ON ur.role_id = r.role_id
LEFT JOIN departments d ON ur.department_id = d.department_id
WHERE ur.is_active = TRUE AND u.is_active = TRUE;

-- View to get effective permissions for users
CREATE OR REPLACE VIEW `v_user_effective_permissions` AS
SELECT DISTINCT
    ur.user_id,
    u.username,
    p.permission_id,
    p.permission_code,
    p.permission_name,
    p.module,
    p.category,
    'role' as permission_source,
    r.role_code as source_detail
FROM user_roles ur
JOIN users u ON ur.user_id = u.user_id
JOIN roles r ON ur.role_id = r.role_id
JOIN role_permissions rp ON r.role_id = rp.role_id
JOIN permissions p ON rp.permission_id = p.permission_id
WHERE ur.is_active = TRUE 
  AND u.is_active = TRUE 
  AND r.is_active = TRUE
  AND rp.granted = TRUE

UNION

SELECT DISTINCT
    up.user_id,
    u.username,
    p.permission_id,
    p.permission_code,
    p.permission_name,
    p.module,
    p.category,
    'direct' as permission_source,
    'user_permission' as source_detail
FROM user_permissions up
JOIN users u ON up.user_id = u.user_id
JOIN permissions p ON up.permission_id = p.permission_id
WHERE up.granted = TRUE 
  AND u.is_active = TRUE
  AND (up.expires_at IS NULL OR up.expires_at > NOW());

-- ================================================
-- 5. CREATE AUDIT TABLES
-- ================================================

-- Role assignment audit
CREATE TABLE IF NOT EXISTS `role_assignment_audit` (
    `audit_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    `action` ENUM('assigned', 'removed', 'modified') NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `assigned_by` INT NULL,
    `reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_role_audit_user` (`user_id`),
    INDEX `idx_role_audit_role` (`role_id`),
    INDEX `idx_role_audit_date` (`created_at`)
);

-- Permission audit
CREATE TABLE IF NOT EXISTS `permission_audit` (
    `audit_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NULL,
    `role_id` INT NULL,
    `permission_id` INT NOT NULL,
    `action` ENUM('granted', 'revoked', 'modified') NOT NULL,
    `source_type` ENUM('role', 'direct') NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `granted_by` INT NULL,
    `reason` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`permission_id`) ON DELETE CASCADE,
    FOREIGN KEY (`granted_by`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_permission_audit_user` (`user_id`),
    INDEX `idx_permission_audit_role` (`role_id`),
    INDEX `idx_permission_audit_permission` (`permission_id`),
    INDEX `idx_permission_audit_date` (`created_at`)
);
