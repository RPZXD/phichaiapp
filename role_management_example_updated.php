<?php
/**
 * ตัวอย่างการใช้งานระบบจัดการสิทธิ์แบบ 4 ฝ่ายงาน
 * Author: ICT Team @Phichai School
 * Date: December 2024
 */

require_once('view/api/RoleController-updated.php');
require_once('config/Database.php');

echo "<h1>ตัวอย่างการใช้งานระบบจัดการสิทธิ์แบบ 4 ฝ่ายงาน</h1>";

// ตัวอย่างการใช้งาน 1: การตรวจสอบสิทธิ์ในระบบ
echo "<h2>1. การตรวจสอบสิทธิ์ในระบบ</h2>";

// สมมติมีผู้ใช้ ID = 1 (Admin) และ ID = 2 (Teacher)
$adminUserId = 1;
$teacherUserId = 2;

// ตรวจสอบสิทธิ์ Admin ในงานวิชาการ
echo "<h3>ตรวจสอบสิทธิ์ Admin:</h3>";
$canAdminViewCurriculum = RoleController::checkUserPermission($adminUserId, 'academic', 'curriculum', 'view');
$canAdminEditCurriculum = RoleController::checkUserPermission($adminUserId, 'academic', 'curriculum', 'edit');
$canAdminDeleteBudget = RoleController::checkUserPermission($adminUserId, 'budget', 'budget_plan', 'delete');

echo "- Admin สามารถดูหลักสูตร: " . ($canAdminViewCurriculum ? "ได้" : "ไม่ได้") . "<br>";
echo "- Admin สามารถแก้ไขหลักสูตร: " . ($canAdminEditCurriculum ? "ได้" : "ไม่ได้") . "<br>";
echo "- Admin สามารถลบแผนงบประมาณ: " . ($canAdminDeleteBudget ? "ได้" : "ไม่ได้") . "<br>";

// ตรวจสอบสิทธิ์ Teacher ในงานวิชาการ
echo "<h3>ตรวจสอบสิทธิ์ Teacher:</h3>";
$canTeacherViewSchedule = RoleController::checkUserPermission($teacherUserId, 'academic', 'schedule', 'view');
$canTeacherCreateAssessment = RoleController::checkUserPermission($teacherUserId, 'academic', 'assessment', 'create');
$canTeacherEditBudget = RoleController::checkUserPermission($teacherUserId, 'budget', 'expenses', 'edit');

echo "- Teacher สามารถดูตารางเรียน: " . ($canTeacherViewSchedule ? "ได้" : "ไม่ได้") . "<br>";
echo "- Teacher สามารถสร้างการประเมิน: " . ($canTeacherCreateAssessment ? "ได้" : "ไม่ได้") . "<br>";
echo "- Teacher สามารถแก้ไขรายจ่าย: " . ($canTeacherEditBudget ? "ได้" : "ไม่ได้") . "<br>";

echo "<hr>";

// ตัวอย่างการใช้งาน 2: การตรวจสอบบทบาท
echo "<h2>2. การตรวจสอบบทบาท</h2>";

// ตรวจสอบบทบาทขั้นต่ำ
echo "<h3>ตรวจสอบบทบาทขั้นต่ำ:</h3>";
$isAdminLevel = RoleController::checkUserRole($adminUserId, 'academic', 5); // ระดับ Admin
$isHeadOrHigher = RoleController::checkUserRole($teacherUserId, 'academic', 4); // ระดับหัวหน้าฝ่ายขึ้นไป
$isOfficerOrHigher = RoleController::checkUserRole($teacherUserId, 'academic', 3); // ระดับเจ้าหน้าที่ขึ้นไป

echo "- User ID 1 มีบทบาทระดับ Admin ในงานวิชาการ: " . ($isAdminLevel ? "ใช่" : "ไม่") . "<br>";
echo "- User ID 2 มีบทบาทระดับหัวหน้าฝ่ายขึ้นไปในงานวิชาการ: " . ($isHeadOrHigher ? "ใช่" : "ไม่") . "<br>";
echo "- User ID 2 มีบทบาทระดับเจ้าหน้าที่ขึ้นไปในงานวิชาการ: " . ($isOfficerOrHigher ? "ใช่" : "ไม่") . "<br>";

echo "<hr>";

// ตัวอย่างการใช้งาน 3: การใช้งานในไฟล์ PHP
echo "<h2>3. ตัวอย่างการใช้งานในไฟล์ PHP</h2>";

