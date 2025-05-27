<?php
session_start();
require_once('controllers/AuthController.php');

// Check if user is forced to change password
$isForced = isset($_GET['force']) && $_GET['force'] == '1';
$tempUser = $_SESSION['temp_user'] ?? null;

// If not forced change and user is not logged in, redirect to login
if (!$isForced && !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// If forced change but no temp user, redirect to login
if ($isForced && !$tempUser) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    // Validation
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'กรุณากรอกรหัสผ่านใหม่และยืนยันรหัสผ่าน';
    } elseif (strlen($newPassword) < 6) {
        $error = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน';
    } else {
        $auth = new AuthController();
        
        // For forced change, use temp user ID and don't require current password
        if ($isForced) {
            $userId = $tempUser['user_id'];
            $result = $auth->changePassword($userId, $newPassword);
        } else {
            // For normal change, require current password
            if (empty($currentPassword)) {
                $error = 'กรุณากรอกรหัสผ่านปัจจุบัน';
            } else {
                $userId = $_SESSION['user']['user_id'];
                $result = $auth->changePassword($userId, $newPassword, $currentPassword);
            }
        }
        
        if (isset($result) && $result['success']) {
            if ($isForced) {
                // Clear temp user and redirect to login
                unset($_SESSION['temp_user']);
                $_SESSION['password_changed'] = true;
                header('Location: login.php?changed=1');
                exit();
            } else {
                $success = $result['message'];
            }
        } else if (isset($result)) {
            $error = $result['message'];
        }
    }
}

require_once('header.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - ระบบโรงเรียนพิชัยวิทยาคม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Kanit', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                    <i class="fas fa-key text-3xl text-blue-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">
                    <?php echo $isForced ? 'ต้องเปลี่ยนรหัสผ่าน' : 'เปลี่ยนรหัสผ่าน'; ?>
                </h1>
                <?php if ($isForced): ?>
                    <p class="text-blue-100">
                        <i class="fas fa-info-circle mr-2"></i>
                        ผู้ใช้: <strong><?php echo htmlspecialchars($tempUser['username']); ?></strong>
                    </p>
                    <p class="text-yellow-200 text-sm mt-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        คุณต้องเปลี่ยนรหัสผ่านก่อนเข้าใช้งานระบบ
                    </p>
                <?php else: ?>
                    <p class="text-blue-100">เปลี่ยนรหัสผ่านของคุณเพื่อความปลอดภัย</p>
                <?php endif; ?>
            </div>

            <!-- Alert Messages -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-6">
                <?php if (!$isForced): ?>
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-white mb-2">
                            <i class="fas fa-lock mr-2"></i>รหัสผ่านปัจจุบัน
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                               placeholder="กรอกรหัสผ่านปัจจุบัน">
                    </div>
                <?php endif; ?>

                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-key mr-2"></i>รหัสผ่านใหม่
                    </label>
                    <input type="password" id="new_password" name="new_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                           placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)" minlength="6">
                    <p class="text-xs text-blue-200 mt-1">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-check mr-2"></i>ยืนยันรหัสผ่านใหม่
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                           placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
                </div>

                <!-- Show Password Checkbox -->
                <div class="flex items-center">
                    <input type="checkbox" id="show_passwords" class="mr-2">
                    <label for="show_passwords" class="text-sm text-white">แสดงรหัสผ่าน</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    เปลี่ยนรหัสผ่าน
                </button>
            </form>

            <!-- Footer -->
            <?php if (!$isForced): ?>
                <div class="mt-6 text-center">
                    <a href="view/index.php" class="text-blue-200 hover:text-white transition-colors duration-300">
                        <i class="fas fa-arrow-left mr-2"></i>กลับสู่หน้าหลัก
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Show/Hide Password Toggle
        document.getElementById('show_passwords').addEventListener('change', function() {
            const passwordFields = [
                'current_password',
                'new_password', 
                'confirm_password'
            ];
            
            passwordFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.type = this.checked ? 'text' : 'password';
                }
            });
        });

        // Password Confirmation Validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('รหัสผ่านไม่ตรงกัน');
                this.style.borderColor = '#ef4444';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });

        // New Password Length Validation
        document.getElementById('new_password').addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                this.setCustomValidity('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                this.style.borderColor = '#ef4444';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });
        const togglePasswordButton = document.getElementById('togglePassword');
const eyeIcon = document.getElementById('eyeIcon');

togglePasswordButton.addEventListener('click', () => {
  const isPassword = passwordInput.type === 'password';
  passwordInput.type = isPassword ? 'text' : 'password';
  eyeIcon.setAttribute('d', isPassword
      ? 'M12 4.5c-4.477 0-8.268 2.943-9.542 7 .274.837.68 1.613 1.196 2.296M15.536 15.536A9.953 9.953 0 0112 17c-4.477 0 8.268-2.943 9.542-7a9.953 9.953 0 01-1.196-2.296M9.88 9.88a3 3 0 014.24 4.24' // Eye open path
      : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.837-.68 1.613-1.196 2.296M15.536 15.536A9.953 9.953 0 0112 17c-4.477 0-8.268-2.943-9.542-7a9.953 9.953 0 011.196-2.296M9.88 9.88a3 3 0 014.24 4.24' // Eye closed path
  );
});
    </script>
</body>
</html>

<?php require_once('script.php'); ?>
</body>
</html>