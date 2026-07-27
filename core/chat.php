<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>제미나이 기반 PHP 챗봇</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .chat-container { width: 430px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px; }
        .chat-box { height: 300px; border: 1px solid #ddd; padding: 10px; overflow-y: auto; margin-bottom: 10px; }
        .message { margin-bottom: 10px; }
        .user { text-align: right; color: blue; }
        .bot { text-align: left; color: green; }
        input[type="text"] { width: 80%; padding: 10px; }
        button { padding: 10px; }
        /* textarea 스타일 추가 */
        .textarea {
            width: 100%; /* input[type="text"]와 동일하게 80% 너비 */
            padding: 10px; /* input[type="text"]와 동일한 패딩 */
            height: 60px; /* 텍스트 입력 영역의 적절한 초기 높이 */
            margin-bottom: 10px; /* 버튼과의 간격을 위한 아래쪽 여백 */
            border: 1px solid #ddd; /* chat-box와 유사한 테두리 */
            border-radius: 4px; /* 살짝 둥근 모서리 */
            box-sizing: border-box; /* 패딩과 테두리가 너비에 포함되도록 설정 */
            resize: vertical; /* 사용자에게 수직 방향으로만 크기 조절 허용 */
            font-size: 1em; /* 기본 글자 크기 */
            line-height: 1.5; /* 줄 간격 */
        }
        .d-none{display:none!important}
        .spin-svg {
          animation: rotateAnimation 2s linear infinite;
          transform-origin: center; /* 회전 기준점을 SVG의 정중앙으로 설정 */
        }
        
        @keyframes rotateAnimation {
          0% {
            transform: rotate(360deg);
          }
          100% {
            transform: rotate(0deg);
          }
        }
    </style>
</head>
<body>

<div class="chat-container">
    <h2>AI 챗봇-kr 1페이지 빌더에대해 물어보세요</h2>
    <div class="chat-box" id="chatBox"></div>
    <textarea class="textarea" id="userInput" placeholder="질문을 입력하세요..."></textarea>
    <button onclick="sendMessage()">전송</button>
    <svg id="loading-image" class="spin-svg d-none" width="30px" height="30px" style="vertical-align: middle;" enable-background="new 0 0 561 561" version="1.1" viewBox="0 0 561 561" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
		<path d="m280.5 76.5v-76.5l-102 102 102 102v-76.5c84.15 0 153 68.85 153 153 0 25.5-7.65 51-17.85 71.4l38.25 38.25c17.85-33.15 30.6-68.85 30.6-109.65 0-112.2-91.8-204-204-204zm0 357c-84.15 0-153-68.85-153-153 0-25.5 7.65-51 17.85-71.4l-38.25-38.25c-17.85 33.15-30.6 68.85-30.6 109.65 0 112.2 91.8 204 204 204v76.5l102-102-102-102v76.5z" fill="#006DF0"/>
    </svg>
</div>

<script>
	function escapeHtml(str) {
      if (typeof str !== "string") return str;
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
    const loading = document.querySelector("#loading-image");
    async function sendMessage() {
        loading.classList.remove("d-none");
        const input = document.getElementById('userInput');
		const inputValue = escapeHtml(input.value);//보안처리
        const chatBox = document.getElementById('chatBox');
        if (input.value.trim() === '') return;
        // 사용자 메시지 화면 표시
        chatBox.innerHTML += `<div class="message user"><strong>나:</strong> ${inputValue}</div>`;
        const userMessage = input.value;
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;
        // 서버(PHP)로 전송
        try {
            const response = await fetch('api/chat-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ message: userMessage }).toString()
            });
            const data = await response.json();
            // 봇 답변 표시
            loading.classList.add("d-none");
            chatBox.innerHTML += `<div class="message bot"><strong>챗봇:</strong> ${data.reply}</div>`;
        } catch (error) {
            loading.classList.add("d-none");
            chatBox.innerHTML += `<div class="message bot"><strong>오류:</strong> 통신 실패 ${error}</div>`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

</body>
</html>
