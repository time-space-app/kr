<?php session_start(); ?>
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
            display: none; /* 기본 숨김 display: flex; */
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
        
        /* 레이어 팝업 배경 */
        .layer-popup {
            display: none; /* 기본 숨김 */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* 어두운 배경 */
            z-index: 1000;
        }
        /* 팝업 박스 */
        .popup-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        /* 헤더 및 닫기 버튼 */
        .popup-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .close-btn {
            cursor: pointer;
            border: none;
            background: none;
            font-size: 40px;
        }
        /* 바디 */
        .popup-body {
            margin-top: 15px;
            min-height: 100px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-latest.js"></script>
    <script>
    $(document).ready(function() {
        loadList();
        // 2. 등록 버튼 클릭 이벤트
        $('.btn-submit').click(function(e) {
            e.preventDefault(); // 기본 폼 제출 막기
            var formData = new FormData($('#boardForm')[0]);
            formData.append('mode', 'insert');
            $.ajax({
                url: '/core/board-api.php', // 서버 저장 API 주소
                type: 'POST',
                data: formData,
                contentType: false, // 필수: multipart/form-data 설정
                processData: false, // 필수: 데이터 쿼리 스트링 변환 막기
                success: function(data) {
                    alert(data.message);
                    $('#title').val('');
                    $('#content').val('');
                    loadList(); // 목록 새로고침
                }
            });
        });
    
        // 게시글 목록을 불러오는 함수
        function loadList(page_location) {
            let numberValue = Number(document.getElementById('page').innerText);
            if(page_location=='prev') numberValue--;
            if(page_location=='next') numberValue++;
            $.ajax({
                url: '/core/board-api.php', // 서버 목록 API 주소
                type: 'GET',
                data: { 
                    page: numberValue, 
                    mode: "list" 
                },
                success: function(data) {
                    let html = '';
                    // 데이터 수만큼 반복하여 테이블 row 생성
                    $.each(data.result, function(index, item) {
                        html += '<div class="list-item" style="cursor:pointer" data-id="' + item.id + '">';
                        html += '<div class="col-no">' + item.id + '</div>';
                        html += '<div class="col-title">' + item.title + '</div>';
                        html += '<div class="col-writer">' + item.writer + '</div>';
                        html += '<div class="col-date">' + item.reg_date + '</div>';
                        html += '</div>';
                    });
                    if(data.result.length>0) {
                        $('.pagination').attr("style", "display:flex;");
                        $('.pagination').show();
                    }
                    document.getElementById('page').innerText = data.page;
                    $('#ajax-list').html(html);
                }
            });
        }
        // 이전5개 클릭
        $(document).on('click', '.prev', function(e) {
            e.preventDefault();
            loadList('prev');
        });
        // 다음5개 클릭
        $(document).on('click', '.next', function(e) {
            e.preventDefault();
            loadList('next');
        });
        
        // 글보기 닫기 버튼 이벤트
        $('.close-btn, .layer-popup').click(function(e) {
            if($(e.target).hasClass('layer-popup') || $(e.target).hasClass('close-btn')) {
                $('#layer-popup').hide();
            }
        });
        // 글보기 버튼 이벤트
        $(document).on('click', '.list-item', function(e) {
            e.preventDefault();
            var boardId = $(this).data('id');
            // AJAX로 데이터 가져오기
            $.ajax({
                url: "/core/board-api.php",
                type: "POST",
                data: { id: boardId, mode: "view" },
                success: function(data) {
                    // 데이터 삽입
                    let response = data.result;
                    $("#popup-title").text(response.title);
                    $('#popup-body-content').html(response.content);
                    $('#layer-popup').show();
                }
            });
        });
        
    });
    </script>
    <!-- 내용보기 레이어 팝업 -->
    <div id="layer-popup" class="layer-popup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="view-title"><span class="popup-title" id="popup-title">로딩 중...</span></h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="board-container">
                <div class="view-header">
                    <div class="view-info">
                        <span>작성자: 홍길동</span>
                        <span>작성일: 2026-05-13</span>
                        <span>조회수: 123</span>
                    </div>
                </div>
                <div class="view-content popup-body" id="popup-body-content">
                    <p>로딩 중...</p>
                </div>
                <?php if (isset($_SESSION['filemanager']['logged'])) { ?>
                <div class="view-actions">
                    <button type="button" class="btn btn-edit">수정하기</button>
                    <button type="button" class="btn btn-delete">삭제하기</button>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="board-container">
        <h2>글쓰기/글수정</h2>
        <form action="./" method="post" id="boardForm" enctype="multipart/form-data">
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
            <button type="button" class="btn btn-submit">등록하기</button>
            <button type="button" class="btn btn-list">목록보기</button>
            </div>
        </form>
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
            <!-- Ajax로 데이터 갱신 시작 -->
            <div id="ajax-list">
            
            </div>
            <!-- Ajax로 데이터 갱신 끝 -->
            <div class="pagination">
                <a class="nav-btn btn prev" href="#">이전 5개</a>
                <span id="page" class="nav-btn btn"></span>
                <a class="nav-btn btn next" href="#">다음 5개</a>
            </div>
        </div>
    </div>