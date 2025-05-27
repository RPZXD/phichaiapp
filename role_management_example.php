<?php
/**
 * ตัวอย่างการใช้งานระบบ Role Management
 * Author: ICT Team @Phichai School
 * Date: May 27, 2025
 */

require_once('config/Database.php');
require_once('view/api/RoleController.php');

// ตัวอย่างการใช้งาน Role Management System
class RoleManagerExample {
    private $roleController;
    
    public function __construct() {
        $this->roleController = new RoleController();
    }

    /**
     * ตัวอย่างการกำหนดบทบาทให้ผู้ใช้
     */
    public function assignUserRoles() {
        echo "<h2>ตัวอย่างการกำหนดบทบาท</h2>\n";
        
        // กำหนดให้ user ID 1 เป็นหัวหน้าฝ่ายวิชาการ
        $this->setUserRole(1, 'academic', 'head');
        
        // กำหนดให้ user ID 1 เป็นครูในฝ่ายงานทั่วไป
        $this->setUserRole(1, 'general', 'teacher');
        
        // กำหนดให้ user ID 2 เป็นเจ้าหน้าที่ฝ่ายงบประมาณ
        $this->setUserRole(2, 'budget', 'officer');
        
        // กำหนดให้ user ID 3 เป็นผู้ชมฝ่ายบุคคล
        $this->setUserRole(3, 'personnel', 'viewer');
    }

    /**
     * ตัวอย่างการตรวจสอบสิทธิ์
     */
    public function checkPermissionExamples() {
        echo "<h2>ตัวอย่างการตรวจสอบสิทธิ์</h2>\n";
        
        $userId = 1;
        
        // ตรวจสอบสิทธิ์ในฝ่ายวิชาการ (เป็นหัวหน้าฝ่าย)
        $canView = $this->checkPermission($userId, 'academic', 'curriculum', 'view');
        $canCreate = $this->checkPermission($userId, 'academic', 'curriculum', 'create');
        $canEdit = $this->checkPermission($userId, 'academic', 'curriculum', 'edit');
        $canDelete = $this->checkPermission($userId, 'academic', 'curriculum', 'delete');
        
        echo "User $userId ในฝ่ายวิชาการ (หัวหน้าฝ่าย):\n";
        echo "- ดูข้อมูลหลักสูตร: " . ($canView ? "ได้" : "ไม่ได้") . "\n";
        echo "- สร้างหลักสูตร: " . ($canCreate ? "ได้" : "ไม่ได้") . "\n";
        echo "- แก้ไขหลักสูตร: " . ($canEdit ? "ได้" : "ไม่ได้") . "\n";
        echo "- ลบหลักสูตร: " . ($canDelete ? "ได้" : "ไม่ได้") . "\n\n";
        
        // ตรวจสอบสิทธิ์ในฝ่ายทั่วไป (เป็นครู)
        $canViewGeneral = $this->checkPermission($userId, 'general', 'facility', 'view');
        $canCreateGeneral = $this->checkPermission($userId, 'general', 'facility', 'create');
        
        echo "User $userId ในฝ่ายทั่วไป (ครู):\n";
        echo "- ดูข้อมูลสิ่งอำนวยความสะดวก: " . ($canViewGeneral ? "ได้" : "ไม่ได้") . "\n";
        echo "- สร้างข้อมูลสิ่งอำนวยความสะดวก: " . ($canCreateGeneral ? "ได้" : "ไม่ได้") . "\n\n";
    }

    /**
     * ตัวอย่างการใช้งานใน Controller หรือ View
     */
    public function practicalUsageExamples() {
        echo "<h2>ตัวอย่างการใช้งานจริง</h2>\n";
        
        echo "<!-- ตัวอย่างใน PHP Controller -->\n";
        echo "<?php\n";
        echo "// ตรวจสอบสิทธิ์ก่อนแสดงหน้า\n";
        echo "if (!RoleManager::checkPermission(\$_SESSION['user']['user_id'], 'academic', 'curriculum', 'view')) {\n";
        echo "    header('Location: access_denied.php');\n";
        echo "    exit();\n";
        echo "}\n";
        echo "?>\n\n";
        
        echo "<!-- ตัวอย่างใน HTML/PHP View -->\n";
        echo "<?php if (RoleManager::checkPermission(\$_SESSION['user']['user_id'], 'academic', 'curriculum', 'create')): ?>\n";
        echo "    <button class='btn btn-primary'>เพิ่มหลักสูตรใหม่</button>\n";
        echo "<?php endif; ?>\n\n";
        
        echo "<?php if (RoleManager::checkRole(\$_SESSION['user']['user_id'], 'academic', 'head')): ?>\n";
        echo "    <div class='admin-panel'>แผงควบคุมหัวหน้าฝ่าย</div>\n";
        echo "<?php endif; ?>\n\n";
        
        echo "<!-- ตัวอย่างใน JavaScript -->\n";
        echo "<script>\n";
        echo "// ตรวจสอบสิทธิ์แบบ Ajax\n";
        echo "async function checkUserPermission(department, module, action) {\n";
        echo "    const hasPermission = await RoleManager.checkPermission(\n";
        echo "        userId, department, module, action\n";
        echo "    );\n";
        echo "    \n";
        echo "    if (hasPermission) {\n";
        echo "        // แสดงปุ่มหรือฟังก์ชัน\n";
        echo "    } else {\n";
        echo "        // ซ่อนหรือปิดการใช้งาน\n";
        echo "    }\n";
        echo "}\n";
        echo "</script>\n\n";
    }

