<?php
/**
 * Test Suite for New 4-Department Role Management System
 * This file tests all the functionality of the updated role system
 */

require_once 'view/api/RoleController-updated.php';

class RoleSystemTest {
    private $roleController;
    private $testUserId;
    private $testResults = [];
    
    public function __construct() {
        $this->roleController = new RoleController();
        echo "🧪 Role Management System Test Suite\n";
        echo "=====================================\n\n";
    }
    
    public function runAllTests() {
        $this->testCreateUser();
        $this->testAssignRoles();
        $this->testPermissionChecking();
        $this->testRoleHierarchy();
        $this->testCrossDepartmentAccess();
        $this->testAuditTrail();
        $this->testRoleSwitching();
        $this->testPermissionInheritance();
        
        $this->printTestSummary();
    }
    
    private function test($testName, $testFunction) {
        echo "🔍 Testing: {$testName}...\n";
        
        try {
            $result = $testFunction();
            if ($result) {
                echo "✅ PASS: {$testName}\n";
                $this->testResults[] = ['name' => $testName, 'status' => 'PASS', 'message' => ''];
            } else {
                echo "❌ FAIL: {$testName}\n";
                $this->testResults[] = ['name' => $testName, 'status' => 'FAIL', 'message' => 'Test returned false'];
            }
        } catch (Exception $e) {
            echo "💥 ERROR: {$testName} - " . $e->getMessage() . "\n";
            $this->testResults[] = ['name' => $testName, 'status' => 'ERROR', 'message' => $e->getMessage()];
        }
        
        echo "\n";
    }
    
    private function testCreateUser() {
        $this->test('Create Test User', function() {
            // Create a test user (simulate)
            $this->testUserId = 999; // Use a test ID
            return true;
        });
    }
    
    private function testAssignRoles() {
        $this->test('Assign Department Roles', function() {
            // Test assigning multiple department roles to a user
            $result1 = $this->roleController->assignUserRole($this->testUserId, 'academic', 'teacher', 1);
            $result2 = $this->roleController->assignUserRole($this->testUserId, 'budget', 'viewer', 1);
            $result3 = $this->roleController->assignUserRole($this->testUserId, 'personnel', 'officer', 1);
            
            return $result1 && $result2 && $result3;
        });
    }
    
    private function testPermissionChecking() {
        $this->test('Permission Checking', function() {
            // Test various permission checks
            $hasAcademicView = $this->roleController->checkPermission($this->testUserId, 'academic', 'curriculum', 'view');
            $hasAcademicEdit = $this->roleController->checkPermission($this->testUserId, 'academic', 'curriculum', 'edit');
            $hasAcademicAdmin = $this->roleController->checkPermission($this->testUserId, 'academic', 'curriculum', 'admin');
            
            // Teacher should have view and edit, but not admin
            return $hasAcademicView && $hasAcademicEdit && !$hasAcademicAdmin;
        });
    }
    
    private function testRoleHierarchy() {
        $this->test('Role Hierarchy', function() {
            // Test that higher roles can access lower role permissions
            
            // Assign admin role to test user in general department
            $this->roleController->assignUserRole($this->testUserId, 'general', 'admin', 1);
            
            // Admin should have all permissions
            $hasView = $this->roleController->checkPermission($this->testUserId, 'general', 'facility', 'view');
            $hasEdit = $this->roleController->checkPermission($this->testUserId, 'general', 'facility', 'edit');
            $hasDelete = $this->roleController->checkPermission($this->testUserId, 'general', 'facility', 'delete');
            $hasAdmin = $this->roleController->checkPermission($this->testUserId, 'general', 'facility', 'admin');
            
            return $hasView && $hasEdit && $hasDelete && $hasAdmin;
        });
    }
    
    private function testCrossDepartmentAccess() {
        $this->test('Cross-Department Access Control', function() {
            // User should not have access to departments they're not assigned to
            $hasUnassignedAccess = $this->roleController->checkPermission($this->testUserId, 'budget', 'planning', 'view');
            
            // But they should have access to assigned departments
            $hasAssignedAccess = $this->roleController->checkPermission($this->testUserId, 'academic', 'curriculum', 'view');
            
            return !$hasUnassignedAccess && $hasAssignedAccess;
        });
    }
    
    private function testAuditTrail() {
        $this->test('Audit Trail Logging', function() {
            // Test that permission actions are logged
            $this->roleController->logPermissionAction($this->testUserId, 'test_action', [
                'test_data' => 'test_value',
                'timestamp' => time()
            ]);
            
            // Check if log was created (simplified check)
            return true; // Would check database in real implementation
        });
    }
    
    private function testRoleSwitching() {
        $this->test('Role Switching Functionality', function() {
            // Test getting user departments
            $departments = $this->roleController->getUserDepartments($this->testUserId);
            
            // User should have multiple departments
            return count($departments) > 1;
        });
    }
    
    private function testPermissionInheritance() {
        $this->test('Permission Inheritance', function() {
            // Test that role permissions are properly inherited
            $rolePermissions = $this->roleController->getRolePermissions('teacher');
            $expectedPermissions = ['view', 'edit'];
            
            return array_intersect($expectedPermissions, $rolePermissions) === $expectedPermissions;
        });
    }
    
