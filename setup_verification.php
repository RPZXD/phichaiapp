<?php
/**
 * Quick Setup Verification for 4-Department Role System
 * This file checks if all required files are in place and properly structured
 */

class SetupVerification {
    private $results = [];
    private $basePath;
    
    public function __construct() {
        $this->basePath = __DIR__;
        echo "🔍 Setup Verification for 4-Department Role System\n";
        echo "=================================================\n\n";
    }
    
    public function runVerification() {
        $this->checkFiles();
        $this->checkDatabaseConnection();
        $this->checkPHPSyntax();
        $this->printSummary();
    }
    
    private function checkFiles() {
        echo "📁 Checking File Structure...\n";
        echo "------------------------------\n";
        
        $requiredFiles = [
            'view/roles_new.php' => 'New Role Management Interface',
            'view/leftmenu_updated.php' => 'Updated Navigation Menu', 
            'view/switch_role.php' => 'Role Switching Interface',
            'view/js/role-management-updated.js' => 'Updated JavaScript',
            'view/api/RoleController-updated.php' => 'Updated API Controller',
            'migrate_roles.php' => 'Database Migration Script',
            'test_role_system.php' => 'System Test Suite',
            'role_management_example_updated.php' => 'Usage Examples',
            'SETUP_GUIDE.md' => 'Setup Documentation'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                echo "✅ {$description}: Found\n";
                $this->results['files']['pass'][] = $file;
            } else {
                echo "❌ {$description}: Missing ({$file})\n";
                $this->results['files']['fail'][] = $file;
            }
        }
        echo "\n";
    }
    
    private function checkDatabaseConnection() {
        echo "🗄️  Checking Database Configuration...\n";
        echo "-------------------------------------\n";
        
        $configFile = $this->basePath . '/config/Database.php';
        if (file_exists($configFile)) {
            echo "✅ Database config file: Found\n";
            
            // Try to check the database configuration without connecting
            $content = file_get_contents($configFile);
            if (strpos($content, 'class Database') !== false) {
                echo "✅ Database class: Properly structured\n";
                $this->results['database']['config'] = 'pass';
            } else {
                echo "❌ Database class: Structure issue\n";
                $this->results['database']['config'] = 'fail';
            }
        } else {
            echo "❌ Database config file: Missing\n";
            $this->results['database']['config'] = 'fail';
        }
        echo "\n";
    }
    
    private function checkPHPSyntax() {
        echo "🔧 Checking PHP Syntax...\n";
        echo "-------------------------\n";
        
        $phpFiles = [
            'view/roles_new.php',
            'view/leftmenu_updated.php', 
            'view/switch_role.php',
            'view/api/RoleController-updated.php',
            'migrate_roles.php',
            'test_role_system.php',
            'role_management_example_updated.php'
        ];
        
        foreach ($phpFiles as $file) {
            $filePath = $this->basePath . '/' . $file;
            if (file_exists($filePath)) {
                $syntaxCheck = shell_exec("php -l \"{$filePath}\" 2>&1");
                if (strpos($syntaxCheck, 'No syntax errors') !== false) {
                    echo "✅ {$file}: Valid syntax\n";
                    $this->results['syntax']['pass'][] = $file;
                } else {
                    echo "❌ {$file}: Syntax error\n";
                    echo "   Error: " . trim($syntaxCheck) . "\n";
                    $this->results['syntax']['fail'][] = $file;
                }
            }
        }
        echo "\n";
    }
    
    private function printSummary() {
        echo "📊 VERIFICATION SUMMARY\n";
        echo "=====================\n";
        
        $totalFiles = count($this->results['files']['pass'] ?? []) + count($this->results['files']['fail'] ?? []);
        $passedFiles = count($this->results['files']['pass'] ?? []);
        
        echo "📁 Files: {$passedFiles}/{$totalFiles} found\n";
        
        $totalSyntax = count($this->results['syntax']['pass'] ?? []) + count($this->results['syntax']['fail'] ?? []);
        $passedSyntax = count($this->results['syntax']['pass'] ?? []);
        
        echo "🔧 Syntax: {$passedSyntax}/{$totalSyntax} valid\n";
        echo "🗄️  Database: " . (($this->results['database']['config'] ?? 'fail') === 'pass' ? '✅ Configured' : '❌ Issue') . "\n";
        
        echo "\n";
        
        if ($passedFiles === $totalFiles && $passedSyntax === $totalSyntax && 
            ($this->results['database']['config'] ?? 'fail') === 'pass') {
            echo "🎉 ALL CHECKS PASSED!\n";
            echo "Ready to proceed with installation.\n";
            echo "\nNext Steps:\n";
            echo "1. Run: php migrate_roles.php\n";
            echo "2. Replace old files with new ones\n";
            echo "3. Test the system\n";
        } else {
            echo "⚠️  ISSUES DETECTED!\n";
            echo "Please fix the issues above before proceeding.\n";
            
            if (!empty($this->results['files']['fail'])) {
                echo "\nMissing files:\n";
                foreach ($this->results['files']['fail'] as $file) {
                    echo "- {$file}\n";
                }
            }
            
            if (!empty($this->results['syntax']['fail'])) {
                echo "\nSyntax errors in:\n";
                foreach ($this->results['syntax']['fail'] as $file) {
                    echo "- {$file}\n";
                }
            }
        }
        
        echo "\n" . str_repeat("=", 50) . "\n";
    }
    
    public function showFileStructure() {
        echo "📋 EXPECTED FILE STRUCTURE\n";
        echo "==========================\n";
        echo "phichaiapp/\n";
        echo "├── config/\n";
        echo "│   └── Database.php\n";
        echo "├── view/\n";
        echo "│   ├── roles_new.php           # New role management UI\n";
        echo "│   ├── leftmenu_updated.php    # Updated navigation\n";
        echo "│   ├── switch_role.php         # Role switching page\n";
        echo "│   ├── js/\n";
        echo "│   │   └── role-management-updated.js\n";
        echo "│   └── api/\n";
        echo "│       └── RoleController-updated.php\n";
        echo "├── migrate_roles.php           # Database migration\n";
        echo "├── test_role_system.php        # System tests\n";
        echo "├── role_management_example_updated.php\n";
        echo "└── SETUP_GUIDE.md              # Documentation\n";
        echo "\n";
    }
    
    public function showQuickStart() {
        echo "🚀 QUICK START GUIDE\n";
        echo "===================\n";
        echo "1. Verify setup: php setup_verification.php\n";
        echo "2. Run migration: php migrate_roles.php\n";
        echo "3. Test system: php test_role_system.php\n";
        echo "4. Replace files:\n";
        echo "   - cp view/roles_new.php view/roles.php\n";
        echo "   - cp view/leftmenu_updated.php view/leftmenu.php\n";
        echo "   - cp view/api/RoleController-updated.php view/api/RoleController.php\n";
        echo "   - cp view/js/role-management-updated.js view/js/role-management.js\n";
        echo "5. Access: http://localhost/phichaiapp/view/roles.php\n";
        echo "\n";
    }
}

