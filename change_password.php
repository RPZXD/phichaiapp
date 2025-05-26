<?php
session_start();

// โหลด config
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$pageConfig = $config['global'];

require_once __DIR__ . '/classes/DatabaseUsers.php';

$username = $_SESSION['change_password_user'] ?? null;
if (!$username) {
    header('Location: login.php');
    exit;
}

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "กรุณากรอกรหัสผ่านใหม่ทั้งสองช่อง";
    } elseif ($new_password !== $confirm_password) {
        $error = "รหัสผ่านใหม่ทั้งสองช่องไม่ตรงกัน";
    } elseif (strlen($new_password) < 6) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        // อัปเดตรหัสผ่าน
        $db = new \App\DatabaseUsers();
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->query("UPDATE teacher SET password = :password WHERE Teach_id = :username OR Teach_name = :username", [
            'password' => $hashed,
            'username' => $username
        ]);
        unset($_SESSION['change_password_user']);
        $success = "เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบใหม่";
        // redirect ไป login หลัง 2 วินาที
        echo "<script>
            setTimeout(function(){ window.location.href = 'login.php'; }, 2000);
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน | <?php echo htmlspecialchars($pageConfig['pageTitle']); ?></title>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($pageConfig['logoLink']); ?>" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Mali:wght@200;300;400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 font-sans" style="font-family: 'Mali', sans-serif;">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md" data-aos="fade-up">
            <div class="flex flex-col items-center mb-4">
                <?php if (!empty($pageConfig['logoLink'])): ?>
                    <img src="<?php echo htmlspecialchars($pageConfig['logoLink']); ?>" alt="logo" class="h-14 w-14 mb-2 rounded-full bg-white p-1 shadow" />
                <?php endif; ?>
                <span class="text-blue-700 font-bold text-lg"><?php echo htmlspecialchars($pageConfig['nameschool']); ?></span>
            </div>
            <h2 class="text-3xl font-bold text-center text-blue-600 mb-6">เปลี่ยนรหัสผ่าน 🔑</h2>
            <span class="text-base text-red-500 ">** รหัสผ่านจะต้องประกอบไปด้วยตัวอักษรและตัวเลขอย่างน้อย 6 ตัวอักษร ** </span>
            <?php if ($error): ?>
                <script>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: <?= json_encode($error) ?>,
                    confirmButtonText: 'ปิด',
                    confirmButtonColor: '#3085d6'
                });
                </script>
            <?php elseif ($success): ?>
                <script>
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: <?= json_encode($success) ?>,
                    showConfirmButton: false,
                    timer: 1800
                });
                </script>
            <?php endif; ?>

            <form action="change_password.php" method="POST">
                <div class="mb-4">
                    <label for="new_password" class="block text-lg font-medium text-gray-700">รหัสผ่านใหม่</label>
                    <input type="password" name="new_password" id="new_password" class="mt-1 p-3 w-full border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="กรอกรหัสผ่านใหม่" required>
                </div>
                <div class="mb-6">
                    <label for="confirm_password" class="block text-lg font-medium text-gray-700">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="mt-1 p-3 w-full border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ยืนยันรหัสผ่านใหม่" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg text-xl font-semibold hover:bg-blue-700 transition duration-300 transform hover:scale-105">บันทึกรหัสผ่านใหม่</button>
            </form>
        </div>
    </div>

    <footer class="w-full text-center text-white text-xs mt-8 mb-2">
        <p>&copy; <?=date('Y')?> <?php echo htmlspecialchars($pageConfig['nameschool']); ?>. All rights reserved. | <?php echo htmlspecialchars($pageConfig['footerCredit']); ?></p>
    </footer>

    <script>
        AOS.init({
            duration: 1200,
            easing: 'ease-out-back',
        });
    </script>
</body>
</html>