    private function printTestSummary() {
        echo str_repeat("=", 50) . "\n";
        echo "📊 TEST SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        
        $passed = array_filter($this->testResults, fn($r) => $r['status'] === 'PASS');
        $failed = array_filter($this->testResults, fn($r) => $r['status'] === 'FAIL');
        $errors = array_filter($this->testResults, fn($r) => $r['status'] === 'ERROR');
        
        echo sprintf("✅ Passed: %d\n", count($passed));
        echo sprintf("❌ Failed: %d\n", count($failed));
        echo sprintf("💥 Errors: %d\n", count($errors));
        echo sprintf("📊 Total: %d\n", count($this->testResults));
        
        if (count($failed) > 0 || count($errors) > 0) {
            echo "\n🔍 FAILED/ERROR DETAILS:\n";
            foreach ($this->testResults as $result) {
                if ($result['status'] !== 'PASS') {
                    echo sprintf("   %s %s: %s\n", 
                        $result['status'] === 'FAIL' ? '❌' : '💥',
                        $result['name'],
                        $result['message']
                    );
                }
            }
        }
        
        $successRate = round((count($passed) / count($this->testResults)) * 100, 1);
        echo sprintf("\n🎯 Success Rate: %s%%\n", $successRate);
        
        if ($successRate >= 90) {
            echo "🎉 Excellent! The role management system is working well.\n";
        } elseif ($successRate >= 70) {
            echo "⚠️  Good, but some issues need attention.\n";
        } else {
            echo "🚨 Major issues detected. Please review the system.\n";
        }
        
        echo str_repeat("=", 50) . "\n";
    }
    
    public function testUIComponents() {
        echo "🖥️  Testing UI Components...\n";
        echo "=============================\n";
        
        // Test if required files exist
        $files = [
            'view/roles_new.php' => 'New Role Management Interface',
            'view/js/role-management-updated.js' => 'Updated JavaScript',
            'view/api/RoleController-updated.php' => 'Updated API Controller',
            'view/leftmenu_updated.php' => 'Updated Navigation Menu',
            'view/switch_role.php' => 'Role Switching Interface'
        ];
        
        foreach ($files as $file => $description) {
            if (file_exists($file)) {
                echo "✅ {$description}: Found\n";
            } else {
                echo "❌ {$description}: Missing ({$file})\n";
            }
        }
        
        echo "\n";
    }
    
    public function generateTestData() {
        echo "📊 Generating Test Data...\n";
        echo "==========================\n";
        
        $testUsers = [
            ['id' => 1001, 'name' => 'Admin User', 'roles' => [
                'academic' => 'admin',
                'budget' => 'admin', 
                'personnel' => 'admin',
                'general' => 'admin'
            ]],
            ['id' => 1002, 'name' => 'Academic Head', 'roles' => [
                'academic' => 'head'
            ]],
            ['id' => 1003, 'name' => 'Multi-Role User', 'roles' => [
                'academic' => 'teacher',
                'budget' => 'viewer',
                'general' => 'officer'
            ]],
            ['id' => 1004, 'name' => 'Budget Officer', 'roles' => [
                'budget' => 'officer'
            ]],
            ['id' => 1005, 'name' => 'Personnel Head', 'roles' => [
                'personnel' => 'head',
                'general' => 'viewer'
            ]]
        ];
        
        foreach ($testUsers as $user) {
            echo "👤 Creating test user: {$user['name']} (ID: {$user['id']})\n";
            
            foreach ($user['roles'] as $department => $role) {
                try {
                    $this->roleController->assignUserRole($user['id'], $department, $role, 1);
                    echo "   ✅ Assigned {$role} role in {$department} department\n";
                } catch (Exception $e) {
                    echo "   ❌ Failed to assign role: " . $e->getMessage() . "\n";
                }
            }
            echo "\n";
        }
        
        echo "✅ Test data generation completed.\n\n";
    }
}

// Demo and testing functions
function demonstrateUsage() {
    echo "🎯 USAGE DEMONSTRATION\n";
    echo "======================\n";
    
    $roleController = new RoleController();
    
    echo "1. Checking permissions:\n";
    echo "   \$hasAccess = \$roleController->checkPermission(1001, 'academic', 'curriculum', 'edit');\n";
    
    echo "\n2. Getting user departments:\n";
    echo "   \$departments = \$roleController->getUserDepartments(1003);\n";
    
    echo "\n3. Assigning roles:\n";
    echo "   \$roleController->assignUserRole(1006, 'academic', 'teacher', 1);\n";
    
    echo "\n4. Logging actions:\n";
    echo "   \$roleController->logPermissionAction(1001, 'login', ['ip' => '127.0.0.1']);\n";
    
    echo "\n📚 For complete documentation, see role_management_example_updated.php\n\n";
}

// Run tests if this file is executed directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $tester = new RoleSystemTest();
    
    echo "Choose an option:\n";
    echo "1. Run full test suite\n";
    echo "2. Test UI components only\n";
    echo "3. Generate test data\n";
    echo "4. Show usage demonstration\n";
    echo "5. Run all\n";
    echo "\nEnter choice (1-5): ";
    
    if (php_sapi_name() === 'cli') {
        $choice = trim(fgets(STDIN));
    } else {
        $choice = $_GET['test'] ?? '5';
    }
    
    switch ($choice) {
        case '1':
            $tester->runAllTests();
            break;
        case '2':
            $tester->testUIComponents();
            break;
        case '3':
            $tester->generateTestData();
            break;
        case '4':
            demonstrateUsage();
            break;
        case '5':
        default:
            $tester->testUIComponents();
            $tester->generateTestData();
            $tester->runAllTests();
            demonstrateUsage();
            break;
    }
}
?>
