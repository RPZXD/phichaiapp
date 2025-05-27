<?php
session_start();
require_once 'api/RoleController-updated.php';

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$roleController = new RoleController();
$userId = $_SESSION['user']['id'];
$userDepartments = $roleController->getUserDepartments($userId);

// Handle role switching
if ($_POST['switch_department'] ?? false) {
    $newDepartment = $_POST['department'];
    $newRole = $_POST['role'];
    
    if (isset($userDepartments[$newDepartment]) && $userDepartments[$newDepartment] === $newRole) {
        $_SESSION['current_department'] = $newDepartment;
        $_SESSION['current_role'] = $newRole;
        
        // Log the role switch
        $roleController->logPermissionAction($userId, 'role_switch', [
            'from_department' => $_SESSION['previous_department'] ?? 'none',
            'to_department' => $newDepartment,
            'to_role' => $newRole
        ]);
        
        $_SESSION['previous_department'] = $_SESSION['current_department'] ?? null;
        
        header('Location: index.php?role_switched=1');
        exit;
    }
}

$currentDepartment = $_SESSION['current_department'] ?? 'academic';
$currentRole = $_SESSION['current_role'] ?? ($userDepartments[$currentDepartment] ?? 'viewer');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนบทบาท - ระบบจัดการโรงเรียน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .role-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .role-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .role-card.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .role-card.active .text-gray-600 {
            color: rgba(255,255,255,0.8) !important;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">

<div class="wrapper">
    <!-- Content Wrapper -->
    <div class="content-wrapper" style="margin-left: 0; background: transparent;">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-white">เปลี่ยนบทบาท</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white">หน้าหลัก</a></li>
                            <li class="breadcrumb-item active text-white">เปลี่ยนบทบาท</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card role-card border-0 shadow-lg">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <h3 class="card-title text-lg font-semibold">
                                    <i class="fas fa-exchange-alt mr-2"></i>
                                    เลือกบทบาทและฝ่ายงาน
                                </h3>
                                <p class="text-gray-600 mt-2">คุณสามารถสลับระหว่างบทบาทต่างๆ ที่ได้รับมอบหมายได้</p>
                            </div>
                            <div class="card-body">
                                <!-- Current Role Display -->
                                <div class="alert alert-info mb-4">
                                    <h5><i class="fas fa-user-tag mr-2"></i>บทบาทปัจจุบัน</h5>
                                    <p class="mb-0">
                                        <strong>ฝ่าย:</strong> 
                                        <?php
                                        $deptNames = [
                                            'academic' => 'ฝ่ายวิชาการ',
                                            'budget' => 'ฝ่ายงบประมาณ', 
                                            'personnel' => 'ฝ่ายบุคคล',
                                            'general' => 'ฝ่ายงานทั่วไป'
                                        ];
                                        echo $deptNames[$currentDepartment] ?? $currentDepartment;
                                        ?>
                                        | <strong>ตำแหน่ง:</strong> 
                                        <?php
                                        $roleNames = [
                                            'admin' => 'ผู้ดูแลระบบ',
                                            'head' => 'หัวหน้าฝ่าย',
                                            'officer' => 'เจ้าหน้าที่',
                                            'teacher' => 'ครู',
                                            'viewer' => 'ผู้ดูข้อมูล'
                                        ];
                                        echo $roleNames[$currentRole] ?? $currentRole;
                                        ?>
                                    </p>
                                </div>

                                <!-- Available Roles -->
                                <form method="POST" action="">
                                    <div class="row">
                                        <?php foreach ($userDepartments as $department => $role): ?>
                                            <div class="col-md-6 mb-4">
                                                <div class="role-card p-4 rounded-lg border cursor-pointer <?= ($department === $currentDepartment) ? 'active' : '' ?>" 
                                                     onclick="selectRole('<?= $department ?>', '<?= $role ?>')">
                                                    
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="rounded-full p-3 mr-3" style="background: rgba(103, 58, 183, 0.1);">
                                                            <?php
                                                            $deptIcons = [
                                                                'academic' => 'fa-graduation-cap',
                                                                'budget' => 'fa-calculator',
                                                                'personnel' => 'fa-users',
                                                                'general' => 'fa-building'
                                                            ];
                                                            ?>
                                                            <i class="fas <?= $deptIcons[$department] ?? 'fa-circle' ?> text-lg"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold mb-1"><?= $deptNames[$department] ?? $department ?></h4>
                                                            <p class="text-gray-600 text-sm mb-0"><?= $roleNames[$role] ?? $role ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="role-permissions">
                                                        <h6 class="font-medium mb-2">สิทธิ์การเข้าถึง:</h6>
                                                        <div class="permissions-list">
                                                            <?php
                                                            // Get role permissions
                                                            $permissions = $roleController->getRolePermissions($role);
                                                            $permissionLabels = [
                                                                'view' => 'ดูข้อมูล',
                                                                'edit' => 'แก้ไขข้อมูล', 
                                                                'delete' => 'ลบข้อมูล',
                                                                'admin' => 'จัดการระบบ'
                                                            ];
                                                            
                                                            foreach ($permissions as $perm):
                                                                if (isset($permissionLabels[$perm])):
                                                            ?>
                                                            <span class="badge badge-<?= $department === $currentDepartment ? 'light' : 'primary' ?> mr-1 mb-1">
                                                                <?= $permissionLabels[$perm] ?>
                                                            </span>
                                                            <?php 
                                                                endif;
                                                            endforeach; 
                                                            ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($department === $currentDepartment): ?>
                                                        <div class="text-right mt-3">
                                                            <span class="badge badge-light">
                                                                <i class="fas fa-check mr-1"></i>
                                                                กำลังใช้งาน
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <input type="hidden" name="department" id="selected_department">
                                    <input type="hidden" name="role" id="selected_role">
                                    <input type="hidden" name="switch_department" value="1">
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" id="switch_btn" class="btn btn-primary btn-lg px-5" disabled>
                                            <i class="fas fa-exchange-alt mr-2"></i>
                                            เปลี่ยนบทบาท
                                        </button>
                                        <a href="index.php" class="btn btn-secondary btn-lg px-5 ml-3">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            ยกเลิก
                                        </a>
                                    </div>
                                </form>

                                <!-- Role Switching History -->
                                <div class="mt-5">
                                    <h5 class="mb-3">
                                        <i class="fas fa-history mr-2"></i>
                                        ประวัติการเปลี่ยนบทบาท
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>วันเวลา</th>
                                                    <th>จากฝ่าย</th>
                                                    <th>ไปยังฝ่าย</th>
                                                    <th>บทบาท</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $history = $roleController->getUserRoleSwitchHistory($userId, 10);
                                                foreach ($history as $record):
                                                ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($record['created_at'])) ?></td>
                                                    <td><?= $deptNames[$record['from_department']] ?? '-' ?></td>
                                                    <td><?= $deptNames[$record['to_department']] ?? '-' ?></td>
                                                    <td>
                                                        <span class="badge badge-info"><?= $roleNames[$record['to_role']] ?? $record['to_role'] ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
