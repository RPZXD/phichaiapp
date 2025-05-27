<?php
function createNavItem($href, $iconClass, $text) {
    return '
    <li class="nav-item">
        <a href="' . htmlspecialchars($href) . '" class="nav-link">
            <i class="nav-icon fas ' . htmlspecialchars($iconClass) . '"></i>
            <p>' . htmlspecialchars($text) . '</p>
        </a>
    </li>';
}

function createNavGroup($title, $iconClass, $items) {
    $html = '
    <li class="nav-item has-treeview">
        <a href="#" class="nav-link">
            <i class="nav-icon fas ' . htmlspecialchars($iconClass) . '"></i>
            <p>' . htmlspecialchars($title) . '<i class="fas fa-angle-left right"></i></p>
        </a>
        <ul class="nav nav-treeview">';
    
    foreach ($items as $item) {
        $html .= '
            <li class="nav-item">
                <a href="' . htmlspecialchars($item['href']) . '" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>' . htmlspecialchars($item['text']) . '</p>
                </a>
            </li>';
    }
    
    $html .= '
        </ul>
    </li>';
    
    return $html;
}

// เมนูหลัก
echo createNavItem('index.php', 'fa-tachometer-alt', 'แดชบอร์ด');

$role = strtolower($_SESSION['user']['role'] ?? '');
$permissions = $_SESSION['user']['permissions'] ?? [];

// เฉพาะ admin เท่านั้น
if ($role === 'admin') {
    echo createNavGroup('จัดการผู้ใช้งาน', 'fa-users', [
        ['href' => 'users.php', 'text' => 'ข้อมูลผู้ใช้งาน'],
        ['href' => 'roles.php', 'text' => 'บทบาทและสิทธิ์'],
        ['href' => 'permissions.php', 'text' => 'การกำหนดสิทธิ์'],
    ]);
    echo createNavGroup('การตั้งค่าระบบ', 'fa-cogs', [
        ['href' => 'settings.php', 'text' => 'ตั้งค่าทั่วไป'],
        ['href' => 'backup.php', 'text' => 'สำรองข้อมูล'],
        ['href' => 'logs.php', 'text' => 'บันทึกการใช้งาน'],
    ]);
}

// ตัวอย่าง: ใช้ permission ในการแสดงเมนู
if (in_array('student.view', $permissions)) {
    echo createNavGroup('จัดการนักเรียน', 'fa-graduation-cap', [
        ['href' => 'students.php', 'text' => 'ข้อมูลนักเรียน'],
        ['href' => 'student_cases.php', 'text' => 'เคสดูแลช่วยเหลือ'],
        ['href' => 'student_reports.php', 'text' => 'รายงานการดูแล'],
    ]);
}
if (in_array('teacher.view', $permissions)) {
    echo createNavGroup('จัดการครู', 'fa-chalkboard-teacher', [
        ['href' => 'teachers.php', 'text' => 'ข้อมูลครู'],
        ['href' => 'teacher_assignments.php', 'text' => 'มอบหมายงาน'],
    ]);
}
if (in_array('report.view', $permissions)) {
    echo createNavGroup('รายงานและสถิติ', 'fa-chart-bar', [
        ['href' => 'reports.php', 'text' => 'รายงานภาพรวม'],
        ['href' => 'statistics.php', 'text' => 'สถิติการใช้งาน'],
        ['href' => 'analytics.php', 'text' => 'วิเคราะห์ข้อมูล'],
    ]);
}

// ทุก role เห็นเมนูแจ้งเตือน/คู่มือ
echo createNavItem('notifications.php', 'fa-bell', 'การแจ้งเตือน');
echo createNavItem('help.php', 'fa-question-circle', 'คู่มือการใช้งาน');

// ออกจากระบบ
echo '<li class="nav-item mt-4">';
echo createNavItem('../logout.php', 'fa-sign-out-alt', 'ออกจากระบบ');
echo '</li>';

?>