# 🚀 ระบบจัดการสิทธิ์การเข้าถึง 4 ฝ่ายงาน - คู่มือการติดตั้งและใช้งาน

## 📋 ภาพรวมระบบ

ระบบจัดการสิทธิ์การเข้าถึงแบบใหม่ที่รองรับ 4 ฝ่ายงาน:

- 🎓 **ฝ่ายวิชาการ** (Academic)
- 💰 **ฝ่ายงบประมาณ** (Budget)
- 👥 **ฝ่ายบุคคล** (Personnel)
- 🏢 **ฝ่ายงานทั่วไป** (General)

### ✨ คุณสมบัติหิลัก

- **Multi-Department Roles**: ผู้ใช้ 1 คนสามารถมีบทบาทต่างกันในแต่ละฝ่าย
- **Role Hierarchy**: Admin(5) > Head(4) > Officer(3) > Teacher(2) > Viewer(1)
- **Granular Permissions**: จัดการสิทธิ์ระดับโมดูลย่อย
- **Audit Trail**: ติดตามการเปลี่ยนแปลงสิทธิ์
- **Role Switching**: เปลี่ยนบทบาทได้ตามที่ได้รับมอบหมาย

## 🔧 การติดตั้ง

### ขั้นตอนที่ 1: การเตรียมไฟล์

ไฟล์ที่สร้างขึ้นใหม่:

```
view/
├── roles_new.php                    # หน้าจัดการสิทธิ์ใหม่
├── leftmenu_updated.php             # เมนูที่อัพเดทแล้ว
├── switch_role.php                  # หน้าเปลี่ยนบทบาท
├── js/
│   └── role-management-updated.js   # JavaScript ใหม่
└── api/
    └── RoleController-updated.php   # API ใหม่

migrate_roles.php                    # สคริปต์ migration
test_role_system.php                 # ไฟล์ทดสอบระบบ
role_management_example_updated.php  # ตัวอย่างการใช้งาน
```

### ขั้นตอนที่ 2: การ Migration ฐานข้อมูล

1. **เรียกใช้ Migration Script**:

```bash
cd c:\xampp\htdocs\phichaiapp
php migrate_roles.php
```

2. **ตารางใหม่ที่จะถูกสร้าง**:
   - `user_roles` - เก็บบทบาทของผู้ใช้ในแต่ละฝ่าย
   - `user_permissions` - สิทธิ์ระดับโมดูล
   - `permission_logs` - บันทึกการเปลี่ยนแปลง
   - `department_modules` - โมดูลในแต่ละฝ่าย
   - `role_definitions` - นิยามบทบาท

### ขั้นตอนที่ 3: การทดสอบระบบ

```bash
# ทดสอบระบบทั้งหมด
php test_role_system.php

# หรือทดสอบผ่าน Web Browser
http://localhost/phichaiapp/test_role_system.php?test=5
```

### ขั้นตอนที่ 4: การแทนที่ไฟล์เดิม

```bash
# สำรองไฟล์เดิม
cp view/roles.php view/roles_old.php
cp view/leftmenu.php view/leftmenu_old.php
cp view/js/role-management.js view/js/role-management_old.js
cp view/api/RoleController.php view/api/RoleController_old.php

# แทนที่ด้วยไฟล์ใหม่
cp view/roles_new.php view/roles.php
cp view/leftmenu_updated.php view/leftmenu.php
cp view/js/role-management-updated.js view/js/role-management.js
cp view/api/RoleController-updated.php view/api/RoleController.php
```

## 💻 การใช้งาน

### 1. การกำหนดสิทธิ์ผู้ใช้

```php
<?php
require_once 'view/api/RoleController.php';

$roleController = new RoleController();

// กำหนดบทบาทให้ผู้ใช้
$roleController->assignUserRole(
    $userId = 123,
    $department = 'academic',
    $role = 'teacher',
    $grantedBy = 1
);

// ตรวจสอบสิทธิ์
$hasAccess = $roleController->checkPermission(
    $userId = 123,
    $department = 'academic',
    $module = 'curriculum',
    $permission = 'edit'
);
?>
```

### 2. การใช้งานในเมนู

```php
<?php
// ในไฟล์ leftmenu.php
$roleController = new RoleController();
$userId = $_SESSION['user']['id'];

// ตรวจสอบสิทธิ์เข้าถึงเมนู
if ($roleController->checkPermission($userId, 'academic', 'curriculum', 'view')) {
    echo createNavItem('academic/curriculum.php', 'fa-book', 'หลักสูตร');
}
?>
```

### 3. การเปลี่ยนบทบาท

ผู้ใช้ที่มีหลายบทบาทสามารถเปลี่ยนได้ผ่าน:

- เมนู "เปลี่ยนบทบาท" ในแถบนำทาง
- หน้า `switch_role.php`

### 4. การติดตามการใช้งาน