function selectRole(department, role) {
    // Remove active class from all cards
    document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to selected card
    event.currentTarget.classList.add('active');
    
    // Update hidden fields
    document.getElementById('selected_department').value = department;
    document.getElementById('selected_role').value = role;
    
    // Enable switch button
    document.getElementById('switch_btn').disabled = false;
    
    // Update button text
    const deptNames = {
        'academic': 'ฝ่ายวิชาการ',
        'budget': 'ฝ่ายงบประมาณ',
        'personnel': 'ฝ่ายบุคคล', 
        'general': 'ฝ่ายงานทั่วไป'
    };
    
    const roleNames = {
        'admin': 'ผู้ดูแลระบบ',
        'head': 'หัวหน้าฝ่าย',
        'officer': 'เจ้าหน้าที่',
        'teacher': 'ครู',
        'viewer': 'ผู้ดูข้อมูล'
    };
    
    document.getElementById('switch_btn').innerHTML = 
        '<i class="fas fa-exchange-alt mr-2"></i>เปลี่ยนเป็น ' + 
        roleNames[role] + ' (' + deptNames[department] + ')';
}

// Show success message if redirected after role switch
if (window.location.search.includes('role_switched=1')) {
    setTimeout(() => {
        alert('เปลี่ยนบทบาทเรียบร้อยแล้ว');
    }, 500);
}
</script>

</body>
</html>
