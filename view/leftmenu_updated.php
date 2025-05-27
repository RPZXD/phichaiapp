<?php
// Updated leftmenu.php for new 4-department role system
require_once 'api/RoleController-updated.php';

function createNavItem($href, $iconClass, $text, $badge = null) {
    $badgeHtml = $badge ? '<span class="badge badge-' . $badge['type'] . ' right">' . $badge['text'] . '</span>' : '';
    return '
    <li class="nav-item">
        <a href="' . htmlspecialchars($href) . '" class="nav-link">
            <i class="nav-icon fas ' . htmlspecialchars($iconClass) . '"></i>
            <p>' . htmlspecialchars($text) . $badgeHtml . '</p>
        </a>
    </li>';
}

function createNavGroup($title, $iconClass, $items, $badge = null) {
    $badgeHtml = $badge ? '<span class="badge badge-' . $badge['type'] . ' right">' . $badge['text'] . '</span>' : '';
    $html = '
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas ' . htmlspecialchars($iconClass) . '"></i>
            <p>' . htmlspecialchars($title) . '<i class="fas fa-angle-left right"></i>' . $badgeHtml . '</p>
        </a>
        <ul class="nav nav-treeview">';
    
    foreach ($items as $item) {
        $itemBadge = isset($item['badge']) ? '<span class="badge badge-' . $item['badge']['type'] . ' right">' . $item['badge']['text'] . '</span>' : '';
        $html .= '
            <li class="nav-item">
                <a href="' . htmlspecialchars($item['href']) . '" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>' . htmlspecialchars($item['text']) . $itemBadge . '</p>
                </a>
            </li>';
    }
    
    $html .= '
        </ul>
    </li>';
    
    return $html;
}

function createDepartmentHeader($departmentName, $iconClass) {
    return '
    <li class="nav-header">' . htmlspecialchars($departmentName) . '</li>';
}

// Initialize role controller
$roleController = new RoleController();
$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    echo createNavItem('login.php', 'fa-sign-in-alt', 'เข้าสู่ระบบ');
    return;
}

// Get user's departments and roles
$userDepartments = $roleController->getUserDepartments($userId);
$userPermissions = $roleController->getUserPermissions($userId);

// Dashboard - ทุกคนเห็น
echo createNavItem('index.php', 'fa-tachometer-alt', 'แดชบอร์ด');

// Admin Management - เฉพาะ admin
if ($roleController->hasSystemRole($userId, 'admin')) {
    echo createDepartmentHeader('จัดการระบบ', 'fa-cogs');
    echo createNavGroup('จัดการผู้ใช้งาน', 'fa-users', [
        ['href' => 'users.php', 'text' => 'ข้อมูลผู้ใช้งาน'],
        ['href' => 'roles_new.php', 'text' => 'บทบาทและสิทธิ์'],
        ['href' => 'permissions.php', 'text' => 'การกำหนดสิทธิ์'],
        ['href' => 'department_settings.php', 'text' => 'ตั้งค่าฝ่ายงาน'],
    ]);
    echo createNavGroup('การตั้งค่าระบบ', 'fa-cogs', [
        ['href' => 'settings.php', 'text' => 'ตั้งค่าทั่วไป'],
        ['href' => 'backup.php', 'text' => 'สำรองข้อมูล'],
        ['href' => 'logs.php', 'text' => 'บันทึกการใช้งาน'],
        ['href' => 'audit_trail.php', 'text' => 'ติดตามการเปลี่ยนแปลง'],
    ]);
}

