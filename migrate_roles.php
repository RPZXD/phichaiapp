<?php
/**
 * Database Migration Script for New 4-Department Role System
 * This script migrates from the old role system to the new department-based system
 */

require_once 'config/Database.php'; // Use the project's database configuration

class RoleMigration {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function migrate() {
        echo "🚀 Starting migration to new 4-department role system...\n\n";
        
        try {
            $this->pdo->beginTransaction();
            
            // Step 1: Create new tables
            $this->createNewTables();
            
            // Step 2: Migrate existing user data
            $this->migrateExistingUsers();
            
            // Step 3: Set up default department modules
            $this->setupDepartmentModules();
            
            // Step 4: Create default role definitions
            $this->createRoleDefinitions();
            
            // Step 5: Backup old tables (rename them)
            $this->backupOldTables();
            
            $this->pdo->commit();
            echo "✅ Migration completed successfully!\n";
            
            $this->printSummary();
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function createNewTables() {
        echo "📋 Creating new database tables...\n";
        
        // User roles table - maps users to departments and roles
        $sql = "CREATE TABLE IF NOT EXISTS user_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            department ENUM('academic', 'budget', 'personnel', 'general') NOT NULL,
            role ENUM('admin', 'head', 'officer', 'teacher', 'viewer') NOT NULL,
            granted_by INT,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            notes TEXT,
            INDEX idx_user_dept (user_id, department),
            INDEX idx_dept_role (department, role),
            UNIQUE KEY unique_user_dept (user_id, department)
        )";
        $this->pdo->exec($sql);
        
        // User permissions table - granular permissions
        $sql = "CREATE TABLE IF NOT EXISTS user_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            department ENUM('academic', 'budget', 'personnel', 'general') NOT NULL,
            module VARCHAR(50) NOT NULL,
            permission_type ENUM('view', 'edit', 'delete', 'admin') NOT NULL,
            granted_by INT,
            granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            INDEX idx_user_perm (user_id, department, module),
            UNIQUE KEY unique_user_permission (user_id, department, module, permission_type)
        )";
        $this->pdo->exec($sql);
        
        // Permission logs table - audit trail
        $sql = "CREATE TABLE IF NOT EXISTS permission_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            target_user_id INT,
            department VARCHAR(20),
            old_role VARCHAR(20),
            new_role VARCHAR(20),
            details JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_action (user_id, action),
            INDEX idx_created_at (created_at)
        )";
        $this->pdo->exec($sql);
        
