<?php
session_start();
if (!isset($_SESSION['filemanager']['logged'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/core/tinyfilemanager.php';</script>";
    exit; // 스크립트 실행 중단
}
phpinfo();
?>