// Demo data generator
function generateDemoData() {
    echo "🎯 DEMO DATA OVERVIEW\n";
    echo "====================\n";
    
    $departments = [
        'academic' => 'ฝ่ายวิชาการ',
        'budget' => 'ฝ่ายงบประมาณ',
        'personnel' => 'ฝ่ายบุคคล',
        'general' => 'ฝ่ายงานทั่วไป'
    ];
    
    $roles = [
        'admin' => 'ผู้ดูแลระบบ (ระดับ 5)',
        'head' => 'หัวหน้าฝ่าย (ระดับ 4)',
        'officer' => 'เจ้าหน้าที่ (ระดับ 3)',
        'teacher' => 'ครู (ระดับ 2)',
        'viewer' => 'ผู้ดูข้อมูล (ระดับ 1)'
    ];
    
    echo "🏢 Departments:\n";
    foreach ($departments as $key => $name) {
        echo "   {$key}: {$name}\n";
    }
    
    echo "\n👤 Roles:\n";
    foreach ($roles as $key => $name) {
        echo "   {$key}: {$name}\n";
    }
    
    echo "\n📋 Sample Users:\n";
    echo "   1001: Admin User (All departments as admin)\n";
    echo "   1002: Academic Head (Academic department head)\n";
    echo "   1003: Multi-Role User (Teacher in academic, Viewer in budget)\n";
    echo "   1004: Budget Officer (Budget department officer)\n";
    echo "   1005: Personnel Head (Personnel head, General viewer)\n";
    
    echo "\n";
}

// Run verification if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $verifier = new SetupVerification();
    
    $action = $argv[1] ?? 'verify';
    
    switch ($action) {
        case 'structure':
            $verifier->showFileStructure();
            break;
        case 'quickstart':
            $verifier->showQuickStart();
            break;
        case 'demo':
            generateDemoData();
            break;
        case 'verify':
        default:
            $verifier->runVerification();
            break;
    }
}
?>
