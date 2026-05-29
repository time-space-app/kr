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
$remote_version_url = 'https://raw.githubusercontent.com/time-space-app/kr-update/refs/heads/main/version.json';
$local_version = '1.0'; // 현재 내 사이트 버전
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remote_version_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
//echo $output; //서버에서 allow_url_fopen 을 지원해 줄 때는 위 6줄이 필요없이 아래 주석된 코드만 있으면 된다.
$remote_info = json_decode($output, true); // file_get_contents($remote_version_url)대신 다른코드 사용
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $core_check = $_POST['core_check'] ?? '';
    // 코어체크 전송과 최신 버전이 있다면 new 표시를 위해 1값 리턴 후 끝내기
    if (!empty($core_check) && $remote_info['version'] > $local_version) {		
		echo "1|v" . $local_version. "|v" . $remote_info['version'];
	}else{
		echo "0|v" . $local_version;
	}
	exit;
}
if ($remote_info['version'] > $local_version) {
    echo "새로운 버전(" . $remote_info['version'] . ")이 있습니다. 업데이트를 시작합니다.<br>";
    
    $tar_file = 'update.tar.gz';
    $download_url = $remote_info['download_url'];
    // cURL 사용 시작
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $download_url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); // 결과를 문자열로 반환
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 검증 생략 (필요시)
    curl_setopt($ch2, CURLOPT_TIMEOUT, 10); // 타임아웃 설정 (10초)
    $response = curl_exec($ch2);
    if(curl_errno($ch2)){
        echo 'cURL 에러: ' . curl_error($ch2);
    }
    curl_close($ch2); // fopen()을 사용하지 못할 때 대신 위 cURL 사용
    // 2. 파일 다운로드 (기존 코드 유지)
	file_put_contents($tar_file, $response); // fopen($download_url, 'r') 대신
	echo "파일 다운로드 완료.<br>";
	// 3. tar.gz 압축 해제 및 덮어쓰기 (PharData 사용)
	try {
		// .tar.gz 파일인 경우
		$phar = new PharData($tar_file);
		
		// extractTo(대상경로, 파일들, 덮어쓰기여부)
		// 덮어쓰기를 위해 세 번째 인자를 true로 설정
		$phar->extractTo(dirname(__DIR__), null, true); 
		
		echo "Update Success(업데이트 성공)! (기존 ".$local_version." 버전에서 -> " . $remote_info['version'] . " 버전으로 업데이트됨.)<br>";
		echo "<a href='/core/tinyfilemanager.php'>관리자 페이지로 이동</a>";
		// 3. 압축 파일 삭제
		unlink($tar_file);

	} catch (Exception $e) {
		echo "Failed to extract update file(업데이트 파일 추출에 실패했습니다.): " . $e->getMessage() . "<br>";
		echo "<a href='/core/tinyfilemanager.php'>관리자 페이지로 이동</a>";
	}
} else {
    echo "It is currently the latest version, so it will not be updated.(현재 최신 버전이므로 더 이상 업데이트되지 않습니다. Current version: " . $local_version . ")<br>";
    echo "<a href='/core/tinyfilemanager.php'>관리자 페이지로 이동</a>";
}
?>
