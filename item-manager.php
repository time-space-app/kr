<?php include_once __DIR__.'/core/theme/light/_header.php'; ?>
<!-- 물품관리 모듈 시작 -->
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
    textarea { height: 20vh; resize: vertical; }
    .btn-popup {
        background-color: #007bff;
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
        textarea { height: 30vh; }
    }
    /* 게시판 페이징 디자인 설정 */
    .pagination {
        display: flex; /* 기본 숨김 display: flex; */
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
    }
    /* 이전/다음 버튼 전용 */
    .pagination .nav-btn {
        font-weight: bold;
        background-color: #e5e5e5;
        color: #007bff;
        border: 1px solid #007bff;
        text-align: center;
    }
    .pagination .nav-btn:hover {
        background-color: #007bff;
        color: white;
        border: 1px solid #007bff;
    }
    .no-data { text-align:center; width: 100% !important; }
</style>
<script>
	function downloadFile(file_save_name) {
    	// 1. 임시 a 태그 생성
    	const link = document.createElement('a');
    	link.href = `/core/util/file-download.php?file_save_name=${encodeURIComponent(file_save_name)}`;
    	link.download = file_save_name; // 다운로드될 파일명 지정
    	// 2. 화면에 숨겨서 추가 (필수)
    	link.style.display = 'none';
    	document.body.appendChild(link);
    	// 3. 클릭 이벤트 발생 및 태그 제거
    	link.click();
    	document.body.removeChild(link);
    }
    // 버튼 클릭 시 자바스크립트 함수 호출
    function openItemForm(mode, id = null) {
        let url = "item-manager-pop.php?mode=" + mode;
        // 수정 모드일 경우 ID 추가 전달
        if (mode === 'edit' && id) {
            url += "&id=" + id;
        }
        // 새 창의 옵션 지정 (가로 800px, 세로 600px)
        const options = "width=800,height=600,scrollbars=yes,resizable=yes";
        // 새 창 열기
        var popup = window.open(url, "itemWindow", options);
        // 열린 창의 내용(DOM)이 모두 로드된 후 실행
        popup.onload = function() {
            // 팝업 내부의 실제 콘텐츠 크기 측정
            var newWidth = popup.document.body.scrollWidth;
            var newHeight = popup.document.body.scrollHeight;
            // 팝업 창 크기 조절
            popup.resizeTo(newWidth, newHeight);
        };
    }
    $(document).ready(function() {
        
    });
</script>
<?php
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
// 초기 테이블 생성
$sql = "CREATE TABLE IF NOT EXISTS item_manager (
      id INT AUTO_INCREMENT COMMENT '등록번호',
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
      item_status VARCHAR(255) NOT NULL DEFAULT '사용' COMMENT '사용여부',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용기기관리'
    ";
    $conn->query($sql);
?>
<!-- 검색어 -->
<style>
/* 검색창 전체 컨테이너를 Flexbox로 설정 */
.search-container {
    display: flex;
    width: 100%;
    max-width: 600px; /* 데스크톱에서 너무 길어지는 것을 방지 */
    margin: 0 auto;
}
/* 검색 입력창: 빈 공간을 모두 차지하도록 설정 */
.search-container input {
    flex: 1; /* 남은 공간을 채우며 유연하게 크기 변경 */
    padding: 10px 15px;
    font-size: 16px;
    border: 2px solid #ccc;
    border-right: none; /* 버튼과 맞닿는 부분 테두리 제거 */
    border-radius: 4px 0 0 4px;
    outline: none;
}
/* 입력창 포커스 시 테두리 색상 변경 */
.search-container input:focus {
    border-color: #2563eb;
}
/* 검색 버튼: 크기 고정 */
.search-container button {
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #2563eb;
    border: 2px solid #2563eb;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    white-space: nowrap; /* 버튼 텍스트 줄바꿈 방지 */
}
/* 모바일 등 작은 화면에서 디자인 조정 */
@media (max-width: 480px) {
    .search-container input {
        font-size: 14px;
    }
    .search-container button {
        padding: 10px 15px;
        font-size: 14px;
    }
}
</style>
<?php
    $keyword = $_GET['search'] ?? ''; // 사용자 입력 검색어
    $now_page = isset($_GET['now_page']) ? (int)$_GET['now_page'] : 1; // 현재 페이지 번호 가져오기 (기본값 1)
    //================ pageing 계산 code ======================================
    // 1 - 현재 페이지 설정
    if ($now_page < 1) $now_page = 1;
    // 2 - 블럭크기 설정
    $block_size = 3;
    // 3 - 각 블럭의 start 페이지 값을 설정한다
    if($now_page % $block_size == 0){
        $start_num = abs($now_page - $block_size + 1);    // 현재 페이지가 블럭의 마지막 페이지 일 경우 해당 블럭의 시작 페이지 번호를 정한다
    }else{
        $start_num = floor($now_page/$block_size)*$block_size + 1; // 현재페이지가 블럭의 마지막 페이지가 아닐경우 시작 페이지를 지정한다
    }
    // 4 - 각 블럭의 end 페이지 값을 설정한다
    $end_num = $start_num + $block_size - 1;
    // 5 - 카운터 쿼리호출 (마지막 페이지에서 존재하지 않는 페이지 숫자를 없애주기 위해 토탈레코드 숫자를 구한다 )
    $sql = "SELECT count(id) AS COUNT FROM item_manager WHERE item_user LIKE '%$keyword%'";
    $result = mysqli_query($conn, $sql);
    $row =$result->fetch_assoc();
    if($row) $total_rec = $row['COUNT'];
    // 6 - 한페이지당 보여줄 레코드 수 설정
    $recnum_per_page = 4; $start = 0;
    // 7 - 불러오기 쿼리문에서 시작레코드 숫자 지정
    if($now_page == 1){
        $st_limit = 0;
    }else{
        $st_limit = $now_page * $recnum_per_page - $recnum_per_page;
    }
    // 8 - 이전 블럭 설정
    $before_block = abs($start - 1);
    // 9 - 다음 블럭 설정
    $next_block = $end_num + 1;