        // Department modules table - defines available modules per department
        $sql = "CREATE TABLE IF NOT EXISTS department_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department ENUM('academic', 'budget', 'personnel', 'general') NOT NULL,
            module_key VARCHAR(50) NOT NULL,
            module_name VARCHAR(100) NOT NULL,
            description TEXT,
            min_role_level INT DEFAULT 1,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            UNIQUE KEY unique_dept_module (department, module_key)
        )";
        $this->pdo->exec($sql);
        
        // Role definitions table - defines what each role can do
        $sql = "CREATE TABLE IF NOT EXISTS role_definitions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role ENUM('admin', 'head', 'officer', 'teacher', 'viewer') NOT NULL,
            level INT NOT NULL,
            permissions JSON NOT NULL,
            description TEXT,
            UNIQUE KEY unique_role (role)
        )";
        $this->pdo->exec($sql);
        
        echo "✅ New tables created successfully.\n\n";
    }
    
    private function migrateExistingUsers() {
        echo "👥 Migrating existing user data...\n";
        
        // Check if old users table exists
        $result = $this->pdo->query("SHOW TABLES LIKE 'users'");
        if ($result->rowCount() == 0) {
            echo "⚠️  No existing users table found. Skipping user migration.\n\n";
            return;
        }
        
        // Get existing users with their roles
        $stmt = $this->pdo->query("SELECT id, username, role FROM users WHERE role IS NOT NULL");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migratedCount = 0;
        
        foreach ($users as $user) {
            $userId = $user['id'];
            $oldRole = strtolower($user['role']);
            
            // Map old roles to new department structure
            $mappings = $this->getOldRoleMappings($oldRole);
            
            foreach ($mappings as $mapping) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_roles (user_id, department, role, granted_by, notes) 
                    VALUES (?, ?, ?, 1, ?)
                    ON DUPLICATE KEY UPDATE 
                    role = VALUES(role), 
                    notes = CONCAT(IFNULL(notes, ''), '; Migrated from old role: " . $oldRole . "')
                ");
                
                $stmt->execute([
                    $userId,
                    $mapping['department'],
                    $mapping['role'],
                    "Migrated from old role: {$oldRole}"
                ]);
                
                // Grant default permissions for this role
                $this->grantDefaultPermissions($userId, $mapping['department'], $mapping['role']);
            }
            
            $migratedCount++;
        }
        
        echo "✅ Migrated {$migratedCount} users successfully.\n\n";
    }
    
    private function getOldRoleMappings($oldRole) {
        $mappings = [
            'admin' => [
                ['department' => 'academic', 'role' => 'admin'],
                ['department' => 'budget', 'role' => 'admin'],
                ['department' => 'personnel', 'role' => 'admin'],
                ['department' => 'general', 'role' => 'admin']
            ],
            'principal' => [
                ['department' => 'academic', 'role' => 'head'],
                ['department' => 'budget', 'role' => 'head'],
                ['department' => 'personnel', 'role' => 'head'],
                ['department' => 'general', 'role' => 'head']
            ],
            'head_teacher' => [
                ['department' => 'academic', 'role' => 'head']
            ],
            'teacher' => [
                ['department' => 'academic', 'role' => 'teacher']
            ],
            'officer' => [
                ['department' => 'general', 'role' => 'officer']
            ],
            'staff' => [
                ['department' => 'general', 'role' => 'viewer']
            ]
        ];
        
        return $mappings[$oldRole] ?? [['department' => 'general', 'role' => 'viewer']];
    }
    
    private function grantDefaultPermissions($userId, $department, $role) {
        $permissions = $this->getDefaultPermissions($role);
        
        foreach ($permissions as $permission) {
            // Get modules for this department
            $stmt = $this->pdo->prepare("SELECT module_key FROM department_modules WHERE department = ?");
            $stmt->execute([$department]);
            $modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($modules as $module) {
                $stmt = $this->pdo->prepare("
                    INSERT IGNORE INTO user_permissions (user_id, department, module, permission_type, granted_by)
                    VALUES (?, ?, ?, ?, 1)
                ");
                $stmt->execute([$userId, $department, $module, $permission]);
            }
        }
    }
    
    private function getDefaultPermissions($role) {
        $permissions = [
            'admin' => ['view', 'edit', 'delete', 'admin'],
            'head' => ['view', 'edit', 'delete'],
            'officer' => ['view', 'edit'],
            'teacher' => ['view', 'edit'],
            'viewer' => ['view']
        ];
        
        return $permissions[$role] ?? ['view'];
    }
    
    private function setupDepartmentModules() {
        echo "🏢 Setting up department modules...\n";
        
        $modules = [
            'academic' => [
                ['key' => 'curriculum', 'name' => 'หลักสูตร', 'min_level' => 2],
                ['key' => 'schedule', 'name' => 'ตารางเรียน', 'min_level' => 2],
                ['key' => 'assessment', 'name' => 'การประเมิน', 'min_level' => 2],
                ['key' => 'student_records', 'name' => 'ระเบียนนักเรียน', 'min_level' => 3],
                ['key' => 'examination', 'name' => 'การสอบ', 'min_level' => 2],
                ['key' => 'research', 'name' => 'วิจัยและพัฒนา', 'min_level' => 3]
            ],
            'budget' => [
                ['key' => 'planning', 'name' => 'วางแผนงบประมาณ', 'min_level' => 3],
                ['key' => 'approval', 'name' => 'อนุมัติงบประมาณ', 'min_level' => 4],
                ['key' => 'tracking', 'name' => 'ติดตามการใช้งบ', 'min_level' => 2],
                ['key' => 'procurement', 'name' => 'จัดซื้อจัดจ้าง', 'min_level' => 3],
                ['key' => 'audit', 'name' => 'ตรวจสอบการเงิน', 'min_level' => 4],
                ['key' => 'reporting', 'name' => 'รายงานทางการเงิน', 'min_level' => 2]
            ],
            'personnel' => [
                ['key' => 'recruitment', 'name' => 'สรรหาบุคลากร', 'min_level' => 4],
                ['key' => 'development', 'name' => 'พัฒนาบุคลากร', 'min_level' => 3],
                ['key' => 'evaluation', 'name' => 'ประเมินผลการปฏิบัติงาน', 'min_level' => 3],
                ['key' => 'leave', 'name' => 'การลาและขาดงาน', 'min_level' => 2],
                ['key' => 'welfare', 'name' => 'สวัสดิการ', 'min_level' => 2],
                ['key' => 'payroll', 'name' => 'เงินเดือนและค่าตอบแทน', 'min_level' => 4]
            ],
            'general' => [
                ['key' => 'facility', 'name' => 'จัดการสิ่งอำนวยความสะดวก', 'min_level' => 2],
                ['key' => 'maintenance', 'name' => 'ซ่อมบำรุง', 'min_level' => 2],
                ['key' => 'security', 'name' => 'รักษาความปลอดภัย', 'min_level' => 2],
                ['key' => 'communication', 'name' => 'ประชาสัมพันธ์', 'min_level' => 2],
                ['key' => 'events', 'name' => 'จัดกิจกรรม', 'min_level' => 2],
                ['key' => 'archive', 'name' => 'จัดเก็บเอกสาร', 'min_level' => 3]
            ]
        ];
        
        $insertedCount = 0;
        foreach ($modules as $department => $moduleList) {
            foreach ($moduleList as $index => $module) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO department_modules 
                    (department, module_key, module_name, min_role_level, sort_order)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    module_name = VALUES(module_name),
                    min_role_level = VALUES(min_role_level)
                ");
                
                $stmt->execute([
                    $department,
                    $module['key'],
                    $module['name'],
                    $module['min_level'],
                    $index + 1
                ]);
                
                $insertedCount++;
            }
        }
        
        echo "✅ Setup {$insertedCount} department modules.\n\n";
    }
    
    private function createRoleDefinitions() {
        echo "👑 Creating role definitions...\n";
        
        $roles = [
            'admin' => [
                'level' => 5,
                'permissions' => ['view', 'edit', 'delete', 'admin'],
                'description' => 'ผู้ดูแลระบบ - มีสิทธิ์เต็มในทุกส่วน'
            ],
            'head' => [
                'level' => 4,
                'permissions' => ['view', 'edit', 'delete'],
                'description' => 'หัวหน้าฝ่าย - จัดการข้อมูลในฝ่ายงาน'
            ],
            'officer' => [
                'level' => 3,
                'permissions' => ['view', 'edit'],
                'description' => 'เจ้าหน้าที่ - ดูและแก้ไขข้อมูล'
            ],
            'teacher' => [
                'level' => 2,
                'permissions' => ['view', 'edit'],
                'description' => 'ครู - ดูและแก้ไขข้อมูลที่เกี่ยวข้อง'
            ],
            'viewer' => [
                'level' => 1,
                'permissions' => ['view'],
                'description' => 'ผู้ดูข้อมูล - ดูข้อมูลเท่านั้น'
            ]
        ];
        
        foreach ($roles as $roleKey => $roleData) {
            $stmt = $this->pdo->prepare("
                INSERT INTO role_definitions (role, level, permissions, description)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                level = VALUES(level),
                permissions = VALUES(permissions),
                description = VALUES(description)
            ");
            
            $stmt->execute([
                $roleKey,
                $roleData['level'],
                json_encode($roleData['permissions']),
                $roleData['description']
            ]);
        }
        
        echo "✅ Role definitions created successfully.\n\n";
    }
    
    private function backupOldTables() {
        echo "💾 Backing up old tables...\n";
        
        $tablesToBackup = ['users', 'permissions', 'roles'];
        $backupSuffix = '_backup_' . date('Ymd_His');
        
        foreach ($tablesToBackup as $table) {
            $result = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($result->rowCount() > 0) {
                $newName = $table . $backupSuffix;
                $this->pdo->exec("RENAME TABLE {$table} TO {$newName}");
                echo "📦 Backed up {$table} as {$newName}\n";
            }
        }
        
        echo "✅ Old tables backed up successfully.\n\n";
    }
    
    private function printSummary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎉 MIGRATION SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        
        // Count users by department and role
        $stmt = $this->pdo->query("
            SELECT department, role, COUNT(*) as count 
            FROM user_roles 
            WHERE is_active = 1 
            GROUP BY department, role 
            ORDER BY department, role
        ");
        $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 User Distribution:\n";
        foreach ($roleCounts as $count) {
            echo sprintf("   %s - %s: %d users\n", 
                ucfirst($count['department']), 
                ucfirst($count['role']), 
                $count['count']
            );
        }
        
        // Count total permissions
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM user_permissions WHERE is_active = 1");
        $permissionCount = $stmt->fetchColumn();
        echo "\n🔐 Total Active Permissions: {$permissionCount}\n";
        
        // Count modules
        $stmt = $this->pdo->query("SELECT department, COUNT(*) as count FROM department_modules GROUP BY department");
        $moduleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\n📋 Department Modules:\n";
        foreach ($moduleCounts as $count) {
            echo sprintf("   %s: %d modules\n", ucfirst($count['department']), $count['count']);
        }
        
        echo "\n✨ Migration completed successfully!\n";
        echo "📝 Next steps:\n";
        echo "   1. Test the new role management system\n";
        echo "   2. Update your application to use the new APIs\n";
        echo "   3. Train users on the new department-based roles\n";
        echo "   4. Remove backup tables when confident everything works\n";
        echo "\n" . str_repeat("=", 60) . "\n";
    }
}

// Execute migration if run directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    echo "🔄 Database Migration for 4-Department Role System\n";
    echo "=================================================\n\n";
    
    // Check if running from command line
    if (php_sapi_name() !== 'cli') {
        echo "⚠️  This script should be run from the command line for safety.\n";
        echo "Usage: php migrate_roles.php\n";
        exit(1);
    }
      try {
        // Use the project's database configuration
        require_once 'config/Database.php';
        
        // Create database connection using the project's configuration
        $database = new App\Database();
        $pdo = $database->getConnection();
        
        $migration = new RoleMigration($pdo);
        $migration->migrate();
        
    } catch (Exception $e) {
        echo "💥 Error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
        exit(1);
    }
}
?>
