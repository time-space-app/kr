<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>CSV파일 일괄 업로드 및 개별 수정</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        .file-box { margin-bottom: 20px; padding: 10px; background: #f9f9f9; }
    </style>
</head>
<body>
	<?php
        $conn = new mysqli("db", "myuser", "mypassword", "mydatabase");
		$sql = "CREATE TABLE IF NOT EXISTS school_manager (
		  id INT AUTO_INCREMENT COMMENT '글번호',
		  item_user VARCHAR(255) NULL COMMENT '사용자명',
		  item_location VARCHAR(255) NULL COMMENT '설치장소',
		  item_manager VARCHAR(255) NULL COMMENT '관리자',
		  item_no VARCHAR(255) NOT NULL DEFAULT (CONCAT(DATE_FORMAT(NOW(), '%Y%m%d%H'), '-', LPAD(FLOOR(RAND() * 10000), 8, '0'))) COMMENT '식별번호',
		  item_id VARCHAR(255) NULL COMMENT '관리번호',
		  item_type VARCHAR(255) NULL COMMENT '기종',
		  item_model VARCHAR(255) NULL COMMENT '모델명',
		  item_cpu VARCHAR(255) NULL COMMENT 'CPU사양',
		  item_ram VARCHAR(255) NULL COMMENT 'RAM사양',
		  item_ssd VARCHAR(255) NULL COMMENT 'SSD사양',
		  item_os VARCHAR(255) NULL COMMENT 'OS사양',
		  item_maker VARCHAR(255) NULL COMMENT '제조사',
		  item_ip VARCHAR(255) NULL COMMENT 'IP주소',
		  item_date VARCHAR(255) NULL COMMENT '구입일',
		  item_price VARCHAR(255) NULL COMMENT '구입단가',
		  item_useful VARCHAR(255) NULL COMMENT '내용연수',
		  item_status VARCHAR(255) NOT NULL DEFAULT '사용' COMMENT 'item_status',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용기기관리'
		";
		$conn->query($sql);
	?>
    <!-- 업로드 폼 -->
    <div class="file-box">
        <form name="upload_form" action="/core/api/item-api.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="excel_file" required>
            <button type="submit">CSV파일 일괄 업로드</button>
        </form>
    </div>
	<!-- DB 데이터 개별 등록 폼 -->
    <h2>개별 등록 폼</h2>
    <table>
        <tr>
            <?php 
			$sql = "SHOW FULL COLUMNS FROM school_manager";
			$result = $conn->query($sql);
			while($row = $result->fetch_assoc()){
			?>
			<th><?php echo $row['Comment'] ?></th>
			<?php } ?>
			<th>등록</th>
        </tr>
            <form name="add_form" action="/core/api/item-api.php" method="POST">
                <tr>
					<?php 
					$sql = "SHOW COLUMNS FROM school_manager";
					$result = $conn->query($sql);
					while($row = $result->fetch_assoc()){
						if($row['Field'] == 'id') {
					?>
						<td><input type="hidden" id="<?php echo $row['Field']?>" name="<?php echo $row['Field']?>" value=""></td>
						<?php }else{ ?>
						<td><input type="text" id="<?php echo $row['Field']?>" name="<?php echo $row['Field']?>" value=""></td>
						<?php } ?>
					<?php } ?>
					<input type="hidden" id="mode" name="mode" value="add">
                    <td><button type="submit">등록</button></td>
                </tr>
            </form>
    </table>
	
    <!-- DB 데이터 리스트 및 개별 수정 폼 -->
    <h2 style="display:inline-block;">등록된 데이터 목록</h2>
	<form name="deleteAll_form" action="/core/api/item-api.php" method="POST" style="display:inline-block;">
		<input type="hidden" name="id" value="deleteAll">
		<input type="hidden" name="mode" value="deleteAll">
		<button type="button" class="deleteAll">일괄 삭제</button>
	</form>
    <table>
        <tr>
            <?php 
			$sql = "SHOW FULL COLUMNS FROM school_manager";
			$result = $conn->query($sql);
			while($row = $result->fetch_assoc()){
			?>
			<th><?php echo $row['Comment'] ?></th>
			<?php } ?>
            <th>수정/삭제</th>
        </tr>
		<?php
        $sql = "SELECT * FROM school_manager ORDER BY id DESC";
        $res = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
			// result 포인터를 초기화하여 첫 번째 필드부터 다시 가져옵니다
			mysqli_field_seek($res, 0); 
        ?>
            <!-- 각 행을 개별 form으로 처리 -->
            <form name="update_form" action="/core/api/item-api.php" method="POST">
                <tr>
					<?php 
						while ($field = mysqli_fetch_field($res)) {
						$fieldName = $field->name; // 필드명
						$fieldValue = $row[$fieldName]; // 필드 데이터
						if($fieldName == 'id') {
					?>
						<td><input type="text" name="<?php echo $fieldName ?>" value="<?php echo $fieldValue ?>" readonly style="border:0"></td>
						<?php }else{ ?>
						<td><input type="text" name="<?php echo $fieldName ?>" value="<?php echo $fieldValue ?>"></td>
						<?php } ?>
                    <?php } ?>
					<input type="hidden" name="mode" value="update">
                    <td>
					<button type="submit">수정</button>
					<button type="button" class="btn-delete">삭제</button>
					</td>
                </tr>
            </form>
        <?php
        }
        ?>
    </table>
	<script>
		const button = document.querySelector('.btn-delete');
		button.addEventListener('click', function(event) {
			if (confirm("정말로 삭제하시겠습니까?") == true) {
				event.preventDefault(); // 기본 동작 방지
				// 1. input 요소의 값을 'delete'로 변경
				const modeElements = document.getElementsByName('mode');
				// 2. 반복문을 통해 값 일괄 변경
				for (let i = 0; i < modeElements.length; i++) {
					modeElements[i].value = 'delete'; 
				}
				document.querySelector('input[name="mode"]').value = 'delete';
				// 3. 폼 전송
				document.querySelector('form[name="update_form"]').submit();
				// 4. 전송 후 반복문을 통해 값 일괄 변경
				for (let i = 0; i < modeElements.length; i++) {
					modeElements[i].value = 'update'; 
				}
			}
		});
		const btnDel = document.querySelector('.deleteAll');
		btnDel.addEventListener('click', function(event) {
			if (confirm("정말로 전체를 삭제하시겠습니까?") == true) {
				event.preventDefault(); // 기본 동작 방지
				// 1. input 요소의 값을 'delete'로 변경
				const modeElements2 = document.getElementsByName('mode');
				// 2. 반복문을 통해 값 일괄 변경
				for (let i = 0; i < modeElements2.length; i++) {
					modeElements2[i].value = 'deleteAll'; 
				}
				// 3. 폼 전송
				document.querySelector('form[name="deleteAll_form"]').submit();
				// 4. 전송 후 반복문을 통해 값 일괄 변경
				for (let i = 0; i < modeElements2.length; i++) {
					modeElements2[i].value = 'update'; 
				}
			}
		});
	</script>
</body>
</html>