// Department-specific menus
$departments = [
    'academic' => [
        'name' => 'ฝ่ายวิชาการ',
        'icon' => 'fa-graduation-cap',
        'modules' => [
            'curriculum' => ['text' => 'หลักสูตร', 'href' => 'academic/curriculum.php'],
            'schedule' => ['text' => 'ตารางเรียน', 'href' => 'academic/schedule.php'],
            'assessment' => ['text' => 'การประเมิน', 'href' => 'academic/assessment.php'],
            'student_records' => ['text' => 'ระเบียนนักเรียน', 'href' => 'academic/student_records.php'],
            'examination' => ['text' => 'การสอบ', 'href' => 'academic/examination.php'],
            'research' => ['text' => 'วิจัยและพัฒนา', 'href' => 'academic/research.php']
        ]
    ],
    'budget' => [
        'name' => 'ฝ่ายงบประมาณ',
        'icon' => 'fa-calculator',
        'modules' => [
            'planning' => ['text' => 'วางแผนงบประมาณ', 'href' => 'budget/planning.php'],
            'approval' => ['text' => 'อนุมัติงบประมาณ', 'href' => 'budget/approval.php'],
            'tracking' => ['text' => 'ติดตามการใช้งบ', 'href' => 'budget/tracking.php'],
            'procurement' => ['text' => 'จัดซื้อจัดจ้าง', 'href' => 'budget/procurement.php'],
            'audit' => ['text' => 'ตรวจสอบการเงิน', 'href' => 'budget/audit.php'],
            'reporting' => ['text' => 'รายงานทางการเงิน', 'href' => 'budget/reporting.php']
        ]
    ],
    'personnel' => [
        'name' => 'ฝ่ายบุคคล',
        'icon' => 'fa-users',
        'modules' => [
            'recruitment' => ['text' => 'สรรหาบุคลากร', 'href' => 'personnel/recruitment.php'],
            'development' => ['text' => 'พัฒนาบุคลากร', 'href' => 'personnel/development.php'],
            'evaluation' => ['text' => 'ประเมินผลการปฏิบัติงาน', 'href' => 'personnel/evaluation.php'],
            'leave' => ['text' => 'การลาและขาดงาน', 'href' => 'personnel/leave.php'],
            'welfare' => ['text' => 'สวัสดิการ', 'href' => 'personnel/welfare.php'],
            'payroll' => ['text' => 'เงินเดือนและค่าตอบแทน', 'href' => 'personnel/payroll.php']
        ]
    ],
    'general' => [
        'name' => 'ฝ่ายงานทั่วไป',
        'icon' => 'fa-building',
        'modules' => [
            'facility' => ['text' => 'จัดการสิ่งอำนวยความสะดวก', 'href' => 'general/facility.php'],
            'maintenance' => ['text' => 'ซ่อมบำรุง', 'href' => 'general/maintenance.php'],
            'security' => ['text' => 'รักษาความปลอดภัย', 'href' => 'general/security.php'],
            'communication' => ['text' => 'ประชาสัมพันธ์', 'href' => 'general/communication.php'],
            'events' => ['text' => 'จัดกิจกรรม', 'href' => 'general/events.php'],
            'archive' => ['text' => 'จัดเก็บเอกสาร', 'href' => 'general/archive.php']
        ]
    ]
];

// Display department menus based on user roles
foreach ($departments as $deptKey => $dept) {
    $userRole = $userDepartments[$deptKey] ?? null;
    
    if ($userRole) {
        // User has a role in this department
        echo createDepartmentHeader($dept['name'], $dept['icon']);
        
        $accessibleModules = [];
        
        foreach ($dept['modules'] as $moduleKey => $module) {
            $permissionKey = $deptKey . '.' . $moduleKey;
            
            // Check if user has permission for this module
            if ($roleController->checkPermission($userId, $deptKey, $moduleKey, 'view')) {
                $badge = null;
                
                // Add badges for special permissions
                if ($roleController->checkPermission($userId, $deptKey, $moduleKey, 'admin')) {
                    $badge = ['type' => 'danger', 'text' => 'Admin'];
                } elseif ($roleController->checkPermission($userId, $deptKey, $moduleKey, 'edit')) {
                    $badge = ['type' => 'warning', 'text' => 'Edit'];
                } elseif ($roleController->checkPermission($userId, $deptKey, $moduleKey, 'view')) {
                    $badge = ['type' => 'info', 'text' => 'View'];
                }
                
                $accessibleModules[] = [
                    'href' => $module['href'],
                    'text' => $module['text'],
                    'badge' => $badge
                ];
            }
        }
        
        if (!empty($accessibleModules)) {
            // Show role badge for department
            $roleBadge = null;
            switch ($userRole) {
                case 'admin':
                    $roleBadge = ['type' => 'danger', 'text' => 'Admin'];
                    break;
                case 'head':
                    $roleBadge = ['type' => 'warning', 'text' => 'หัวหน้า'];
                    break;
                case 'officer':
                    $roleBadge = ['type' => 'primary', 'text' => 'เจ้าหน้าที่'];
                    break;
                case 'teacher':
                    $roleBadge = ['type' => 'success', 'text' => 'ครู'];
                    break;
                case 'viewer':
                    $roleBadge = ['type' => 'secondary', 'text' => 'ผู้ดู'];
                    break;
            }
            
            echo createNavGroup($dept['name'], $dept['icon'], $accessibleModules, $roleBadge);
        }
    }
}