echo "<h3>A. การป้องกันการเข้าถึงหน้า (Page Protection):</h3>";
echo "<pre>";
echo htmlspecialchars('
<?php
session_start();
require_once("view/api/RoleController-updated.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION["user"]["id"];

// ตรวจสอบสิทธิ์เข้าถึงหน้าจัดการงบประมาณ
$canAccessBudget = RoleController::checkUserRole($userId, "budget", 2); // ครูขึ้นไป

if (!$canAccessBudget) {
    echo "<div class=\"alert alert-danger\">คุณไม่มีสิทธิ์เข้าถึงหน้านี้</div>";
    exit();
}

// โค้ดหน้าจัดการงบประมาณ...
?>
');
echo "</pre>";

echo "<h3>B. การซ่อนแสดงปุ่มตามสิทธิ์:</h3>";
echo "<pre>";
echo htmlspecialchars('
<?php
$userId = $_SESSION["user"]["id"];

// ตรวจสอบสิทธิ์ต่างๆ
$canCreate = RoleController::checkUserPermission($userId, "academic", "curriculum", "create");
$canEdit = RoleController::checkUserPermission($userId, "academic", "curriculum", "edit");
$canDelete = RoleController::checkUserPermission($userId, "academic", "curriculum", "delete");
?>

<div class="toolbar">
    <?php if ($canCreate): ?>
        <button class="btn btn-success" onclick="createCurriculum()">
            <i class="fas fa-plus"></i> เพิ่มหลักสูตร
        </button>
    <?php endif; ?>
    
    <?php if ($canEdit): ?>
        <button class="btn btn-warning" onclick="editCurriculum()">
            <i class="fas fa-edit"></i> แก้ไข
        </button>
    <?php endif; ?>
    
    <?php if ($canDelete): ?>
        <button class="btn btn-danger" onclick="deleteCurriculum()">
            <i class="fas fa-trash"></i> ลบ
        </button>
    <?php endif; ?>
</div>
');
echo "</pre>";

echo "<h3>C. การตรวจสอบสิทธิ์ใน AJAX/API:</h3>";
echo "<pre>";
echo htmlspecialchars('
<?php
// api/curriculum.php
session_start();
require_once("../view/api/RoleController-updated.php");

$userId = $_SESSION["user"]["id"];
$action = $_POST["action"] ?? $_GET["action"];

switch ($action) {
    case "create":
        // ตรวจสอบสิทธิ์สร้าง
        if (!RoleController::checkUserPermission($userId, "academic", "curriculum", "create")) {
            http_response_code(403);
            echo json_encode(["error" => "ไม่มีสิทธิ์สร้างข้อมูล"]);
            exit();
        }
        // ดำเนินการสร้าง...
        break;
        
    case "update":
        // ตรวจสอบสิทธิ์แก้ไข
        if (!RoleController::checkUserPermission($userId, "academic", "curriculum", "edit")) {
            http_response_code(403);
            echo json_encode(["error" => "ไม่มีสิทธิ์แก้ไขข้อมูล"]);
            exit();
        }
        // ดำเนินการแก้ไข...
        break;
        
    case "delete":
        // ตรวจสอบสิทธิ์ลบ
        if (!RoleController::checkUserPermission($userId, "academic", "curriculum", "delete")) {
            http_response_code(403);
            echo json_encode(["error" => "ไม่มีสิทธิ์ลบข้อมูล"]);
            exit();
        }
        // ดำเนินการลบ...
        break;
}
?>
');
echo "</pre>";

echo "<hr>";

// ตัวอย่างการใช้งาน 4: สร้างเมนูแบบไดนามิก
echo "<h2>4. การสร้างเมนูแบบไดนามิกตามสิทธิ์</h2>";

echo "<h3>ตัวอย่างโค้ดสำหรับสร้างเมนู:</h3>";
echo "<pre>";
echo htmlspecialchars('
<?php
function generateDynamicMenu($userId) {
    // กำหนดโครงสร้างเมนู
    $menus = [
        "academic" => [
            "name" => "งานวิชาการ",
            "icon" => "fa-graduation-cap",
            "submenus" => [
                "curriculum" => ["name" => "หลักสูตร", "icon" => "fa-book", "url" => "curriculum.php"],
                "schedule" => ["name" => "ตารางเรียน", "icon" => "fa-calendar", "url" => "schedule.php"],
                "assessment" => ["name" => "การประเมิน", "icon" => "fa-chart-line", "url" => "assessment.php"]
            ]
        ],
        "budget" => [
            "name" => "งานงบประมาณ",
            "icon" => "fa-chart-pie",
            "submenus" => [
                "budget_plan" => ["name" => "แผนงบประมาณ", "icon" => "fa-chart-bar", "url" => "budget.php"],
                "expenses" => ["name" => "รายจ่าย", "icon" => "fa-money-bill", "url" => "expenses.php"]
            ]
        ]
    ];

    $html = "<ul class=\"menu\">";
    
    foreach ($menus as $deptKey => $dept) {
        // ตรวจสอบว่ามีสิทธิ์เข้าถึงฝ่ายนี้หรือไม่
        $hasAccess = RoleController::checkUserRole($userId, $deptKey, 1); // ระดับขั้นต่ำ
        
        if ($hasAccess) {
            $html .= "<li class=\"menu-item\">";
            $html .= "<a href=\"#\"><i class=\"fas {$dept["icon"]}\"></i> {$dept["name"]}</a>";
            $html .= "<ul class=\"submenu\">";
            
            foreach ($dept["submenus"] as $moduleKey => $submenu) {
                // ตรวจสอบสิทธิ์เข้าถึงโมดูล
                $canView = RoleController::checkUserPermission($userId, $deptKey, $moduleKey, "view");
                
                if ($canView) {
                    $html .= "<li><a href=\"{$submenu["url"]}\">";
                    $html .= "<i class=\"fas {$submenu["icon"]}\"></i> {$submenu["name"]}</a></li>";
                }
            }
            
            $html .= "</ul></li>";
        }
    }
    
    $html .= "</ul>";
    return $html;
}

// การใช้งาน
$userId = $_SESSION["user"]["id"];
echo generateDynamicMenu($userId);
?>
');
echo "</pre>";

echo "<hr>";

// ตัวอย่างการใช้งาน 5: Best Practices
echo "<h2>5. Best Practices และข้อแนะนำ</h2>";

echo "<h3>A. การตั้งค่าค่าเริ่มต้น:</h3>";
echo "<ul>";
echo "<li><strong>Fail-safe:</strong> เมื่อไม่แน่ใจ ให้ปฏิเสธการเข้าถึง</li>";
echo "<li><strong>Principle of Least Privilege:</strong> ให้สิทธิ์เท่าที่จำเป็น</li>";
echo "<li><strong>Role Hierarchy:</strong> ใช้ประโยชน์จากระบบลำดับชั้นบทบาท</li>";
echo "</ul>";

echo "<h3>B. การจัดการ Error:</h3>";
echo "<pre>";
echo htmlspecialchars('
try {
    $hasPermission = RoleController::checkUserPermission($userId, $dept, $module, $permission);
    if (!$hasPermission) {
        throw new Exception("ไม่มีสิทธิ์เข้าถึง");
    }
    // ดำเนินการต่อ...
} catch (Exception $e) {
    error_log("Permission denied: " . $e->getMessage());
    // แสดงข้อความแจ้งเตือนแก่ผู้ใช้
    echo "<div class=\"alert alert-danger\">{$e->getMessage()}</div>";
}
');
echo "</pre>";

echo "<h3>C. การ Debug และ Log:</h3>";
echo "<pre>";
echo htmlspecialchars('
// เปิดใช้งาน logging
if (defined("DEBUG") && DEBUG) {
    error_log("Checking permission for User: $userId, Dept: $dept, Module: $module, Permission: $permission");
}

// ใช้ Permission Logs API
// GET: api/RoleController-updated.php?action=getPermissionLogs&userId=1&limit=10
');
echo "</pre>";

echo "<h3>D. การ Cache สิทธิ์:</h3>";
echo "<pre>";
echo htmlspecialchars('
// เก็บ cache สิทธิ์ในเซสชัน (สำหรับลดการ query ฐานข้อมูล)
if (!isset($_SESSION["user_permissions"])) {
    $_SESSION["user_permissions"] = [];
}

$cacheKey = "{$userId}_{$dept}_{$module}_{$permission}";
if (!isset($_SESSION["user_permissions"][$cacheKey])) {
    $_SESSION["user_permissions"][$cacheKey] = 
        RoleController::checkUserPermission($userId, $dept, $module, $permission);
}

$hasPermission = $_SESSION["user_permissions"][$cacheKey];
');
echo "</pre>";

echo "<hr>";
echo "<p><strong>หมายเหตุ:</strong> ระบบนี้ถูกออกแบบให้มีความยืดหยุ่นและปลอดภัย ผู้ดูแลระบบสามารถกำหนดสิทธิ์ได้อย่างละเอียด และระบบจะบันทึก log การเปลี่ยนแปลงทั้งหมดเพื่อการตรวจสอบ</p>";
?>
