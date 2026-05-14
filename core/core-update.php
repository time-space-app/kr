<?php
session_start();
if (!isset($_SESSION['filemanager']['logged'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='tinyfilemanager.php';</script>";
    exit; // 스크립트 실행 중단
}
/*
 업데이트 하기 전 현재 core 파일의 사용자가 웹-데이터 사용자로 변경되어 있어야 덮어 쓰기가 가능 합니다. 보통은 FTP로 업로드 하시면 필요없고, 로컬의 가상머신에서 작업 시 ssh로 접속 후 아래 명령이 필요합니다. 
 chown -R www-data:www-data /var/www/html
 */
// 1. 최신 버전 정보 가져오기
$remote_version_url = 'https://raw.githubusercontent.com/time-space-app/kr-update/refs/heads/master/version.json';
$local_version = '0.9'; // 현재 내 사이트 버전

$remote_info = json_decode(file_get_contents($remote_version_url), true);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $core_check = $_POST['core_check'] ?? '';
    // 코어체크 전송과 최신 버전이 있다면 new 표시를 위해 1값 리턴 후 끝내기
    if (!empty($core_check) && $remote_info['version'] > $local_version) {		
		echo 1;
	}else{
		echo 0;
	}
	exit;
}
if ($remote_info['version'] > $local_version) {
    echo "새로운 버전(" . $remote_info['version'] . ")이 있습니다. 업데이트를 시작합니다.<br>";
    
    $tar_file = 'update.tar.gz';
    $download_url = $remote_info['download_url'];

    // 2. 파일 다운로드 (기존 코드 유지)
	file_put_contents($tar_file, fopen($download_url, 'r'));
	echo "파일 다운로드 완료.<br>";

	// 3. tar.gz 압축 해제 및 덮어쓰기 (PharData 사용)
	try {
		// .tar.gz 파일인 경우
		$phar = new PharData($tar_file);
		
		// extractTo(대상경로, 파일들, 덮어쓰기여부)
		// 덮어쓰기를 위해 세 번째 인자를 true로 설정
		$phar->extractTo(__DIR__, null, true); 
		
		echo "Update Success! (Updated version: " . $remote_info['version'] . ")<br>";
		echo "<a href='tinyfilemanager.php'>To main screen</a>";
		// 3. 압축 파일 삭제
		unlink($tar_file);

	} catch (Exception $e) {
		echo "Failed to extract update file: " . $e->getMessage() . "<br>";
		echo "<a href='tinyfilemanager.php'>To main screen</a>";
	}
} else {
    echo "It is currently the latest version, so it will not be updated.(Current version: " . $local_version . ")<br>";
    echo "<a href='tinyfilemanager.php'>To main screen</a>";
}
?>
