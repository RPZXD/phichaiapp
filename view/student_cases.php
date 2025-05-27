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
                <span class="text-4xl">🏥</span> เคสดูแลช่วยเหลือนักเรียน
            </h1>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">เคสทั้งหมด</h2>
                    <button class="bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-lg font-bold shadow hover:from-primary-dark hover:to-secondary-dark transition">
                        <i class="fas fa-plus mr-2"></i>สร้างเคสใหม่
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200">เคสทั้งหมด</h3>
                        <p class="text-xl font-bold text-blue-900 dark:text-blue-100">89</p>
                    </div>
                    <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">รอดำเนินการ</h3>
                        <p class="text-xl font-bold text-yellow-900 dark:text-yellow-100">23</p>
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-orange-800 dark:text-orange-200">กำลังดำเนินการ</h3>
                        <p class="text-xl font-bold text-orange-900 dark:text-orange-100">34</p>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-green-800 dark:text-green-200">เสร็จสิ้น</h3>
                        <p class="text-xl font-bold text-green-900 dark:text-green-100">28</p>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">เร่งด่วน</h3>
                        <p class="text-xl font-bold text-red-900 dark:text-red-100">4</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700">
                                <th class="border px-4 py-2 text-left">เลขที่เคส</th>
                                <th class="border px-4 py-2 text-left">นักเรียน</th>
                                <th class="border px-4 py-2 text-left">ประเภทปัญหา</th>
                                <th class="border px-4 py-2 text-left">ผู้รับผิดชอบ</th>
                                <th class="border px-4 py-2 text-left">สถานะ</th>
                                <th class="border px-4 py-2 text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="border px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-clipboard-list text-4xl mb-2"></i>
                                    <br>กำลังโหลดข้อมูลเคส...
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
