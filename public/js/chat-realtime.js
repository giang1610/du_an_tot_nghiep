(function () {
  const userId = window.chatUserId;
  const currentSender = window.currentSender || 'admin'; // Mặc định là admin

  if (!userId) {
    // console.warn('Không có userId để lắng nghe kênh chat.');
    return;
  }

  // console.log(' Lắng nghe tại: chat.' + userId);

  const chatBox = document.querySelector('.chat-box');
  const typingIndicator = document.getElementById('typing-indicator');

  if (!chatBox) {
    // console.warn(' Không tìm thấy khung chat để hiển thị tin nhắn.');
    return;
  }

  Echo.channel('chat.' + userId)
    .listen('.chat.message', function (e) {
      // console.log('[  Nhận tin nhắn mới:', e);

      // Phân biệt người gửi
      const isAdmin = e.sender === 'admin';
      const messageClass = isAdmin ? 'admin' : '';
      const bubbleClass = isAdmin ? 'admin' : 'user';

      const messageHtml = `
  <div class="chat-message ${messageClass}">
    ${!isAdmin ? `
      <img src="${e.avatar || 'https://i.pravatar.cc/150?u=' + userId}" class="avatar me-2" />
    ` : ''}
    <div>
      <div class="chat-bubble ${bubbleClass}">
        ${e.message.replace(/\n/g, '<br>')}
      </div>
      <div class="chat-time">
        ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
      </div>
    </div>
  </div>
`;


      chatBox.insertAdjacentHTML('beforeend', messageHtml);
      chatBox.scrollTop = chatBox.scrollHeight;

      if (typingIndicator) {
        typingIndicator.style.display = 'none';
      }
    });

})();
