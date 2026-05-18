<!-- 메뉴 모듈 시작 -->
    <!-- 구글 번역기가 시작 -->
    <div id="google_translate_element" style="display: none;"></div>
    <script type="text/javascript">
      // 1. 구글 번역 초기화 함수
      function googleTranslateElementInit() {
        new google.translate.TranslateElement(
          {
            pageLanguage: 'ko', // 내 홈페이지의 기본 언어
            includedLanguages: 'ko,en,ja,zh-CN', // 번역을 지원할 언어 목록
            autoDisplay: false
          },
          'google_translate_element'
        );
      }
      // 2. a 링크 클릭 시 언어 변경 함수
      function changeLanguage(langCode) {
        var event = null;
        var selects = document.getElementsByClassName('goog-te-combo');
        
        if (selects == null || selects.length == 0) {
          alert("번역기가 아직 로드되지 않았습니다. 잠시만 기다려주세요.");
          return;
        }
        var languageSelect = selects[0];
        languageSelect.value = langCode;
        // 이벤트 생성 및 트리거 (콤보박스 값 변경을 번역기에 적용)
        if (document.createEvent) {
          event = document.createEvent('HTMLEvents');
          event.initEvent('change', true, true);
          languageSelect.dispatchEvent(event);
        } else {
          event = document.createEventObject();
          event.eventType = 'change';
          languageSelect.fireEvent('onchange', event);
        }
      }
      $(document).ready(function() {
        $('.lang-link').on('click', function(e) {
            e.preventDefault(); // 기본 링크 이동 방지
            var targetLang = $(this).attr('data-lang'); // 클릭한 언어 코드 가져오기
            changeLanguage(targetLang);
        });
      });
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <!-- 구글 번역기가 끝 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
    <div class="floating-container">
        <div class="floating-menu">
            <a href=".top" class="sub-button"><i class="fa fa-home"></i>&nbsp;홈으로</a>
            <a href=".m9-list-style-" class="sub-button"><i class="fa fa-envelope"></i>&nbsp;게시판</a>
            <a href=".m9-google_map" class="sub-button"><i class="fa fa-share-alt"></i>&nbsp;오시는길</a>
            <a href="javascript:void(0);" class="sub-button lang-link" data-lang="zh-CN"><i class="fa fa-file-o"></i>&nbsp;중국어</a>
            <a href="javascript:void(0);" class="sub-button lang-link" data-lang="en"><i class="fa fa-file-text-o"></i>&nbsp;영어</a>
            <a href="javascript:void(0);" class="sub-button lang-link" data-lang="ja"><i class="fa fa-file-code-o"></i>&nbsp;일본어</a>
            <a href="javascript:void(0);" class="sub-button lang-link" data-lang="ko"><i class="fa fa-file-text"></i>&nbsp;한국어</a>
        </div>
        <div class="floating-button">
            <i class="fa fa-bars"></i>
        </div>
    </div>
    <script>
    //메뉴 링크 액션(아래)
    $(document).ready(function(){
        $('.floating-menu a').click(function(e){
            e.preventDefault(); // 기본 <a> 링크 이동 방지
            var target = $($(this).attr('href')); // 클릭한 메뉴의 href 속성값을 타겟으로 설정
            if($(this).attr('href') == '.m9-list-style-') target = $("[class^='m9-list-style-']");
            console.log(target);
            var targetTop = 0;
            if (target.length > 0) {
                targetTop = target.offset().top; // 타겟의 화면상 세로 위치(px)를 가져옴
            }else{
                targetTop = 0;
            }
            // 0.4초 동안 타겟 위치로 부드럽게 스크롤 이동
            $('html, body').animate({scrollTop: targetTop}, 400);
        });
    });
    //메뉴 클릭 액션(아래)
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.querySelector('.floating-container');
        const mainBtn = document.querySelector('.floating-button');
    
        // 모바일 터치 및 PC 클릭 대응
        mainBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            container.classList.toggle('active');
        });
    
        // PC 환경에서 마우스 오버(Hover) 시 펼치기 원할 경우 주석 해제
        /*
        container.addEventListener('mouseenter', () => {
            container.classList.add('active');
        });
        container.addEventListener('mouseleave', () => {
            container.classList.remove('active');
        });
        */
    
        // 메뉴 영역 밖을 클릭하면 닫히도록 설정
        document.addEventListener('click', () => {
            if (container.classList.contains('active')) {
                container.classList.remove('active');
            }
        });
    });
    </script>
    <style>
    /* 플로딩 메뉴 디자인 과 소스 */
    .floating-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .floating-button {
        width: 60px;
        height: 60px;
        background-color: #007bff;
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, background-color 0.3s;
    }
    
    .floating-button:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }
    
    .floating-menu {
        display: flex;
        flex-direction: column-reverse;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    /* 메뉴가 열렸을 때 */
    .floating-container.active .floating-menu {
        max-height: 200px;
        overflow: visible;
    }
    
    .floating-container.active .floating-button {
        transform: rotate(90deg); /* 펼치기 버튼 X자 회전 */
    }
    
    .sub-button {
        min-width: 100px;
        height: 45px;
        background-color: #28a745;
        color: white;
        border-radius: 10%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 18px;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, background-color 0.3s;
    }
    
    .sub-button:hover {
        background-color: #218838;
    }
    </style>
<!-- 메뉴 모듈 끝 -->
<!-- 지도 모듈 시작 -->
    <script>
    function google_map() {
        try {
    		const elements = document.querySelector('.m9-google_map');
    		var mapOptions = elements;
    	    console.log("여기");
    		const str = mapOptions.getAttribute('data-m9-execute').match(/\((.*?)\)/)[1];
    		// 작은따옴표를 큰따옴표로 변환
    		const validJsonStr = str.replace(/'/g, '"');
    		const jsonObject = JSON.parse(validJsonStr);
    		console.log(jsonObject);
            // 초기값 {'x':'126.9753042','y':'37.5599416','zoom':'16','address':'Address','title':'Company'} 
    		// 사용값 {'x':'127.1515013','y':'36.8181846','zoom':'18','address':'Address','title':'타임스페이스'}
            var latlng = {lat: Number(jsonObject.y), lng: Number(jsonObject.x)};
            
            // 지도 생성
            var map = new google.maps.Map(document.querySelector('.m9-google_map'), {
                zoom: parseInt(jsonObject.zoom), // 배율
                center: latlng
            });
            
            // 마커 추가 deprecated 됨
            var marker = new google.maps.Marker({
                position: latlng,
                map: map,
                title: jsonObject.title
            });
        } catch (e) {
            // 에러 처리 코드
            console.error("에러 발생:", e.message); // 에러 로그 출력
            return null; // 에러 시 기본값 반환
        }

    }
    </script>
    <script src="/core/proxy.php?callback=google_map" async defer></script>
<!-- 지도 모듈 끝 -->