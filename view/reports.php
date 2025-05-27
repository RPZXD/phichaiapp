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
                <span class="text-4xl">📊</span> รายงานและสถิติ
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">ผู้ใช้งานทั้งหมด</h3>
                            <p class="text-3xl font-bold text-primary">1,547</p>
                        </div>
                        <div class="text-4xl text-blue-500">👥</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">นักเรียนทั้งหมด</h3>
                            <p class="text-3xl font-bold text-green-600">1,245</p>
                        </div>
                        <div class="text-4xl text-green-500">🎓</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">เคสดูแล</h3>
                            <p class="text-3xl font-bold text-yellow-600">89</p>
                        </div>
                        <div class="text-4xl text-yellow-500">🏥</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">การเข้าใช้วันนี้</h3>
                            <p class="text-3xl font-bold text-purple-600">234</p>
                        </div>
                        <div class="text-4xl text-purple-500">📈</div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">สถิติการใช้งานรายเดือน</h2>
                    <div class="h-64 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-line text-4xl mb-2"></i>
                            <p>กราฟสถิติการใช้งาน</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">การกระจายตัวของเคส</h2>
                    <div class="h-64 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <div class="text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-pie text-4xl mb-2"></i>
                            <p>กราฟแบ่งประเภทเคส</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-primary/30 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">กิจกรรมล่าสุด</h2>
                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-2xl mr-3">🔐</div>
                        <div>
                            <p class="text-gray-800 dark:text-gray-200">ผู้ใช้ admin เข้าสู่ระบบ</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">เมื่อ 5 นาทีที่แล้ว</p>
                        </div>
                    </div>
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-2xl mr-3">📝</div>
                        <div>
                            <p class="text-gray-800 dark:text-gray-200">สร้างเคสใหม่สำหรับนักเรียน รหัส 12345</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">เมื่อ 15 นาทีที่แล้ว</p>
                        </div>
                    </div>
                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-2xl mr-3">✅</div>
                        <div>
                            <p class="text-gray-800 dark:text-gray-200">ปิดเคสหมายเลข #2024-089</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">เมื่อ 1 ชั่วโมงที่แล้ว</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php require_once('footer.php'); ?>
<?php require_once('script.php'); ?>
</body>
</html>
