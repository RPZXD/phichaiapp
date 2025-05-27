<?php
ob_start(); // Start output buffering
require_once('header.php');
require_once(__DIR__ . '/controllers/AuthController.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $username = trim($_POST['txt_username_email'] ?? '');
    $password = trim($_POST['txt_password'] ?? '');
    $auth = new AuthController();
    $result = $auth->login($username, $password);
    if ($result['success']) {
        // Check if force password change is required
        if (isset($result['force_password_change']) && $result['force_password_change']) {
            $_SESSION['temp_user'] = [
                'user_id' => $result['user_id'],
                'username' => $result['username']
            ];
            header('Location: change_password.php?force=1');
            exit();
        }
        
        $_SESSION['jwt'] = $result['token'];
        $_SESSION['user'] = $result['user'];
        $role = strtolower($result['user']['role']);
        // Toast success
        echo '<div id="toast-success" class="fixed top-20 right-4 z-[9999] flex items-center w-full max-w-xs p-4 mb-4 text-gray-900 bg-green-100 rounded-lg shadow dark:text-gray-200 dark:bg-green-800 animate-fade-in">
        <svg class="w-6 h-6 text-green-500 dark:text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <div class="ml-3 text-sm font-normal">เข้าสู่ระบบสำเร็จ กำลังเปลี่ยนหน้า...</div></div>';
        // redirect ทุก role ไปหน้าเดียวกัน (index.php)
        $redirectUrl = 'view/index.php';
        echo '<script>setTimeout(function(){ window.location.href = "' . $redirectUrl . '"; }, 1200);</script>';
        exit();
    } else {
        $loginError = $result['message'];
    }
}

if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    echo '<div id="toast-logout" class="fixed top-20 right-4 z-[9999] flex items-center w-full max-w-xs p-4 mb-4 text-gray-900 bg-yellow-100 rounded-lg shadow dark:text-gray-200 dark:bg-yellow-800 animate-fade-in">
        <svg class="w-6 h-6 text-yellow-500 dark:text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
        <div class="ml-3 text-sm font-normal">ออกจากระบบเรียบร้อยแล้ว</div></div>';
    echo '<script>setTimeout(function(){ var t=document.getElementById("toast-logout"); if(t)t.remove(); }, 2500);</script>';
}
?>
<body class="hold-transition sidebar-mini layout-fixed bg-gradient-to-br from-primary-light via-white to-secondary-light min-h-screen">
<div class="wrapper">
    <?php require_once('wrapper.php');?>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"></h1>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="row flex items-center justify-center min-h-[70vh] mt-5 bg-transparent">
                    <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 border border-primary animate-fade-in">
                        <h2 class="text-3xl font-extrabold text-center text-primary mb-6 flex items-center justify-center gap-2 animate-fade-in-down">
                            <span class="text-4xl">🔐</span> เข้าสู่ระบบ
                        </h2>
                        <?php if (!empty($loginError)): ?>
                            <div id="toast-error" class="fixed top-20 right-4 z-[9999] flex items-center w-full max-w-xs p-4 mb-4 text-gray-900 bg-red-100 rounded-lg shadow dark:text-gray-200 dark:bg-red-800 animate-fade-in">
                                <svg class="w-6 h-6 text-red-500 dark:text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <div class="ml-3 text-sm font-normal"><?= htmlspecialchars($loginError) ?></div>
                                <button type="button" onclick="this.parentElement.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-red-100 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8 dark:bg-red-800 dark:text-red-200 dark:hover:bg-red-700" aria-label="Close">
                                    <span class="sr-only">Close</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </button>
                            </div>
                            <script>setTimeout(function(){ var t=document.getElementById('toast-error'); if(t)t.remove(); }, 3500);</script>
                        <?php endif; ?>
                        <form action="" method="POST" class="space-y-5">
                            <div>
                                <label class="block text-gray-600 mb-1 font-semibold flex items-center gap-1">👤 ชื่อผู้ใช้งาน</label>
                                <input type="text" name="txt_username_email" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200 shadow-sm" placeholder="กรุณากรอกชื่อผู้ใช้งาน...">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1 font-semibold flex items-center gap-1">🔑 รหัสผ่าน</label>
                                <input type="password" name="txt_password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none transition-all duration-200 shadow-sm" placeholder="กรุณากรอกรหัสผ่าน...">
                            </div>
                            <button type="submit" name="signin" class="w-full bg-gradient-to-r from-primary to-secondary text-white p-3 rounded-lg hover:from-primary-dark hover:to-secondary-dark shadow-lg font-bold text-lg flex items-center justify-center gap-2 transition-all duration-200 animate-bounce-in">
                                🚀 เข้าสู่ระบบ
                            </button>
                        </form>
                        <div class="mt-6 text-center text-gray-400 text-xs animate-fade-in">
                            © <?= date('Y') ?> โรงเรียนพิชัย | ระบบดูแลช่วยเหลือนักเรียน
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php require_once('footer.php');?>
</div>
<?php require_once('script.php'); ?>
</body>
</html>