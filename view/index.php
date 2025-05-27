<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}
require_once('header.php');
require_once('wrapper.php');
?>
<div class="content-wrapper min-h-screen ">
    <section class="content p-8 ">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-6 flex items-center gap-2">
                <span class="text-4xl">🛡️</span> แดชบอร์ด
            </h1>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                <p class="text-lg text-gray-700 dark:text-gray-200 mb-2">สวัสดี <span class="font-semibold text-primary dark:text-yellow-400"><?php echo htmlspecialchars($_SESSION['user']['fullname'] ?? $_SESSION['user']['username']); ?></span></p>
                <p class="text-gray-500 dark:text-gray-400">คุณเข้าสู่ระบบในฐานะ <span class="font-semibold text-primary dark:text-yellow-400"><?php echo htmlspecialchars(ucfirst($_SESSION['user']['role'])); ?></span></p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="../logout.php" class="inline-block bg-gradient-to-r from-red-500 to-yellow-400 text-white px-5 py-2 rounded-lg font-bold shadow hover:from-red-600 hover:to-yellow-500 transition">ออกจากระบบ</a>
                    <?php if (strtolower($_SESSION['user']['role']) === 'admin'): ?>
                        <a href="users.php" class="inline-block bg-gradient-to-r from-primary to-secondary text-white px-5 py-2 rounded-lg font-bold shadow hover:from-primary-dark hover:to-secondary-dark transition">จัดการผู้ใช้งาน</a>
                        <a href="reports.php" class="inline-block bg-gradient-to-r from-green-500 to-blue-400 text-white px-5 py-2 rounded-lg font-bold shadow hover:from-green-600 hover:to-blue-500 transition">รายงาน</a>
                    <?php endif; ?>
                    <?php if (in_array(strtolower($_SESSION['user']['role']), ['admin','teacher','hod','vp','director'])): ?>
                        <a href="students.php" class="inline-block bg-gradient-to-r from-yellow-500 to-pink-400 text-white px-5 py-2 rounded-lg font-bold shadow hover:from-yellow-600 hover:to-pink-500 transition">ข้อมูลนักเรียน</a>
                        <a href="student_cases.php" class="inline-block bg-gradient-to-r from-purple-500 to-indigo-400 text-white px-5 py-2 rounded-lg font-bold shadow hover:from-purple-600 hover:to-indigo-500 transition">กรณี/ช่วยเหลือนักเรียน</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once('../footer.php'); ?>
<?php require_once('script.php'); ?>
</body>
</html>

