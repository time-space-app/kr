<?php
session_start();
try {
    // 1. 사용자의 DB 연결 정보 입력 및 초기 테이블 생성
    $servername = "db";
    $username = "myuser";
    $password = "mypassword";
    $dbname = "mydatabase";
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
    // 2. 글 작성 처리 (POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['title']) && isset($_SESSION['filemanager']['logged'])) {
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
                echo "파일 업로드 실패.";
            }
        }
        $stmt = $conn->prepare("INSERT INTO board (title, content, file_name, file_path, writer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $content, $fileName, $filePath, $_SESSION['filemanager']['logged']);
        $stmt->execute();
        header("Location: index.php"); // 페이지 새로고침
        exit;
    }
    // 3. 글 목록 불러오기
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 번호 가져오기 (기본값 1)
    if ($page < 1) $page = 1;
    $itemsPerPage = 5; // 한 페이지에 보여줄 개수
    $offset = ($page - 1) * $itemsPerPage; //OFFSET 계산: (현재페이지 - 1) * 5
    $sql = "SELECT * FROM board ORDER BY id DESC LIMIT $itemsPerPage OFFSET $offset";
    $result = $conn->query($sql);
    if ($conn !== null) {
        $conn->close(); // 1. 물리적 연결 닫기
        $conn = null;   // 2. 객체 참조 제거
    }
} catch(Exception $e) {
    //echo $e->getMessage(); //DB 연결 미 작업에러 발생 시 페이지 상단에 No such file or directory 가 표시
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width">
    <title>홈페이지에 오신것을 환영합니다.</title>
    <!-- 몽9에디터 설치 kimilguk -->
    <link rel="stylesheet" href="/ckeditor/plugins/mong9-editor/source/etc/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/ckeditor/plugins/mong9-editor/source/css/mong9-base.css">
    <link rel="stylesheet" href="/ckeditor/plugins/mong9-editor/source/css/mong9.css">
    <link rel="stylesheet" href="/ckeditor/plugins/mong9-editor/source/css/mong9-m.css" media="all and (max-width: 768px)">
    <link rel="stylesheet" href="/ckeditor/plugins/mong9-editor/source/css/mong9-e.css" media="all and (max-width: 576px)">
  </head>
  <body>
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
        textarea { height: 300px; resize: vertical; }
        .btn-submit {
            background-color: #007bff;
        }
        .btn-edit {
            background-color: #0b57d0;
        }
        .btn-delete {
            background-color: #FF0000;
        }
        .btn-list {
            background-color: #808080;
        }
        @media (min-width: 768px) {
            .btn { width: auto; } /* 데스크톱에서는 버튼 크기 자동 */
        }
        
        /* 글 보기+반응형 설정 */
        .view-header { border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .view-title { margin: 0 0 10px 0; font-size: 24px; }
        .view-info { font-size: 14px; color: #777; }
        .view-info span { margin-right: 15px; }
        .view-content { min-height: 200px; line-height: 1.6; font-size: 16px; }
        .view-actions { text-align: right; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; }
        @media (max-width: 768px) {
            body { padding: 10px; }
            .view-container { padding: 15px; }
            .view-title { font-size: 20px; }
            .view-info span { display: block; margin-bottom: 5px; }
        }
        @media (max-width: 480px) {
            .view-title { font-size: 18px; }
            .view-content { font-size: 14px; }
            .view-actions { text-align: center; }
        }
        
        /* 목록 보기+반응형 설정 */
        .col-no { width: 10%; text-align: center; }
        .col-title { width: 60%; }
        .col-writer { width: 15%; text-align: center; }
        .col-date { width: 15%; text-align: center; }
        @media (max-width: 480px) {
            .list-header { display: none; } /* 헤더 숨김 */
            .list-item {
                flex-direction: column; /* 세로 배치 */
                align-items: flex-start;
                padding: 15px;
            }
            .col-no, .col-title, .col-writer, .col-date {
                width: 100%;
                text-align: left;
                padding: 2px 0;
            }
            .col-title { font-size: 1.1em; font-weight: bold; }
            .col-no::before { content: "No. "; color: #888; }
            .col-writer::before { content: "작성자: "; color: #888; }
            .col-date::before { content: "날짜: "; color: #888; }
        }
        
        /* 게시판 페이징 디자인 설정 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        /* 이전/다음 버튼 전용 */
        .pagination a.nav-btn {
            font-weight: bold;
            background-color: #e5e5e5;
            color: #007bff;
            border: 1px solid #007bff;
            text-align: center;
        }
        
        .pagination a.nav-btn:hover {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
    </style>
    <div class="board-container">
        <h2>글쓰기/글수정</h2>
        <form action="./" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">제목</label>
                <input type="text" id="title" name="title" placeholder="제목을 입력하세요" required>
            </div>
            <div class="form-group">
                <label for="content">내용</label>
                <textarea id="content" name="content" placeholder="내용을 입력하세요" required></textarea>
            </div>
            <div class="form-group">
                <label for="upload_file">첨부파일</label>
                <input type="file" id="upload_file" name="upload_file"><br>
            </div>
            <div class="view-actions">
            <button type="submit" class="btn btn-submit">등록하기</button>
            <button type="submit" class="btn btn-edit">수정하기</button>
            <button type="submit" class="btn btn-delete">삭제하기</button>
            <button type="button" class="btn btn-list">목록보기</button>
            </div>
        </form>
    </div>
    <div class="board-container">
        <div class="view-header">
            <h2 class="view-title">반응형 게시판 글보기 예제입니다.</h2>
            <div class="view-info">
                <span>작성자: 홍길동</span>
                <span>작성일: 2026-05-13</span>
                <span>조회수: 123</span>
            </div>
        </div>
        
        <div class="view-content">
            <p>반응형 웹 사이트 소스 예제입니다.</p>
            <p>화면 크기에 따라 폰트 사이즈와 레이아웃이 유연하게 변경됩니다.</p>
            <p>모바일에서도 가독성이 좋게 스타일링 되었습니다.</p>
        </div>
        
        <div class="view-actions">
            <button type="button" class="btn btn-list">목록보기</button>
            <button type="button" class="btn btn-edit">수정하기</button>
        </div>
    </div>
    <div class="board-container">
        <h2>공지사항</h2>
        <div class="board-list">
            <!-- 헤더 (데스크탑) -->
            <div class="list-header">
                <div class="col-no">번호</div>
                <div class="col-title">제목</div>
                <div class="col-writer">작성자</div>
                <div class="col-date">작성일</div>
            </div>
            <!-- 아이템 시작 -->
            <?php if($result && mysqli_num_rows($result) > 0) { ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="list-item">
                    <div class="col-no"><?php echo $row['id'] ?></div>
                    <div class="col-title"><a href="#"><?php echo htmlspecialchars($row['title']) ?></a></div>
                    <div class="col-writer"><?php echo $row['writer'] ?></div>
                    <div class="col-date"><?php echo $row['reg_date'] ?></div>
                </div>
                <?php endwhile; ?>
                <div class="pagination">
                    <a class="nav-btn btn" href="?page=<?php echo ($page-1) ?>">이전 5개</a>
                    <a class="nav-btn btn" href="?page=<?php echo ($page+1) ?>">다음 5개</a>
                </div>
            <?php }else{ ?>
                <div class="list-item">
                    <div class="col-no">&nbsp;</div>
                    <div class="col-title"><a href="#">등록된 데이터가 없습니다.</a></div>
                    <div class="col-writer">관리자</div>
                    <div class="col-date">&nbsp;</div>
                </div>
            <?php } ?>
            <!-- 아이템 끝 -->
        </div>
    </div>