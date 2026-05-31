<?php
include_once dirname(__DIR__) . '/util/Parsedown.php'; //마크다운 형식으로 변환

// 1. API 키 및 설정
// 설정 파일 불러오기
$env = include_once dirname(__DIR__) . '/env.php';
// 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
$apiKey = $_ENV['API_KEY'] ?? $env['API_KEY'];
$model = $_ENV['API_MODEL'] ?? $env['API_MODEL']; // 사용할 모델명
// 2. 마크다운 파일 읽기
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
if($host == "time-space.kr") $protocol = "https://";
$rootUrl = $protocol . $host;
$mdFilePath = $rootUrl ."/README.md";
//$mdFilePath = dirname(dirname(__DIR__)) ."/README.md"; // file_get_contents을 사용가능하다면 위 5줄 코드는 필요없다.
$systemInstruction = "";
//if (file_exists($mdFilePath)) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $mdFilePath);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 결과를 문자열로 반환
    curl_setopt($ch, CURLOPT_HEADER, false);        // 헤더 정보 포함 안 함
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);          // 타임아웃 시간(초)
    $output = curl_exec($ch);
    curl_close($ch);
    $systemInstruction = $output;
    //$systemInstruction = iconv('UTF-8', 'UTF-8//IGNORE', $output);
    //$systemInstruction = file_get_contents($mdFilePath); // file_get_contents을 사용가능하다면 위 curl 사용코드는 필요없다.
//}
// 3. 요청 URL (API 키를 URL 쿼리 파라미터로 포함)
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $apiKey;
// 4. 요청 데이터
$prompt = '빌더의 이름을 알려줘.';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prompt = isset($_POST['message']) ? $_POST['message'] : '';
}
//$data = json_decode(file_get_contents('php://input'), true); // file_get_contents을 사용가능하다면 위 4줄 코드는 필요없다.
//$prompt = isset($data['message']) ? $data['message'] : '';
if (empty($prompt)) {
    echo json_encode(["error" => "질문을 입력해주세요."]);
    exit;
}
$data = [
    "systemInstruction" => [
        "parts" => [
            "text" => "당신은 제공된 문서를 기반으로 답변하는 챗봇입니다. 다음 문서를 참고하여 질문에 답변하세요.\n\n" . $systemInstruction
        ]
    ],
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
];
$payload = json_encode($data);
// 5. 스트림 옵션 설정
$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => $payload,
    ],
];
$context = json_encode($data);
//$context  = stream_context_create($options);// file_get_contents을 사용가능하다면 위 1줄 코드는 필요없다.
// 6. API 호출 및 응답 받기
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true); // POST 방식으로 설정
curl_setopt($ch2, CURLOPT_POSTFIELDS, $context); // 전송할 데이터
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($context)
]);
$result = curl_exec($ch2);
curl_close($ch2);
//$result = file_get_contents($url, false, $context); // file_get_contents을 사용가능하다면 위 11줄 코드는 필요없다.
// 7. 결과 처리
if ($result === FALSE) {
    echo "API 호출 실패";
} else {
    // 응답받은 JSON 문자열을 연관 배열로 디코딩
    $responseArray = json_decode($result, true);
    
    // 생성된 텍스트 출력
    if (isset($responseArray['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $responseArray['candidates'][0]['content']['parts'][0]['text'];
        $parsedown = new Parsedown();
        // 마크다운을 HTML로 안전하게 변환
        $html_output = $parsedown->text($reply); 
        echo json_encode(["reply" => $html_output]);
    } else {
        echo json_encode(["reply" => "답변을 생성하지 못했습니다."]);
    }
}
?>
