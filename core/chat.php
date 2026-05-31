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
    </style>
</head>
<body>

<div class="chat-container">
    <h2>AI 챗봇-kr 1페이지 빌더에대해 물어보세요</h2>
    <div class="chat-box" id="chatBox"></div>
    <input type="text" id="userInput" placeholder="질문을 입력하세요...">
    <button onclick="sendMessage()">전송</button>
</div>

<script>
    async function sendMessage() {
        const input = document.getElementById('userInput');
        const chatBox = document.getElementById('chatBox');
        
        if (input.value.trim() === '') return;

        // 사용자 메시지 화면 표시
        chatBox.innerHTML += `<div class="message user"><strong>나:</strong> ${input.value}</div>`;
        
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
            chatBox.innerHTML += `<div class="message bot"><strong>챗봇:</strong> ${data.reply}</div>`;
        } catch (error) {
            chatBox.innerHTML += `<div class="message bot"><strong>오류:</strong> 통신 실패</div>`;
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

</body>
</html>
