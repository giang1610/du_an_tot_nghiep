import Echo from '../echo';

export function listenToNewMessages(userId, callback) {
  console.log('[📡] Lắng nghe tin nhắn mới tại: chat.' + userId);

  const localUser = JSON.parse(localStorage.getItem('user'));
  const localUserId = localUser?.id;

  const channel = Echo.channel(`chat.${userId}`)
    .listen('.chat.message', (e) => {
      console.log('💬 Nhận tin nhắn mới:', e);

      // So sánh với userId từ localStorage
      if (e.user_id !== localUserId) {
        console.log('🛑 Tin nhắn không phải của user hiện tại, bỏ qua');
        return;
      }

      callback({
        id: e.id || Date.now(),
        sender: e.sender || 'admin',
        message: e.message,
        user_id: e.user_id,
      });
    });

  return channel;
}
