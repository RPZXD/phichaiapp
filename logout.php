<?php
session_start();
// ลบ session ทั้งหมด
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
// ลบ JWT cookie ถ้ามี
setcookie('jwt', '', time() - 3600, '/');
// redirect ไป login พร้อม toast
header('Location: login.php?logout=1');
exit();
