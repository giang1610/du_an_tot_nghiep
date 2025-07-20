(function () {
  const userId = window.chatUserId;

  if (!userId) {
    console.warn('[⚠️] Không có userId để lắng nghe kênh chat.');
    return;
  }

  console.log('[📡] Lắng nghe tại: chat.' + userId);

  const chatBox = document.querySelector('.chat-box');
  const typingIndicator = document.getElementById('typing-indicator');

  if (!chatBox) {
    console.warn('[❌] Không tìm thấy khung chat để hiển thị tin nhắn.');
    return;
  }

  // Nhận tin nhắn mới
  Echo.channel('chat.' + userId)
    .listen('.chat.message', function (e) {
      console.log('[💬 Event] Nhận tin nhắn mới:', e);

      const messageHtml = `
        <div class="chat-message">
          <img src="https://i.pravatar.cc/150?u=${userId}" alt="User Avatar" class="avatar me-2">
          <div>
            <div class="chat-bubble user">${e.message}</div>
            <div class="chat-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
          </div>
        </div>
      `;

      chatBox.insertAdjacentHTML('beforeend', messageHtml);
      chatBox.scrollTop = chatBox.scrollHeight;

      // Ẩn đang nhập nếu có
      if (typingIndicator) {
        typingIndicator.style.display = 'none';
      }
    })

    // Sự kiện typing
    .listen('.chat.typing', (e) => {
      if (!typingIndicator) {
        console.warn('❌ Không tìm thấy phần tử typing-indicator trong DOM');
        return;
      }

      // Hiện typing nếu là đúng người dùng
      if (e.user_id == userId) {
        typingIndicator.style.display = 'block';
        clearTimeout(window._typingTimeout);
        window._typingTimeout = setTimeout(() => {
          typingIndicator.style.display = 'none';
        }, 3000); // Ẩn sau 3s nếu không gõ tiếp
      }
    });
})();
