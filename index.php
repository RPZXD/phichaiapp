<?php 
// Read configuration from JSON file
$config = json_decode(file_get_contents('config.json'), true);
$global = $config['global'];

require_once('header.php');

?>
<body class="hold-transition sidebar-mini layout-fixed light-mode">
<div class="wrapper">

    <?php require_once('wrapper.php');?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

  <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?php echo $global['nameschool']; ?></h1>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <section class="content">
  <div class="flex flex-col items-center justify-center min-h-[60vh] bg-gradient-to-br from-blue-50 via-white to-indigo-100 rounded-lg shadow-2xl p-10 mx-auto max-w-2xl border border-blue-200">
    <div class="text-7xl mb-4 animate-bounce">🎓✨</div>
    <h2 class="text-4xl font-extrabold text-indigo-700 mb-4 text-center drop-shadow-lg tracking-wide">
      ระบบบริหารงานทั่วไป
    </h2>
    <p class="text-lg text-gray-700 mb-6 text-center">
      <span class="inline-block animate-pulse">👋</span>
      ยินดีต้อนรับสู่ระบบบริหารงานทั่วไป
      <span class="font-semibold text-blue-700"><?php echo $global['nameschool']; ?></span>!
      <br>
      <span class="text-base text-gray-500">ระบบนี้ประกอบด้วยฟังก์ชันหลัก:</span>
    </p>
    <ul class="list-none w-full max-w-md mb-6 space-y-4">
      <li class="flex items-center bg-white rounded-lg shadow hover:shadow-lg transition p-4 border-l-4 border-blue-400 group">
        <span class="text-3xl mr-4 group-hover:scale-125 transition-transform">📋</span>
        <div>
          <span class="font-bold text-blue-700">แจ้งซ่อม</span>
          <span class="block text-gray-600 text-sm">แจ้งปัญหาและติดตามสถานะการซ่อมแซม</span>
        </div>
      </li>
      <li class="flex items-center bg-white rounded-lg shadow hover:shadow-lg transition p-4 border-l-4 border-indigo-400 group">
        <span class="text-3xl mr-4 group-hover:scale-125 transition-transform">🏢</span>
        <div>
          <span class="font-bold text-indigo-700">จองห้องประชุม</span>
          <span class="block text-gray-600 text-sm">ตรวจสอบและจองห้องประชุมได้อย่างสะดวก</span>
        </div>
      </li>
      <li class="flex items-center bg-white rounded-lg shadow hover:shadow-lg transition p-4 border-l-4 border-green-400 group">
        <span class="text-3xl mr-4 group-hover:scale-125 transition-transform">🚗</span>
        <div>
          <span class="font-bold text-green-700">จองรถ</span>
          <span class="block text-gray-600 text-sm">จองรถสำหรับภารกิจต่าง ๆ ของโรงเรียน</span>
        </div>
      </li>
    </ul>
    <div class="mb-8">
      <img src="dist/img/logo-phicha.png" alt="<?php echo $global['nameschool']; ?> Logo"
        class="max-h-32 mx-auto rounded-full shadow-lg border-4 border-indigo-200 hover:scale-105 transition-transform duration-300">
    </div>
    <a href="login.php"
      class="inline-block bg-gradient-to-r from-blue-600 to-indigo-500 hover:from-indigo-600 hover:to-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:scale-105 transition-all duration-200 text-xl tracking-wide animate-pulse">
      🚀 เริ่มต้นใช้งาน
    </a>
    <div class="mt-8 text-center text-gray-400 text-xs animate-fade-in">
      <span class="mr-1">🤝</span> Powered by General Management System <span class="ml-1">🎉</span>
    </div>
  </div>
</section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
    <?php require_once('footer.php');?>
</div>
<!-- ./wrapper -->


<script>

</script>
<?php require_once('script.php');?>
</body>
</html>
