<?php include_once __DIR__.'/core/theme/light/_header.php'; ?>
<!-- 물품관리 팝업창 시작 -->
<script src="https://code.jquery.com/jquery-latest.js"></script>
<style>
    /* 기본 스타일 */
    .board-container {margin: 0 auto; padding: 20px; }
    .list-header, .list-item { display: flex; padding: 10px; border-bottom: 1px solid #ddd; }
    .list-header { font-weight: bold; background-color: #f5f5f5; }
    .btn {
        color: white;
        padding: 10px 20px;
        margin-bottom: 4px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%; /* 모바일에서 버튼 꽉 차게 */
    }
    /* 글쓰기,수정 폼+반응형 설정 */
    input[type="text"], textarea {
        width: 100%;
        padding: 10px;
        box-sizing: border-box; /* 패딩 포함 너비 계산 */
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn-submit {
        background-color: #007bff;
    }
    .btn-edit, .edit-item {
        background-color: #0b57d0;
    }
    .btn-delete {
        background-color: #FF0000;
    }
    .btn-close {
        background-color: #808080;
    }
    .btn-list {
        background-color: #808080;
    }
    @media (min-width: 768px) {
        .btn { width: auto; } /* 데스크톱에서는 버튼 크기 자동 */
    }
    .no-data { text-align:center; width: 100% !important; }
</style>
<script>
$(document).ready(function() {
	const button = $('#updateForm .btn-delete');
	button.click(function(event) {
		if (confirm("정말로 삭제하시겠습니까?") == true) {
			event.preventDefault(); // 기본 동작 방지
			// 1. input 요소의 값을 'delete'로 변경
			const modeElements = $('#updateForm input[name="mode"]');
			// 2. 전송 전 mode 값 변경
            modeElements.val('delete');
			// 3. 폼 전송
			$('#updateForm').submit();
			// 4. 전송 후 mode 값 변경
			modeElements.val('update');
		}
	});
});
</script>
<?php
function maskName($name) {
    $length = mb_strlen($name, 'UTF-8'); // 이름의 총 길이 계산
    if ($length <= 2) {
        // 2자 이하인 경우 첫 글자만 남기고 두 번째 글자 마스킹
        $masked = mb_substr($name, 0, 1, 'UTF-8') . '*';
    } else {
        // 3자 이상인 경우 가운데 글자들을 *로 마스킹
        $startStr = mb_substr($name, 0, 1, 'UTF-8'); // 첫 글자
        $endStr = mb_substr($name, -1, 1, 'UTF-8');  // 마지막 글자
        // 가운데 길이만큼 * 생성
        $masked = $startStr . str_repeat('*', $length - 2) . $endStr;
    }
    return $masked;
}
?>
<?php
    $mode = $_GET['mode'] ?? ''; //입력, 수정, 삭제
    $id = $_GET['id'] ?? ''; //입력, 수정, 삭제
	//$response = ['result' => 'fail', 'message' => $mode.'s'.$_POST['id']];
	//echo json_encode($response);
	//exit;
	$response = ['result' => 'fail', 'message' => '알 수 없는 오류'];
    // 설정 파일 불러오기
    $env = include_once __DIR__ . '/core/env.php';
    // 클라우드 서버변수인 $_ENV를 사용하고, 없다면 로컬 서버변수를 사용(아래)
    $servername = $_ENV['DB_HOST'] ?? $env['DB_HOST'];
    $username = $_ENV['DB_USER'] ?? $env['DB_USER'];
    $password = $_ENV['DB_PASS'] ?? $env['DB_PASS'];
    $dbname = $_ENV['DB_NAME'] ?? $env['DB_NAME'];
    // DB 연결
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8");
?>
<?php
switch ($mode) {
?>
<?php
	case 'view':
?>
    <form action="" method="post" id="viewForm">
        <input type="hidden" id="mode" name="mode" value="view">
        <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
        <div class="m9-grid-block">
        <div class="m9-grid-1">
        <div class="m9-column-1">
        <div class="m9-user-background-1 m9-padding-1">
        <ul class="m9-list-style-0 m9-float-3 m9-spacing-1 m-m9-float-1">
        <?php
        $sql = "SELECT * FROM item_manager WHERE id = ?";
        $stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		?>
		    <?php 
				while ($field = mysqli_fetch_field($result)) {
				$fieldName = $field->name; // 필드명
				$fieldValue = $row[$fieldName]; // 필드 데이터
				
			?>
    			<?php if($fieldName == 'id') { ?>
    			<?php }elseif($fieldName == 'item_user') { ?>
    			<li>
    			    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                    [<?php echo substr($fieldName,5) ?>]:
                    <?php echo maskName($fieldValue) ?>
                    </div>
                </li>
    			<?php }else{ ?>
    		    <li>
    		    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                    [<?php echo substr($fieldName,5) ?>]:
                    <?php echo $fieldValue?>
                </div>
    			</li>
    			<?php } ?>
		<?php } ?>
        </ul>
        </div>
        </div>
        </div>
        </div>
    </form>
<?php
    $stmt->close();
    break;
?>
<?php
	case 'edit':
?>
    <form action="/core/api/item-manager-api.php" method="post" id="updateForm">
        <input type="hidden" id="mode" name="mode" value="update">
        <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
        <div class="m9-grid-block">
        <div class="m9-grid-1">
        <div class="m9-column-1">
        <div class="m9-user-background-1 m9-padding-1">
        <ul class="m9-list-style-0 m9-float-3 m9-spacing-1 m-m9-float-1">
        <?php
        $sql = "SELECT * FROM item_manager WHERE id = ?";
        $stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		?>
		    <?php 
				while ($field = mysqli_fetch_field($result)) {
				$fieldName = $field->name; // 필드명
				$fieldValue = $row[$fieldName]; // 필드 데이터
				
			?>
    			<?php if($fieldName == 'id') { ?>
    			<li>
    			    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                    <label for="<?php echo $fieldName?>"><?php //echo $row['Comment'] ?></label>
                    <input type="text" value="<?php echo $fieldValue?>" readonly style="border:0">
                    </div>
                </li>
    			<?php }else{ ?>
    		    <li>
    		    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                    <label for="<?php echo $fieldName?>"><?php //echo $row['Comment'] ?></label>
                    <input type="text" id="<?php echo $fieldName?>" name="<?php echo $fieldName?>" value="<?php echo $fieldValue?>">
                </div>
    			</li>
    			<?php } ?>
		<?php } ?>
        </ul>
        </div>
        </div>
        </div>
        </div>
        <?php if (isset($_SESSION['filemanager']['logged'])){ ?>
        <div class="view-actions no-data">
        <button type="submit" class="btn btn-submit">수정하기</button>
        <button type="button" class="btn btn-delete">삭제하기</button>
        </div>
        <?php } ?>
    </form>
<?php
    $stmt->close();
    break;
?>
<?php
	case 'delete_all':
?>
    <form action="/core/api/item-manager-api.php" method="post" id="delForm"" style="height:250px;">
        <input type="hidden" id="mode" name="mode" value="deleteAll">
        <input type="hidden" id="id" name="id" value="<?php echo $_SESSION['filemanager']['logged'] ?>">
        <div class="m9-grid-block">
        <div class="m9-grid-1">
        <div class="m9-column-1">
        <div class="m9-user-background-1 m9-padding-1">
        <ul class="m9-list-style-0 m9-float-1 m9-spacing-1 m-m9-float-1">
		    <li>
		    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                주의 전체 삭제시 데이터 복구가 불가능 합니다. 주의하시기 바랍니다. 정말로 전체 삭제 하시겠습니까?
            </div>
			</li>
        </ul>
        </div>
        </div>
        </div>
        </div>
        <div class="view-actions no-data">
        <button type="submit" class="btn btn-submit">전체삭제</button>
        </div>
    </form>
<?php
    break;
?>
<?php
	case 'csv':
?>
    <form action="/core/api/item-manager-api.php" method="post" id="csvForm" enctype="multipart/form-data" style="height:250px;">
	<input type="hidden" id="mode" name="mode" value="csv">
        <div class="m9-grid-block">
        <div class="m9-grid-1">
        <div class="m9-column-1">
        <div class="m9-user-background-1 m9-padding-1">
        <ul class="m9-list-style-0 m9-float-1 m9-spacing-1 m-m9-float-1">
		    <li>
		    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                <label for="excel_file">CSV파일만 업로드 가능</label>
                <input type="file" name="excel_file" id="excel_file" required>
            </div>
			</li>
        </ul>
        </div>
        </div>
        </div>
        </div>
        <div class="view-actions no-data">
        <button type="submit" class="btn btn-submit">CSV파일 일괄 업로드</button>
        <a href="/item-list-sample.csv" class="btn btn-submit" download>CSV파일 샘플 다운로드</a>
        </div>
    </form>
<?php
    break;
?>
<?php
	case 'write':
?>
    <form action="/core/api/item-manager-api.php" method="post" id="boardForm">
        <input type="hidden" id="mode" name="mode" value="add">
        <div class="m9-grid-block">
        <div class="m9-grid-1">
        <div class="m9-column-1">
        <div class="m9-user-background-1 m9-padding-1">
        <ul class="m9-list-style-0 m9-float-3 m9-spacing-1 m-m9-float-1">
        <?php 
			$sql = "SHOW FULL COLUMNS FROM item_manager";
			$result = $conn->query($sql);
			while($row = $result->fetch_assoc()){
		?>
		    <?php
				if($row['Field'] == 'id') {
				    continue;
			?>
			<?php }else{ ?>
		    <li>
		    <div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
                <label for="<?php echo $row['Field']?>"><?php echo $row['Comment'] ?></label>
                <input type="text" id="<?php echo $row['Field']?>" name="<?php echo $row['Field']?>" value="">
            </div>
			<?php } ?>
			</li>
        <?php } ?>
        </ul>
        </div>
        </div>
        </div>
        </div>
        <div class="view-actions no-data">
        <input type="hidden" id="id" name="id" value="">
        <button type="submit" class="btn btn-submit">등록하기</button>
        </div>
    </form>
<?php
    break;
?>
<?php
}
if ($conn !== null) {
	$conn->close(); // 1. 물리적 연결 닫기
	$conn = null;   // 2. 객체 참조 제거
}
?>
<?php include_once __DIR__.'/core/theme/light/_footer.php'; ?>