// Cross-department features
$hasCrossDeptAccess = false;
foreach ($userDepartments as $role) {
    if (in_array($role, ['admin', 'head'])) {
        $hasCrossDeptAccess = true;
        break;
    }
}

if ($hasCrossDeptAccess) {
    echo createDepartmentHeader('รายงานและวิเคราะห์', 'fa-chart-bar');
    echo createNavGroup('รายงานข้ามฝ่าย', 'fa-chart-line', [
        ['href' => 'reports/cross_department.php', 'text' => 'รายงานภาพรวม'],
        ['href' => 'reports/performance.php', 'text' => 'ประสิทธิภาพองค์กร'],
        ['href' => 'reports/budget_overview.php', 'text' => 'สรุปงบประมาณ'],
        ['href' => 'reports/staff_overview.php', 'text' => 'สรุปบุคลากร'],
    ]);
}

// Common features for all users
echo createDepartmentHeader('เครื่องมือทั่วไป', 'fa-tools');

$commonItems = [
    ['href' => 'notifications.php', 'text' => 'การแจ้งเตือน'],
    ['href' => 'calendar.php', 'text' => 'ปฏิทินกิจกรรม'],
    ['href' => 'help.php', 'text' => 'คู่มือการใช้งาน'],
    ['href' => 'profile.php', 'text' => 'ข้อมูลส่วนตัว']
];

foreach ($commonItems as $item) {
    echo createNavItem($item['href'], 'fa-circle', $item['text']);
}

// Role switching (if user has multiple roles)
if (count($userDepartments) > 1) {
    echo createNavItem('switch_role.php', 'fa-exchange-alt', 'เปลี่ยนบทบาท', 
        ['type' => 'info', 'text' => count($userDepartments)]);
}

// Logout
echo '<li class="nav-item mt-4">';
echo createNavItem('../logout.php', 'fa-sign-out-alt', 'ออกจากระบบ');
echo '</li>';

// Add some JavaScript for dynamic menu behavior
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add active class to current page
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
            
            // If it's in a treeview, expand the parent
            const treeviewParent = link.closest('.nav-treeview');
            if (treeviewParent) {
                const parentLi = treeviewParent.closest('.has-treeview');
                if (parentLi) {
                    parentLi.classList.add('menu-open');
                    const parentLink = parentLi.querySelector('> .nav-link');
                    if (parentLink) {
                        parentLink.classList.add('active');
                    }
                }
            }
        }
    });
    
    // Add department role indicators
    const roleElements = document.querySelectorAll('.badge');
    roleElements.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip-role';
            tooltip.textContent = this.getAttribute('data-tooltip') || this.textContent;
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1000;
                pointer-events: none;
            `;
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left - tooltip.offsetWidth / 2 + rect.width / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        });
        
        badge.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip-role');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});
</script>

<style>
/* Department headers */
.nav-header {
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    margin-top: 15px;
    padding: 10px 15px 5px;
}

/* Role badges */
.badge {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 10px;
}

/* Navigation enhancements */
.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transition: background-color 0.2s;
}

.nav-link.active {
    background-color: rgba(0, 123, 255, 0.2);
    color: #007bff;
}

/* Department icons */
.nav-icon {
    width: 20px;
    text-align: center;
}

/* Treeview styling */
.nav-treeview .nav-link {
    padding-left: 50px;
}

.nav-treeview .nav-icon {
    font-size: 0.8rem;
}
</style>
