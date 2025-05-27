<?php 

require_once('header.php');
require_once('config/Setting.php');

date_default_timezone_set('Asia/Bangkok');
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $setting->getPageTitle(); ?></title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          animation: {
            shake: 'shake 1s infinite',
          },
          keyframes: {
            shake: {
              '0%, 100%': { transform: 'translateX(0)' },
              '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-5px)' },
              '20%, 40%, 60%, 80%': { transform: 'translateX(5px)' },
            },
          },
        },
      },
    }
  </script>
  <style>
    .animate-shake { animation: shake 1s infinite; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper bg-white dark:bg-gray-800 min-h-screen transition-colors duration-300">

  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-2xl font-bold flex items-center">🌟 ยินดีต้อนรับสู่ระบบสารสนเทศโรงเรียนพิชัย</h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container-fluid">
        <!-- Section: เลือกฝ่ายงาน 4 ฝ่าย -->
        <div class="container mx-auto mt-8 mb-8">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- ฝ่ายวิชาการ -->
            <a href="#" class="group block rounded-2xl shadow-xl bg-gradient-to-br from-indigo-400 to-indigo-600 dark:from-indigo-700 dark:to-indigo-900 text-white p-6 text-center transform transition hover:scale-105 hover:shadow-2xl hover:from-indigo-500 hover:to-indigo-700 duration-200">
              <div class="flex flex-col items-center">
                <span class="text-5xl mb-2 animate-bounce">📚</span>
                <div class="text-xl font-bold mb-1">ฝ่ายวิชาการ</div>
                <div class="text-sm opacity-80">ระบบสารสนเทศ, ตารางเรียน, ผลการเรียน</div>
              </div>
            </a>
            <!-- ฝ่ายกิจการนักเรียน -->
            <a href="#" class="group block rounded-2xl shadow-xl bg-gradient-to-br from-pink-400 to-pink-600 dark:from-pink-700 dark:to-pink-900 text-white p-6 text-center transform transition hover:scale-105 hover:shadow-2xl hover:from-pink-500 hover:to-pink-700 duration-200">
              <div class="flex flex-col items-center">
                <span class="text-5xl mb-2 animate-bounce">🎒</span>
                <div class="text-xl font-bold mb-1">ฝ่ายกิจการนักเรียน</div>
                <div class="text-sm opacity-80">ระบบเช็คชื่อ, พฤติกรรม, กิจกรรม</div>
              </div>
            </a>
            <!-- ฝ่ายบริหารทั่วไป -->
            <a href="#" class="group block rounded-2xl shadow-xl bg-gradient-to-br from-green-400 to-green-600 dark:from-green-700 dark:to-green-900 text-white p-6 text-center transform transition hover:scale-105 hover:shadow-2xl hover:from-green-500 hover:to-green-700 duration-200">
              <div class="flex flex-col items-center">
                <span class="text-5xl mb-2 animate-bounce">🏫</span>
                <div class="text-xl font-bold mb-1">ฝ่ายบริหารทั่วไป</div>
                <div class="text-sm opacity-80">งานธุรการ, บุคลากร, อาคารสถานที่</div>
              </div>
            </a>
            <!-- ฝ่ายงบประมาณ -->
            <a href="#" class="group block rounded-2xl shadow-xl bg-gradient-to-br from-yellow-400 to-yellow-600 dark:from-yellow-700 dark:to-yellow-900 text-white p-6 text-center transform transition hover:scale-105 hover:shadow-2xl hover:from-yellow-500 hover:to-yellow-700 duration-200">
              <div class="flex flex-col items-center">
                <span class="text-5xl mb-2 animate-bounce">💰</span>
                <div class="text-xl font-bold mb-1">ฝ่ายงบประมาณ</div>
                <div class="text-sm opacity-80">งบประมาณ, พัสดุ, การเงิน</div>
              </div>
            </a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('footer.php');?>
</div>
<!-- ./wrapper -->

<!-- DataTables CSS/JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.13.7/i18n/th.json"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require_once('script.php');?>
<script>
// Hamburger menu toggle
const hamburgerBtn = document.getElementById('hamburger-btn');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebar-overlay');
if(hamburgerBtn && sidebar && sidebarOverlay) {
  hamburgerBtn.addEventListener('click', () => {
    sidebar.classList.toggle('-translate-x-full');
    sidebarOverlay.classList.toggle('hidden');
  });
  sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    sidebarOverlay.classList.add('hidden');
  });
}
// Theme toggle
const themeToggle = document.getElementById('theme-toggle');
const htmlEl = document.documentElement;
if(themeToggle) {
  // Initial
  if(localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    htmlEl.classList.add('dark');
    themeToggle.checked = true;
  } else {
    htmlEl.classList.remove('dark');
    themeToggle.checked = false;
  }
  themeToggle.addEventListener('change', function() {
    if(this.checked) {
      htmlEl.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      htmlEl.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  });
}
</script>
</body>
</html>
