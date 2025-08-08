let typingTimeout = null;
const urlID = window.location.pathname.split('/');
const userId = parseInt(urlID[urlID.length - 1]);

// Kiểm tra userId hiện tại có tồn tại không
if (typeof window.currentUserId !== 'undefined' && window.currentUserId) {

    Echo.channel('chat-typing')
        .listen('.user.typing', (e) => {
            if (!e?.user?.id) return;

            // Chỉ hiển thị nếu người gửi khác user hiện tại
            if (e.user.id !== window.currentUserId && e.user.id === userId  ) {
                console.log('[Typing] Nhận sự kiện typing từ user ID:', e.user.id);
                showTypingIndicator();
            }
        });

    function showTypingIndicator() {
        const el = document.getElementById('typing-indicator');
        if (!el) {
            console.warn('❌ Không tìm thấy phần tử typing-indicator trong DOM');
            return;
        }

        el.style.display = 'block';

        // Xóa timeout cũ để reset lại 3s
        clearTimeout(typingTimeout);

        typingTimeout = setTimeout(() => {
            el.style.display = 'none';
        }, 3000);
    }
} else {
    console.warn('⚠️ Không có window.currentUserId, không thể lắng nghe sự kiện typing');
}
