<?php
session_start();
// 설정 파일 불러오기
$env = include_once dirname(__DIR__) . '/env.php';
// 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
$servername = $_ENV['DB_HOST'] ?? $env['DB_HOST'];
$username = $_ENV['DB_USER'] ?? $env['DB_USER'];
$password = $_ENV['DB_PASS'] ?? $env['DB_PASS'];
$dbname = $_ENV['DB_NAME'] ?? $env['DB_NAME'];
// DB 연결
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");
// 엑셀 파일을 CSV로 변환 후 업로드해야 한다.
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK && isset($_POST['mode']) && isset($_SESSION['filemanager']['logged'])) {
    $mode = mysqli_real_escape_string($conn, $_POST['mode']);
    if($mode == 'csv') {
        try {
    		$fileTmpPath = $_FILES['excel_file']['tmp_name'];
    		$fileName = $_FILES['excel_file']['name'];
    		$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    		// CSV 파일인지 검증
    		if ($fileExtension !== 'csv') {
    			echo "<script>alert('CSV 파일만 업로드 가능합니다.'); history.back();</script>";
    			die("CSV 파일만 업로드 가능합니다.");
    		}
    		// 3. CSV 파일 읽기
    		$file = fopen($fileTmpPath, 'r');
    		// 2번째 행부터 데이터 읽기 (1번째 행은 헤더인 경우)
    		fgetcsv($file, 0, ",", "\"", "\\");
            while (($row = fgetcsv($file, 0, ",", "\"", "\\")) !== false) {
                // 각 셀의 데이터 추출
    			$fieldValues = [];
    			for($i=1;$i<=17;$i++){
    				$rowValue = mb_convert_encoding($row[$i], 'UTF-8', 'EUC-KR');
    				$fieldValues[] = $rowValue; 
    			}
                $sql = "SHOW COLUMNS FROM item_manager";
    			$result = $conn->query($sql);
    			$fields = [];
    			while($row = $result->fetch_assoc()){
    				if($row['Field'] != 'id') {
    					$fields[] = $row['Field']; // 'Field' 키에 필드명이 담겨 있습니다.
    				}
    			}
    			if (empty(array_filter($fieldValues)) || trim(implode('', $fieldValues)) == '') {
    				continue; // 값이 없거나 공백뿐이면 건너뛰기
    			}
    			$field_string = implode(", ", $fields);
    			$values_string = "'" . implode("', '", $fieldValues) . "'";
    			$sql = "INSERT INTO item_manager ($field_string) VALUES ($values_string)";
    			mysqli_query($conn, $sql);
            }
            echo "<script>alert('일괄 업로드 및 저장이 완료되었습니다.'); if(window.opener)window.opener.location.reload();window.close()</script>";
        } catch(Exception $e) {
            echo "CSV 파일 읽기 실패: ", $e->getMessage();
    		echo "<script>alert('CSV 파일 읽기 실패'); history.back();</script>";
    		exit;
        }
    	$conn->close();
    	exit;
    }
}else{
    echo "CSV 파일 등록 실패-관리자만 이용가능합니다.";
}
if (isset($_POST['id']) && isset($_POST['mode']) && isset($_SESSION['filemanager']['logged'])) {
	$mode = mysqli_real_escape_string($conn, $_POST['mode']);
	switch ($mode) {
		case 'add':
			$sql = "SHOW COLUMNS FROM item_manager";
			$result = $conn->query($sql);
			$fields = [];
			$fieldValues = [];
			while($row = $result->fetch_assoc()){
				if($row['Field'] != 'id') {
					$fields[] = $row['Field']; // 'Field' 키에 필드명이 담겨 있습니다.
					$fieldName = $row['Field'];
					$fieldValues[] = mysqli_real_escape_string($conn, $_POST[$fieldName]);
				}
			}
			$field_string = implode(", ", $fields);
			$values_string = "'" . implode("', '", $fieldValues) . "'";
			$sql = "INSERT INTO item_manager ($field_string) VALUES ($values_string)";
			$result = mysqli_query($conn, $sql);
			if ($result) {
				echo "<script>alert('등록되었습니다.'); if(window.opener)window.opener.location.reload();window.close()</script>";
			} else {
				echo "<script>alert('등록 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			break;
		case 'update':
			$id = mysqli_real_escape_string($conn, $_POST['id']);	
			$sql = "SELECT * FROM item_manager WHERE id = '$id'";
			$res = mysqli_query($conn, $sql);
			if ($res && $res->num_rows > 0) { 
				$sql = "UPDATE item_manager SET ";
				while ($field = mysqli_fetch_field($res)) {
					$fieldName = $field->name; // 필드명
					if($fieldName != 'id') {
						$fieldValue = mysqli_real_escape_string($conn, $_POST[$fieldName]);
						$sql .=	"$fieldName = '$fieldValue',";
					}
				}
				$sql = rtrim($sql, ", "); // 오른쪽 끝의 ", " 제거
				$sql .=	" WHERE id = $id";
				$result = mysqli_query($conn, $sql);
				if ($result) {
					echo "<script>alert('수정되었습니다.'); if(window.opener)window.opener.location.reload();window.close()</script>";
				} else {
					echo "<script>alert('수정 실패: " . mysqli_error($conn) . "'); history.back();</script>";
				}
			}else{
				echo "<script>alert('수정 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			break;
		case 'delete':
			$id = mysqli_real_escape_string($conn, $_POST['id']);	
			$sql = "DELETE FROM item_manager WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $id);
			$stmt->execute();
			if ($stmt->affected_rows > 0) {
				echo "<script>alert('삭제되었습니다.'); if(window.opener)window.opener.location.reload();window.close()</script>";
			} else {
				echo "<script>alert('삭제 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			$stmt->close();
			break;
		case 'deleteAll':	
			$sql = "TRUNCATE TABLE item_manager";
			$result = mysqli_query($conn, $sql);
			if ($result) {
				echo "<script>alert('모두 삭제되었습니다.'); if(window.opener)window.opener.location.reload();window.close()</script>";
			} else {
				echo "<script>alert('모두 삭제 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			$stmt->close();
			break;
	}
	if ($conn !== null) {
		$conn->close(); // 1. 물리적 연결 닫기
		$conn = null;   // 2. 객체 참조 제거
	}
}else{
    echo "CRUD 작업 실패-관리자만 이용가능합니다.";
}
?>
