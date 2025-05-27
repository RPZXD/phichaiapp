<?php
session_start();
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'admin') {
    header('Location: ../login.php');
    exit();
}
require_once('header.php');
require_once('wrapper.php');
?>
<div class="content-wrapper min-h-screen bg-gradient-to-br from-primary-light via-white to-secondary-light dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <section class="content p-8">
        <div class="container mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-6 flex items-center gap-2">
                <span class="text-4xl">🎓</span> จัดการข้อมูลนักเรียน
            </h1>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">รายการนักเรียนทั้งหมด</h2>
                    <div class="flex gap-2">
                        <button class="bg-green-500 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-green-600 transition">
                            <i class="fas fa-upload mr-2"></i>นำเข้าข้อมูล
                        </button>
                        <button class="bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-lg font-bold shadow hover:from-primary-dark hover:to-secondary-dark transition">
                            <i class="fas fa-plus mr-2"></i>เพิ่มนักเรียน
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">นักเรียนทั้งหมด</h3>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">1,245</p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">กำลังศึกษา</h3>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">1,198</p>
                    </div>
                    <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">ต้องการดูแล</h3>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">47</p>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">เสี่ยงออกกลางคัน</h3>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">12</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="border px-4 py-2 text-left">รหัสนักเรียน</th>
                                <th class="border px-4 py-2 text-left">ชื่อ-นามสกุล</th>
                                <th class="border px-4 py-2 text-left">ชั้น</th>
                                <th class="border px-4 py-2 text-left">สถานะ</th>
                                <th class="border px-4 py-2 text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="border px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-graduation-cap text-4xl mb-2"></i>
                                    <br>กำลังโหลดข้อมูลนักเรียน...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once('footer.php'); ?>
<?php require_once('script.php'); ?>
</body>
</html>