    /**
     * ตัวอย่างการจัดการข้อมูลผ่าน API
     */
    public function apiUsageExamples() {
        echo "<h2>ตัวอย่างการใช้งาน API</h2>\n";
        
        echo "// 1. ดึงสิทธิ์ของผู้ใช้\n";
        echo "GET api/RoleController.php?action=getUserPermissions&user_id=1\n\n";
        
        echo "// 2. กำหนดบทบาท\n";
        echo "POST api/RoleController.php\n";
        echo "{\n";
        echo "    \"action\": \"setUserRole\",\n";
        echo "    \"user_id\": 1,\n";
        echo "    \"department\": \"academic\",\n";
        echo "    \"role\": \"head\"\n";
        echo "}\n\n";
        
        echo "// 3. ตรวจสอบสิทธิ์\n";
        echo "GET api/RoleController.php?action=checkPermission&user_id=1&department=academic&module=curriculum&action=view\n\n";
        
        echo "// 4. กำหนดบทบาทหลายคนพร้อมกัน\n";
        echo "POST api/RoleController.php\n";
        echo "{\n";
        echo "    \"action\": \"bulkAssignRoles\",\n";
        echo "    \"assignments\": [\n";
        echo "        {\"user_id\": 1, \"department\": \"academic\", \"role\": \"head\"},\n";
        echo "        {\"user_id\": 2, \"department\": \"budget\", \"role\": \"officer\"},\n";
        echo "        {\"user_id\": 3, \"department\": \"personnel\", \"role\": \"viewer\"}\n";
        echo "    ]\n";
        echo "}\n\n";
    }

    /**
     * ตัวอย่างการขยายระบบ
     */
    public function extensionExamples() {
        echo "<h2>ตัวอย่างการขยายระบบ</h2>\n";
        
        echo "// เพิ่มฝ่ายงานใหม่\n";
        echo "// แก้ไขใน RoleController.php, property \$departments\n";
        echo "\$this->departments['it'] = [\n";
        echo "    'name' => 'ฝ่ายเทคโนโลยีสารสนเทศ',\n";
        echo "    'modules' => ['system', 'network', 'support', 'development']\n";
        echo "];\n\n";
        
        echo "// เพิ่มบทบาทใหม่\n";
        echo "\$this->roles['specialist'] = ['name' => 'ผู้เชี่ยวชาญ', 'level' => 3.5];\n\n";
        
        echo "// เพิ่มสิทธิ์ใหม่\n";
        echo "\$this->permissions[] = 'approve';\n";
        echo "\$this->rolePermissions['head']['approve'] = true;\n\n";
        
        echo "// สร้าง Custom Permission Check\n";
        echo "function checkCustomPermission(\$userId, \$department, \$customCondition) {\n";
        echo "    \$userRole = \$this->getUserRole(\$userId, \$department);\n";
        echo "    \$roleLevel = \$this->roles[\$userRole]['level'] ?? 0;\n";
        echo "    \n";
        echo "    // เงื่อนไขเพิ่มเติม\n";
        echo "    return \$roleLevel >= 3 && \$customCondition;\n";
        echo "}\n\n";
    }

    // Helper methods for demonstration
    private function setUserRole($userId, $department, $role) {
        // จำลองการกำหนดบทบาท
        echo "✓ กำหนด User $userId เป็น $role ในฝ่าย $department\n";
    }

    private function checkPermission($userId, $department, $module, $action) {
        // จำลองการตรวจสอบสิทธิ์
        $roles = [
            1 => ['academic' => 'head', 'general' => 'teacher'],
            2 => ['budget' => 'officer'],
            3 => ['personnel' => 'viewer']
        ];
        
        $rolePermissions = [
            'head' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
            'officer' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
            'teacher' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
            'viewer' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false]
        ];
        
        $userRole = $roles[$userId][$department] ?? null;
        if (!$userRole) return false;
        
        return $rolePermissions[$userRole][$action] ?? false;
    }
}

// รันตัวอย่าง
if (isset($_GET['run_example'])) {
    $example = new RoleManagerExample();
    
    echo "<pre>";
    echo "<h1>ตัวอย่างการใช้งานระบบ Role Management</h1>\n";
    echo "============================================\n\n";
    
    $example->assignUserRoles();
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    $example->checkPermissionExamples();
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    $example->practicalUsageExamples();
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    $example->apiUsageExamples();
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    $example->extensionExamples();
    echo "</pre>";
} else {
    echo "<h1>ตัวอย่างการใช้งานระบบ Role Management</h1>";
    echo "<p><a href='?run_example=1'>คลิกเพื่อดูตัวอย่าง</a></p>";
}
?>
