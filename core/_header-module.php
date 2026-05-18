<!-- 게시판 모듈 시작 -->
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
        textarea { height: auto; resize: vertical; }
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
            width: 80%;
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
    <script>
	function downloadFile(file_save_name) {
			// 1. 임시 a 태그 생성
			const link = document.createElement('a');
			link.href = `core/file-download.php?file_save_name=${encodeURIComponent(file_save_name)}`;
			link.download = file_save_name; // 다운로드될 파일명 지정
			// 2. 화면에 숨겨서 추가 (필수)
			link.style.display = 'none';
			document.body.appendChild(link);
			// 3. 클릭 이벤트 발생 및 태그 제거
			link.click();
			document.body.removeChild(link);
		}
    $(document).ready(function() {
        loadList();
        // CRUD 버튼 클릭 이벤트
        $('.btn-delete').click(function(e) {
            e.preventDefault(); // 기본 폼 제출 막기
            if (!confirm("정말 삭제하시겠습니까?")) return;
            var formData = new FormData($('#editForm')[0]);
            formData.append('mode', 'delete');
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
                    $('.layer-popup').hide();
                }
            });
        });
        $('.btn-edit').click(function(e) {
            e.preventDefault(); // 기본 폼 제출 막기
            var formData = new FormData($('#editForm')[0]);
            formData.append('mode', 'edit');
            $.ajax({
                url: '/core/board-api.php', // 서버 저장 API 주소
                type: 'POST',
                data: formData,
                contentType: false, // 필수: multipart/form-data 설정
                processData: false, // 필수: 데이터 쿼리 스트링 변환 막기
                success: function(data) {
                    alert(data.message);
                    //$('#editForm')[0].reset();
                    loadList(); // 목록 새로고침
					$('.view-item').trigger('click');
                    //$('.layer-popup').hide();
                }
            });
        });
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
                    $('#boardForm')[0].reset();
                    loadList(); // 목록 새로고침
                    $('.layer-popup').hide();
                }
            });
        });
        // 게시글 목록을 불러오는 함수
        function loadList(page_location) {
            let numberValue = Number($('#page').text());
            let pagination = '<div class="pagination">';
                pagination += '<a class="nav-btn btn prev" href="#">이전</a>';
                pagination += '<span id="page" class="nav-btn btn"></span>';
                pagination += '<a class="nav-btn btn next" href="#">다음</a>';
                <?php if (isset($_SESSION['filemanager']['logged'])) { ?>
                pagination += '<button type="button" class="btn btn-popup">글등록창</button>';
                <?php } ?>
                pagination += '</div>';
            $('.pagination').remove();
            $("[class^='m9-list-style-']").after(pagination);
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
						if ($("[class^='m9-list-style-'] li").hasClass('display-inline-block')) {
							html += '<li class="float-left display-inline-block e-float-none e-display-block m9-margin-right-1 e-m9-margin-right-0 list-item" style="cursor:pointer" data-id="' + item.id + '">'+item.title+'</li>';
						}else{
							html += '<li class="list-item" style="cursor:pointer" data-id="' + item.id + '">'+item.title+'</li>';
						}
                    });
                    if(data.result.length>0) {
                        $('.pagination').attr("style", "display:flex;");
                        $('.pagination').show();
                        document.getElementById('page').innerText = data.page;
						$("[class^='m9-list-style-']").html(html);
                    }else{
                        $('.pagination').attr("style", "display:flex;");
                        $('.pagination').show();
                        document.getElementById('page').innerText = data.page-1;
                    }
                }
            });
        }
        // 이전 게시글 클릭
        $(document).on('click', '.prev', function(e) {
            e.preventDefault();
            loadList('prev');
        });
        // 다음 게시글 클릭
        $(document).on('click', '.next', function(e) {
            e.preventDefault();
            loadList('next');
        });
        // 글보기 닫기 버튼 이벤트
        $('.close-btn, .btn-close').click(function(e) { //.layer-popup, 
            $('.layer-popup').hide();
        });
        // 글등록창 보기 이벤트
        $(document).on('click', '.btn-popup', function(e) {
            e.preventDefault();
            $('#boardForm')[0].reset();
            $('#write-popup').show();
        });
        // 글수정창 보기 이벤트
        $(document).on('click', '.edit-item', function(e) {
            e.preventDefault();
			$('#editForm')[0].reset();
            var boardId = $('#popup-title').attr('data-id');
            // AJAX로 데이터 가져오기
            $.ajax({
                url: "/core/board-api.php",
                type: "POST",
                data: { id: boardId, mode: "view" },
                success: function(data) {
                    $('.layer-popup').hide();
                    // 데이터 삽입
                    let response = data.result;
                    $('#editForm #title').val(response.title);
                    var content = response.content.replace(/\\r\\n/g, '\n');
                    $('#editForm #content').val(content);
                    $('#editForm #id').val(boardId);
					if($('.download-real-file').length > 0) $('.download-real-file').remove();
					if(response.file_name) {
					$('.download-file').text(response.file_name);
					$('.download-file').after('<a href="#" onclick="downloadFile(\''+response.file_save_name+'\')" class="download-real-file">다운로드</a>');
					}else{
					$('.download-file').text('');
					$('.download-real-file').remove();
					}
                    $('#edit-popup').show();
                }
            });
        });
        // 글보기창 보기 이벤트
        $(document).on('click', '.list-item', function(e) {
            e.preventDefault();
            $('.layer-popup').hide();
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
                    $('#popup-title').attr('data-id', response.id);
                    var content = response.content.replace(/\\r\\n/g, '<br>');
                    $('#popup-body-content').html(content);
					$('.view-writer').html(response.writer);
					$('.view-count').html(response.view_count);
					$('.view-reg_date').html(response.reg_date);
					if($('.download-real-file').length > 0) $('.download-real-file').remove();
					if(response.file_name) {
					$('.download-file').text(response.file_name);
					$('.download-file').after('<a href="#" onclick="downloadFile(\''+response.file_save_name+'\')" class="download-real-file">다운로드</a>');
					}else{
					$('.download-file').text('');
					$('.download-real-file').remove();
					}
                    $('#view-popup').show();
                }
            });
        });
        // 수정창에서 글보기창 보기 이벤트
        $(document).on('click', '.view-item', function(e) {
            e.preventDefault();
            $('.layer-popup').hide();
            var boardId = $('#editForm #id').val();
            // AJAX로 데이터 가져오기
            $.ajax({
                url: "/core/board-api.php",
                type: "POST",
                data: { id: boardId, mode: "view" },
                success: function(data) {
                    let response = data.result;
                    $("#popup-title").text(response.title);
                    $('#popup-title').attr('data-id', response.id);
                    var content = response.content.replace(/\\r\\n/g, '<br>');
                    $('#popup-body-content').html(content);
					if($('.download-real-file').length > 0) $('.download-real-file').remove();
					if(response.file_name) {
					$('.download-file').text(response.file_name);
					$('.download-file').after('<a href="#" onclick="downloadFile(\''+response.file_save_name+'\')" class="download-real-file">다운로드</a>');
					}else{
					$('.download-file').text('');
					$('.download-real-file').remove();
					}
                    $('#view-popup').show();
                }
            });
        });
    });
    </script>
    <!-- 내용보기 레이어 팝업 -->
    <div id="view-popup" class="layer-popup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="view-title"><span class="popup-title" id="popup-title">로딩 중...</span></h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="board-container">
                <div class="view-header">
                    <div class="view-info">
                        작성자: <span class="view-writer">홍길동</span>
                        작성일: <span class="view-reg_date">2026-05-13</span>
                        조회수: <span class="view-count">123</span>
						첨부파일: <span class="download-file"></span>
                    </div>
                </div>
                <div class="view-content popup-body" id="popup-body-content">
                    <p>로딩 중...</p>
                </div>
                <?php if (isset($_SESSION['filemanager']['logged'])) { ?>
                <div class="view-actions">
                    <button type="button" class="btn btn-close">창닫기</button>
                    <button type="button" class="btn edit-item">수정하기</button>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div id="edit-popup" class="layer-popup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="view-title"><span class="popup-title">글수정</span></h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="board-container">
                <form action="./" method="post" id="editForm" enctype="multipart/form-data">
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
					첨부된 파일: <span class="download-file"></span>
                    <?php if (isset($_SESSION['filemanager']['logged'])) { ?>
                    <div class="view-actions">
                        <input type="hidden" id="id" name="id" placeholder="글고유id" required>
                        <button type="button" class="btn btn-list view-item">글보기</button>
                        <button type="button" class="btn btn-edit">수정하기</button>
                        <button type="button" class="btn btn-delete">삭제하기</button>
                        <button type="button" class="btn btn-close">창닫기</button>
                    </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
    <div id="write-popup" class="layer-popup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="view-title"><span class="popup-title">글쓰기</span></h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="board-container">
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
                    <button type="button" class="btn btn-close">창닫기</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<!-- 게시판 모듈 끝 -->