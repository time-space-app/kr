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
    </body>
</html>