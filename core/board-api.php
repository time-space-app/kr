<?php
session_start();
try {
	header('Content-Type: application/json'); // JSON 응답 설정
	$mode = $_POST['mode'] ?? ''; //입력, 수정, 삭제
	//$response = ['result' => 'fail', 'message' => $mode.'s'.$_POST['id']];
	//echo json_encode($response);
	//exit;
	$response = ['result' => 'fail', 'message' => '알 수 없는 오류'];
    // 설정 파일 불러오기
    $env = include_once __DIR__ . '/env.php';
    // 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
    $servername = $_ENV['DB_HOST'] ?? $env['DB_HOST'];
    $username = $_ENV['DB_USER'] ?? $env['DB_USER'];
    $password = $_ENV['DB_PASS'] ?? $env['DB_PASS'];
    $dbname = $_ENV['DB_NAME'] ?? $env['DB_NAME'];
    // 1. DB 연결 및 초기 테이블 생성
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "CREATE TABLE IF NOT EXISTS board (
      id INT AUTO_INCREMENT COMMENT '게시글 번호',
      title VARCHAR(255) NOT NULL COMMENT '제목',
      content TEXT NOT NULL COMMENT '내용',
      file_name VARCHAR(255) DEFAULT NULL COMMENT '저장된 파일명',
      file_path VARCHAR(255) DEFAULT NULL COMMENT '실제 파일 저장 경로',
      writer VARCHAR(255) NOT NULL COMMENT '작성자',
      reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '작성일',
      view_count INT DEFAULT 0 COMMENT '조회수',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판'
    ";
    $conn->query($sql);
	switch ($mode) {
		case 'insert':
			// 2. 글 작성 처리 (POST)
			if (isset($_POST['title']) && isset($_SESSION['filemanager']['logged'])) {
				$title = $_POST['title'];
				$content = $_POST['content'];
				$title = $conn->real_escape_string($title);
				$content = $conn->real_escape_string($content);
				$fileName = null;
				$filePath = null;
				// 파일 업로드 처리
				if ($_FILES['upload_file']['name']) {
					$targetDir = "board_upload/";
					// 파일명 중복 피하기 위해 고유 아이디 사용 추천
					$fileName = basename($_FILES["upload_file"]["name"]);
					$ext = pathinfo($fileName, PATHINFO_EXTENSION);
					$file_ext = strtolower($ext);
					$filePath = $targetDir . date('YmdHis') . "_" . uniqid() . "." . $ext; // 저장 경로
					// 파일 업로드
					if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $filePath)) {
						// 성공적으로 저장됨
					} else {
						$response = ['result' => 'fail', 'message' => '파일업로드 오류가 발생 되었습니다.'];
					}
				}
				$stmt = $conn->prepare("INSERT INTO board (title, content, file_name, file_path, writer) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param("sssss", $title, $content, $fileName, $filePath, $_SESSION['filemanager']['logged']);
				$stmt->execute();
				//header("Location: index.php"); // 페이지 새로고침
				//exit;
			}
			$response = ['result' => 'success', 'message' => '글이 작성되었습니다.'];
			break;
		case 'view':
			$sql = "SELECT * FROM board WHERE id =".$_POST['id'];
			$result = $conn->query($sql);
			$row = mysqli_fetch_assoc($result);
			$response = ['result' => $row, 'message' => '글이 로드되었습니다.'];
			break;
		default:
			// 3. 글 목록 불러오기
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 번호 가져오기 (기본값 1)
			if ($page < 1) $page = 1;
			$itemsPerPage = 5; // 한 페이지에 보여줄 개수
			$offset = ($page - 1) * $itemsPerPage; //OFFSET 계산: (현재페이지 - 1) * 5
			$sql = "SELECT * FROM board ORDER BY id DESC LIMIT $itemsPerPage OFFSET $offset";
			$result = $conn->query($sql);
			$posts = array();
			while($row = mysqli_fetch_assoc($result)) {
				$posts[] = $row;
			}
			$response = ['result' => $posts, 'message' => '글이 로드되었습니다.', 'page' => $page];
			break;
	}
	if ($conn !== null) {
		$conn->close(); // 1. 물리적 연결 닫기
		$conn = null;   // 2. 객체 참조 제거
	}
	echo json_encode($response);
} catch(Exception $e) {
    //echo $e->getMessage(); //DB 연결 미 작업에러 발생 시 페이지 상단에 No such file or directory 가 표시
	echo json_encode($response);
}
?>