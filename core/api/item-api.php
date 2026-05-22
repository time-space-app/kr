<?php
$conn = new mysqli("db", "myuser", "mypassword", "mydatabase");
$conn->set_charset("utf8");
// 엑셀 파일을 CSV로 변환 후 업로드해야 한다.
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
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
            $sql = "SHOW COLUMNS FROM school_manager";
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
			$sql = "INSERT INTO school_manager ($field_string) VALUES ($values_string)";
			mysqli_query($conn, $sql);
        }
        echo "<script>alert('일괄 업로드 및 저장이 완료되었습니다.'); location.href='/item-manager.php';</script>";
    } catch(Exception $e) {
        echo "CSV 파일 읽기 실패: ", $e->getMessage();
		exit;
		echo "<script>alert('CSV 파일 읽기 실패'); history.back();</script>";
    }
	$conn->close();
	exit;
} else {
    //echo "<script>alert('파일이 업로드되지 않았습니다.'); history.back();</script>";
}
if (isset($_POST['id']) && isset($_POST['mode'])) {
	$mode = mysqli_real_escape_string($conn, $_POST['mode']);
	switch ($mode) {
		case 'add':
			$sql = "SHOW COLUMNS FROM school_manager";
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
			$sql = "INSERT INTO school_manager ($field_string) VALUES ($values_string)";
			$result = mysqli_query($conn, $sql);
			if ($result) {
				echo "<script>alert('등록되었습니다.'); location.href='/item-manager.php';</script>";
			} else {
				echo "<script>alert('등록 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			break;
		case 'update':
			$id = mysqli_real_escape_string($conn, $_POST['id']);	
			$sql = "SELECT * FROM school_manager WHERE id = '$id'";
			$res = mysqli_query($conn, $sql);
			if ($res && $res->num_rows > 0) { 
				$sql = "UPDATE school_manager SET ";
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
					echo "<script>alert('수정되었습니다.'); location.href='/item-manager.php';</script>";
				} else {
					echo "<script>alert('수정 실패: " . mysqli_error($conn) . "'); history.back();</script>";
				}
			}else{
				echo "<script>alert('수정 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			break;
		case 'delete':
			$id = mysqli_real_escape_string($conn, $_POST['id']);	
			$sql = "DELETE FROM school_manager WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $id);
			$stmt->execute();
			if ($stmt->affected_rows > 0) {
				echo "<script>alert('삭제되었습니다.'); location.href='/item-manager.php';</script>";
			} else {
				echo "<script>alert('삭제 실패: " . mysqli_error($conn) . "'); history.back();</script>";
			}
			$stmt->close();
			break;
		case 'deleteAll':	
			$sql = "TRUNCATE TABLE school_manager";
			$result = mysqli_query($conn, $sql);
			if ($result) {
				echo "<script>alert('모두 삭제되었습니다.'); location.href='/item-manager.php';</script>";
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
}
?>
