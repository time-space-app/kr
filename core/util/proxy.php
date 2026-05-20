<?php
/*
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // 주석 건너뜀
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}
loadEnv(dirname(__DIR__) . '/env.php');
$apiKey = getenv('GOOGLE_API_KEY'); // .env에서 변수 값 로드
*/
// 설정 파일 불러오기
$env = require dirname(__DIR__) . '/env.php';
// 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
$apiKey = $_ENV['GOOGLE_API_KEY'] ?? $env['GOOGLE_API_KEY'];
$query = $_GET['callback']; //호출하는 곳의 자바스크립트 렌더링 함수
$url = "https://maps.googleapis.com/maps/api/js?key=".$apiKey."&callback=".htmlspecialchars($query);
// 요청 전달 및 결과 반환
echo file_get_contents($url);
?>