```php
<?php
// บันทึกการกระทำ
$roleController->logPermissionAction($userId, 'login', [
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT']
]);

// ดูประวัติการเปลี่ยนบทบาท
$history = $roleController->getUserRoleSwitchHistory($userId, 10);
?>
```

## 🏗️ โครงสร้างสิทธิ์

### บทบาทและระดับสิทธิ์

| บทบาท   | ระดับ | สิทธิ์                    | คำอธิบาย    |
| ------- | ----- | ------------------------- | ----------- |
| Admin   | 5     | view, edit, delete, admin | ผู้ดูแลระบบ |
| Head    | 4     | view, edit, delete        | หัวหน้าฝ่าย |
| Officer | 3     | view, edit                | เจ้าหน้าที่ |
| Teacher | 2     | view, edit                | ครู         |
| Viewer  | 1     | view                      | ผู้ดูข้อมูล |

### โมดูลในแต่ละฝ่าย

#### 🎓 ฝ่ายวิชาการ

- หลักสูตร (Curriculum)
- ตารางเรียน (Schedule)
- การประเมิน (Assessment)
- ระเบียนนักเรียน (Student Records)
- การสอบ (Examination)
- วิจัยและพัฒนา (Research)

#### 💰 ฝ่ายงบประมาณ

- วางแผนงบประมาณ (Planning)
- อนุมัติงบประมาณ (Approval)
- ติดตามการใช้งบ (Tracking)
- จัดซื้อจัดจ้าง (Procurement)
- ตรวจสอบการเงิน (Audit)
- รายงานทางการเงิน (Reporting)

#### 👥 ฝ่ายบุคคล

- สรรหาบุคลากร (Recruitment)
- พัฒนาบุคลากร (Development)
- ประเมินผลการปฏิบัติงาน (Evaluation)
- การลาและขาดงาน (Leave)
- สวัสดิการ (Welfare)
- เงินเดือนและค่าตอบแทน (Payroll)

#### 🏢 ฝ่ายงานทั่วไป

- จัดการสิ่งอำนวยความสะดวก (Facility)
- ซ่อมบำรุง (Maintenance)
- รักษาความปลอดภัย (Security)
- ประชาสัมพันธ์ (Communication)
- จัดกิจกรรม (Events)
- จัดเก็บเอกสาร (Archive)

## 🔍 การแก้ไขปัญหา

### ปัญหาที่พบบ่อย

1. **ผู้ใช้ไม่เห็นเมนู**

   - ตรวจสอบว่าได้กำหนดบทบาทแล้ว
   - ตรวจสอบ session และการ login

2. **เมนูไม่แสดงถูกต้อง**

   - ตรวจสอบไฟล์ `leftmenu.php` ว่าใช้เวอร์ชันใหม่
   - ตรวจสอบการ include RoleController

3. **Permission ไม่ทำงาน**

   - ตรวจสอบตาราง `user_roles` และ `user_permissions`
   - รัน migration script ใหม่

4. **การเปลี่ยนบทบาทไม่ทำงาน**
   - ตรวจสอบ session management
   - ตรวจสอบ JavaScript errors

### การ Debug

```php
<?php
// เปิด debug mode
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตรวจสอบสิทธิ์ผู้ใช้
$roleController = new RoleController();
$userId = 123;

echo "User Departments:\n";
var_dump($roleController->getUserDepartments($userId));

echo "User Permissions:\n";
var_dump($roleController->getUserPermissions($userId));
?>
```

## 🚀 การพัฒนาต่อ

### การเพิ่มฝ่ายงานใหม่

1. เพิ่มใน enum ของตาราง `user_roles`
2. เพิ่มโมดูลใน `department_modules`
3. อัพเดท UI และ JavaScript

### การเพิ่มบทบาทใหม่

1. เพิ่มใน `role_definitions`
2. กำหนด level และ permissions
3. อัพเดท UI

### การ Customize

- แก้ไขสี theme ใน CSS
- เพิ่ม/ลด โมดูลตามต้องการ
- ปรับแต่ง permission logic

## 📞 การสนับสนุน

หากพบปัญหาหรือต้องการความช่วยเหลือ:

1. ตรวจสอบ log ไฟล์
2. รัน test suite (`test_role_system.php`)
3. ตรวจสอบ documentation ใน `role_management_example_updated.php`

## 📋 Checklist การติดตั้ง

- [ ] รัน migration script สำเร็จ
- [ ] ทดสอบระบบผ่าน test suite
- [ ] แทนที่ไฟล์เดิมด้วยไฟล์ใหม่
- [ ] ทดสอบการ login และเมนู
- [ ] ทดสอบการกำหนดสิทธิ์
- [ ] ทดสอบการเปลี่ยนบทบาท
- [ ] ตรวจสอบ audit trail
- [ ] สำรองข้อมูลเดิม
- [ ] อบรมผู้ใช้งาน

---

🎉 **ยินดีด้วย!** คุณได้ติดตั้งระบบจัดการสิทธิ์การเข้าถึง 4 ฝ่ายงานเรียบร้อยแล้ว