?>
<form class="search-container" action="" method="GET">
    <input type="search" id="search" name="search" placeholder="검색어를 입력하세요.">
    <button type="submit">검색</button>
</form>
<div class="no-data"><span>검색된 전체 물품갯수 : <?php echo $total_rec ?> 개</span></div>
<!--//Mong9 Editor//-->
<div class="m9-grid-block">
<div class="m9-grid-1">
<div class="m9-column-1">
<div class="m9-user-background-1 m9-padding-1">
<ul class="m9-list-style-0 m9-float-2 m9-spacing-1 m-m9-float-1">
    <?php
    // 10 - 페이징 처리 쿼리 실행
    $sql = "SELECT * FROM item_manager WHERE item_user LIKE '%$keyword%' ORDER BY id DESC LIMIT $st_limit , $recnum_per_page";
    $res = mysqli_query($conn, $sql);
    if(mysqli_num_rows($res) > 0) { 
        while ($row = $res->fetch_assoc()) {
            // result 포인터를 초기화하여 첫 번째 필드부터 다시 가져옵니다
    		mysqli_field_seek($res, 0);
        ?>
    	<li>
    	<div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
    	<div class="display-table-cell vertical-align-top" style="width:100px">
    	<img src="/core/util/qr.php?param=<?php echo $row['id'] ?>" />
    	</div>
    	<div class="display-table-cell vertical-align-top" style="cursor:pointer" <?php if (isset($_SESSION['filemanager']['logged'])){ ?> onclick="openItemForm('edit',<?php echo $row['id'] ?>)" <?php } ?>>
    	<h3 class="font-weight-700 m9-f-large" style="margin-bottom:5px;"><?php echo $row['item_location'] ?></h3>
        	<div class="m9-f-small m9-font-color-3">
        	<?php 
        	$fieldValues = [];
        	while ($field = mysqli_fetch_field($res)) {
    		    $fieldName = $field->name; // 필드명
        		if($fieldName == 'id' || $fieldName == 'item_location' || $fieldName == 'item_price') {
        	        continue;
        	    }else{
        	        $fieldValues[] = $row[$fieldName]; // 필드 데이터
        	    }
        	}
        	$values_string = "'" . implode("', '", $fieldValues) . "'";
        	?>
            <?php echo $values_string; ?>
        	</div>
    	</div>
    	</div>
    	</li>
<?php }
	}else{ ?>
	<li class="no-data">
    	<div class="display-table width-100 m9-padding-1 m9-border background-color-white m9-round-3">
    	조회된 데이터가 없습니다.
    	</div>
    </li>
<?php } ?>
</ul>
</div>
<div class="pagination">
    <?php if($start_num > 1){ ?>
    <a class="nav-btn btn prev" href="<?php echo '?now_page='.$before_block.'&search='.$keyword?>">이전</a>
    <?php } ?>
    <?php
    for($i=$start_num; $i<=$end_num; $i++){ // 11 - 페이지 링크
        if(ceil($total_rec/$recnum_per_page) >= $i){
            if($now_page == $i){ ?>
                <span id="page" class="nav-btn btn" style="cursor:default;background-color: #007bff;color:#fff"><?php echo $i ?></span>
            <? }else{ ?>
                <a href="<?php echo '?now_page='.$i.'&search='.$keyword?>" onFocus="blur()" class="nav-btn btn"><?php echo $i?></a>      
            <?php }
        }
    }
    ?>     
    <?php if($end_num * $recnum_per_page < $total_rec){ ?>
        <a href="<?php echo '?now_page='.$next_block.'&search='.$keyword?>" class="nav-btn btn next">다음</a>
    <?php } ?>
    <?php if (isset($_SESSION['filemanager']['logged'])){ ?>
        <button type="button" class="btn btn-popup btn-submit" onclick="openItemForm('write')">개별등록창</button>
        <button type="button" class="btn btn-popup btn-list" onclick="openItemForm('csv')">csv일괄등록창</button>
        <button type="button" class="btn btn-popup btn-delete" onclick="openItemForm('delete_all')">전체삭제</button>
    <?php } ?>
</div>
</div>
</div>
</div>
<?php
    if ($conn !== null) {
		$conn->close(); // 1. 물리적 연결 닫기
		$conn = null;   // 2. 객체 참조 제거
	}
?>
<?php include_once __DIR__.'/core/theme/light/_footer.php'; ?>