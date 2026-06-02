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
    </style>
</head>
<body>

<div class="chat-container">
    <h3>Nagoyais.com's page-based AI chatbot</h3>
    <div class="chat-box" id="chatBox"></div>
    <textarea class="textarea" type="text" id="userInput" placeholder="Input Question..."></textarea>
    <button onclick="sendMessage()">Send</button>
</div>

<script>
    async function sendMessage() {
        const input = document.getElementById('userInput');
        const chatBox = document.getElementById('chatBox');
        
        if (input.value.trim() === '') return;

        // 사용자 메시지 화면 표시
        chatBox.innerHTML += `<div class="message user"><strong>Me:</strong> ${input.value}</div>`;
        
        const userMessage = input.value;
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;

        // 서버(PHP)로 전송
        try {
            const response = await fetch('api/chat-page-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ message: userMessage }).toString()
            });

            const data = await response.json();
            
            // 봇 답변 표시
            chatBox.innerHTML += `<div class="message bot"><strong>ChatBot:</strong> ${data.reply}</div>`;
        } catch (error) {
            chatBox.innerHTML += `<div class="message bot"><strong>Error:</strong> Comunication Fail ${error}</div>`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

</body>
</html>
