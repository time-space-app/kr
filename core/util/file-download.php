<?php
// 설정 파일 불러오기
$env = include_once dirname(__DIR__) . '/env.php';
// 1. 설정 및 검증
$uploadDir = $_ENV['UPLOAD_DIR'] ?? $env['UPLOAD_DIR']; // 첨부파일 경로 설정
$fileName = $_GET['file_save_name']; // 첨부파일 경로+다운로드할 파일명
// 폴더 탐색 공격 방지 (../ 등의 상위 경로 이동 문자 차단)
if (strpos($fileName, '..') !== false || strpos($fileName, '/') !== false) {
    die("잘못된 파일 접근입니다.");
}
$filePath = $uploadDir . $fileName;
// 2. 파일 존재 및 권한 확인
if (file_exists($filePath) && is_file($filePath)) {
    // 3. 브라우저별 한글 파일명 깨짐 방지-파일이름에 한글이 들어가지 않도록 했기 때문에 필요없음.
    /*
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $encodedName = urlencode($fileName);
    if (strpos($agent, 'MSIE') !== false || strpos($agent, 'Trident') !== false) {
        $encodedName = iconv('UTF-8', 'EUC-KR', $fileName);
    }
    */
    // 4. HTTP 헤더 설정
    header('Content-Type: application/octet-stream');
    header("Content-Length: ".filesize("$filePath"));
    header("Content-Disposition: attachment; filename=$fileName");
    header('Content-Transfer-Encoding: binary');
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    header('Expires: 0');
    // 5. 파일 전송
    $fp = fopen($filePath, "rb"); 
    fpassthru($fp);
    fclose($fp);
} else {
    echo "<script>alert('파일이 존재하지 않습니다.');history.back();</script>";
}
?>