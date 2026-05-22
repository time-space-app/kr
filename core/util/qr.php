<?php
include dirname(__DIR__).'/api/phpqrcode.php';  // PHP QR Code 라이브러리를 불러옵니다.

// URL 파라미터 검사 및 처리
if (isset($_GET['param'])) {
    // 사용자 입력이므로 반드시 검증을 거쳐야 합니다!
    $param = $_GET['param'];
    // 스크립트가 아무것도 출력하지 않도록 해야 합니다!!!
    // 그렇지 않으면 PNG 바이너리 파일이 손상될 수 있습니다!
    ob_start();
    $codeText = 'http://localhost/news-letter.php?param='.$param; // 여기에 데이터베이스 요청
    $debugLog = ob_get_contents();
    ob_end_clean(); // 여기서 처리가 종료됩니다
    // DB와 연동된 내용이 아니고 직접 정보 보여줄때$item_location = '교무실'; // 설치장소
    $item_user = '김일국'; // 사용자명
    $item_location = '교무실'; // 설치장소
    $item_manager = '교무실'; // 관리자
    $item_no = '25101701-00000000'; // 식별번호/물품목록번호
    $item_id = 'M000000000'; // 관리번호
    $item_type = '노트북'; // 기종
    $item_model = 'NT750QCJ-K01G'; // 모델명
    $item_cpu = 'Core i5 10210U(1.6GHz)'; // CPU사양
    $item_ram = '8GB'; // RAM사양
    $item_ssd = '500GB'; // HDD사양
    $item_os = 'Windows 11'; // OS사양
    $item_maker = '삼성전자'; // 제조사
    $item_ip = '10.0.0.0'; // IP주소
    $item_date = '20250331'; // 구입일/취득일
    $item_price = '1000000'; // 구입단가
    $item_useful = '9'; // 내용연수
    $item_status = "사용중"; // 사용/폐기
    // 데이터 통합
    $email = 'kimilguk@yahoo.co.kr';
    $subject = $item_user.'님의사용기기정보';
    $body = '
    '.$item_user.'-사용자명
    '.$item_location.'-설치장소
    '.$item_manager.'-관리자
    '.$item_no.'-식별번호
    '.$item_id.'-관리번호
    '.$item_type.'-기종
    '.$item_model.'-모델명
    '.$item_cpu.'-CPU사양
    '.$item_ram.'-RAM사양
    '.$item_ssd.'-SSD사양
    '.$item_os.'-OS사양
    '.$item_maker.'-제조사
    '.$item_ip.'-IP주소
    '.$item_date.'-구입일
    '.$item_price.'-구입단가
    '.$item_useful.'-내용연수
    '.$item_status.'-사용여부';
    // 메일 내용 조립
    $codeContents = 'mailto:'.$email.'?subject='.urlencode($subject).'&body='.urlencode($body);
    header('Content-Type: image/png');  // 콘텐츠 타입을 PNG로 설정 후 직접 출력할 때
    $pixel = 1; // 픽셀크기
    $pattern = [QR_ECLEVEL_L, QR_ECLEVEL_M, QR_ECLEVEL_Q, QR_ECLEVEL_H]; // 패턴 복잡도 L 이 가장단순하며 인쇄시 유리함.
    $margin = 4; // 외부 프레임 여백
    // 이미지를 PNG 스트림 형식으로 브라우저에 직접 출력합니다.
    QRcode::png($codeContents, null, $pattern[0], $pixel, $margin);  // 사이즈 330 QR 코드 생성 및 출력
} else {
    echo "URL 파라미터로 외부에서 img태그에 출력할 때 샘플.(아래)<br>";
    $ourParam = 1234;
    echo '<img src="qr.php?param='.$ourParam.'" />';
}
?>