<?php
//kimilguk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['pwd'] ?? '';
    // 결과 반환 (JSON 등)
    if (!empty($password)) {
		// Generate secure hash (BCrypt default)
		$hash = password_hash($password, PASSWORD_DEFAULT);
		echo $hash;
		// Return hash wrapped in JSONP callback
		//echo '(' . json_encode($hash) . ')';
	}
}
?>
