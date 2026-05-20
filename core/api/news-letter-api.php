<?php
session_start();
include_once __DIR__.'/secure-api.php';
try {
	header('Content-Type: application/json'); // JSON 응답 설정
	$mode = $_POST['mode'] ?? ''; //입력, 수정, 삭제
	//$response = ['result' => 'fail', 'message' => $mode.'s'.$_POST['id']];
	//echo json_encode($response);
	//exit;
	$response = ['result' => 'fail', 'message' => '알 수 없는 오류'];
    // 설정 파일 불러오기
    $env = include_once dirname(__DIR__) . '/env.php';
    // 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
    $servername = $_ENV['DB_HOST'] ?? $env['DB_HOST'];
    $username = $_ENV['DB_USER'] ?? $env['DB_USER'];
    $password = $_ENV['DB_PASS'] ?? $env['DB_PASS'];
    $dbname = $_ENV['DB_NAME'] ?? $env['DB_NAME'];
	$uploadDir = $_ENV['UPLOAD_DIR'] ?? $env['UPLOAD_DIR']; // 첨부파일 경로 설정
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0755, true);
	}
    // 1. DB 연결 및 초기 테이블 생성
    $conn = new mysqli($servername, $username, $password, $dbname);
    $sql = "CREATE TABLE IF NOT EXISTS news_letter (
      id INT AUTO_INCREMENT COMMENT '게시글 번호',
      title VARCHAR(255) NOT NULL COMMENT '제목',
      content TEXT NOT NULL COMMENT '내용',
      file_name VARCHAR(255) DEFAULT NULL COMMENT '업로드 시 파일명',
      file_save_name VARCHAR(255) DEFAULT NULL COMMENT '실제 저장된 파일명',
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
			if (isset($_POST['title']) && !empty($_POST['title']) && isset($_SESSION['filemanager']['logged'])) {
				$title = $_POST['title'];
				$content = $_POST['content'];
				$title = $conn->real_escape_string($title);
				$content = $conn->real_escape_string($content);
				$fileName = null;
				$fileSaveName = null;
				// 파일 업로드 처리
				if ($_FILES['upload_file']['name']) {
					//$uploadDir = "board_upload/";
					// 파일명 중복 피하기 위해 고유 아이디 사용 추천
					$fileName = basename($_FILES["upload_file"]["name"]);
					$ext = pathinfo($fileName, PATHINFO_EXTENSION);
					$file_ext = strtolower($ext);
					$fileSaveName = date('YmdHis') . "_" . uniqid() . "." . $ext; // 저장 경로
					// 파일 업로드
					if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $uploadDir . $fileSaveName)) {
						// 성공적으로 저장됨
					} else {
						$response = ['result' => 'fail', 'message' => '파일업로드 오류가 발생 되었습니다.'];
					}
				}
				$stmt = $conn->prepare("INSERT INTO news_letter (title, content, file_name, file_save_name, writer) VALUES (?, ?, ?, ?, ?)");
				$stmt->bind_param("sssss", $title, $content, $fileName, $fileSaveName, $_SESSION['filemanager']['logged']);
				$stmt->execute();
				if ($stmt->affected_rows > 0) {
					$response = ['result' => 'success', 'message' => '글이 작성되었습니다.'];
				} else {
					$response = ['result' => 'fail', 'message' => '글이 작성되지 않았습니다.'];
				}
				$stmt->close();
			}else{
				$response = ['result' => 'fail', 'message' => '글의 입력값이 올바르지 않습니다.'];
			}
			break;
		case 'edit':
			// 2. 글 수정 처리 (POST)
			if (isset($_POST['title']) && !empty($_POST['title']) && isset($_SESSION['filemanager']['logged'])) {
				$id = $_POST['id']; // 게시글 번호
				$title = $_POST['title'];
				$content = $_POST['content-edit'];
				$title = $conn->real_escape_string($title);
				$content = $conn->real_escape_string($content);
				$fileName = null;
				$fileSaveName = null;
				// 파일 업로드 처리
				if ($_FILES['upload_file']['name']) {
					//$uploadDir = "board_upload/";
					// 파일명 중복 피하기 위해 고유 아이디 사용 추천
					$fileName = basename($_FILES["upload_file"]["name"]);
					$ext = pathinfo($fileName, PATHINFO_EXTENSION);
					$file_ext = strtolower($ext);
					$fileSaveName = date('YmdHis') . "_" . uniqid() . "." . $ext; // 저장 경로
					// 파일 업로드
					if (move_uploaded_file($_FILES["upload_file"]["tmp_name"], $uploadDir . $fileSaveName)) {
						//기존파일이 있다면 삭제 시작
						$sql = "SELECT * FROM news_letter WHERE id = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
						$result = $stmt->get_result();
						$row = $result->fetch_assoc();
						// 서버에 실제 파일이 존재하는지 확인하고 삭제합니다.
						if ($result->num_rows > 0) {
							$oldFileSaveName = $row['file_save_name'];
							if (file_exists($uploadDir . $oldFileSaveName)) {
								unlink($uploadDir . $oldFileSaveName); // 실제 서버 파일 삭제
							}
						}
						$stmt->close();
						//기존파일이 있다면 삭제 끝
						// 성공적으로 저장됨
						$sql = "UPDATE news_letter SET title = ?, content = ?, file_name = ?, file_save_name = ? WHERE id = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("ssssi", $title, $content, $fileName, $fileSaveName, $id);
						$stmt->execute();
					} else {
						$response = ['result' => 'fail', 'message' => '파일업로드 오류가 발생 되었습니다.'];
					}
					
				}else{
					$sql = "UPDATE news_letter SET title = ?, content = ? WHERE id = ?";
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("ssi", $title, $content, $id);
					$stmt->execute();
				}
				//header("Location: index.php"); // 페이지 새로고침
				//exit;
				if ($stmt->affected_rows > 0) {
					$response = ['result' => 'success', 'message' => '글이 수정되었습니다.'];
				} else {
					$response = ['result' => 'fail', 'message' => '글이 수정되지 않았습니다.'];
				}
				$stmt->close();
			}else{
				$response = ['result' => 'fail', 'message' => '글의 입력값이 올바르지 않습니다.'];
			}
			break;
		case 'view':
			if (isset($_POST['id']) && !empty($_POST['id'])) {
				$id = $_POST['id']; // 게시글 번호
				$sql = "SELECT * FROM news_letter WHERE id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				// 모든 배열 값의 HTML 태그 변환 (XSS 방지)
				array_walk_recursive($row, function (&$value) {
					// 문자열 타입인 경우에만 htmlspecialchars 적용
					if (is_string($value)) {
						$value = safeHtmlOutput($value);
					}
				});
				$response = ['result' => $row, 'message' => '글이 로드되었습니다.'];
				$sql = "UPDATE news_letter SET view_count = view_count + 1 WHERE id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->close();
			}else{
				$response = ['result' => 'fail', 'message' => '글의 입력값이 올바르지 않습니다.'];
			}
			break;
		case 'delete':
			if (isset($_POST['id']) && !empty($_POST['id']) && isset($_SESSION['filemanager']['logged'])) {
				$id = $_POST['id']; // 게시글 번호
				/* 뉴스레터에서는 사용하지 않음.
				$sql = "SELECT * FROM news_letter WHERE id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				// 서버에 실제 파일이 존재하는지 확인하고 삭제합니다.
				if ($result->num_rows > 0) {
					$fileSaveName = $row['file_save_name'];
					if (file_exists($uploadDir . $fileSaveName)) {
						unlink($uploadDir . $fileSaveName); // 실제 서버 파일 삭제
					}
				}
				$stmt->close();
				*/
				$sql = "DELETE FROM news_letter WHERE id = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("i", $id);
				$stmt->execute();
				if ($stmt->affected_rows > 0) {
					$response = ['result' => 'success', 'message' => '글이 삭제되었습니다.'];
				} else {
					$response = ['result' => 'fail', 'message' => '글이 삭제되지 않았습니다.'];
				}
				$stmt->close();
			}else{
				$response = ['result' => 'fail', 'message' => '글의 입력값이 올바르지 않습니다.'];
			}
			break;
		default:
			// 3. 글 목록 불러오기
			$keyword = $_GET['search'] ?? ''; // 사용자 입력 검색어
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 번호 가져오기 (기본값 1)
			if ($page < 1) $page = 1;
			$itemsPerPage = 4; // 한 페이지에 보여줄 개수
			$offset = ($page - 1) * $itemsPerPage; //OFFSET 계산: (현재페이지 - 1) * 4
			$sql = "SELECT * FROM news_letter WHERE title LIKE ? ORDER BY id DESC LIMIT $itemsPerPage OFFSET $offset";
			$stmt = $conn->prepare($sql);
			$search_param = "%" . $keyword . "%";
			$stmt->bind_param("s", $search_param);
			$stmt->execute();
			$result = $stmt->get_result();
			$posts = array();
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					// 모든 배열 값의 HTML 태그 변환 (XSS 방지)
					array_walk_recursive($row, function (&$value) {
						// 문자열 타입인 경우에만 htmlspecialchars 적용
						if (is_string($value)) {
							$value = safeHtmlOutput($value);
						}
					});
					$posts[] = $row;
				}
				$response = ['result' => $posts, 'message' => '글이 로드되었습니다.', 'page' => $page];
			} else {
				$response = ['result' => $posts, 'message' => '등록된 글이 없습니다.', 'page' => $page];
			}
			$stmt->close();
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