const currentUserId = window.chatUserId || null;
const currentSender = window.currentSender || 'admin';

Echo.channel('chat.admin')
    .listen('.chat.message', function (e) {
        // console.log('[Danh sách chat] Có tin nhắn mới:', e);

        const listContainer = document.querySelector('.chat-list-container');
        if (!listContainer) return;

        // Xóa alert "chưa có khách hàng nào" nếu có tin nhắn đầu tiên
        const emptyAlert = listContainer.querySelector('.no-chat-msg');
        if (emptyAlert) emptyAlert.remove();

        const userId = e.user_id;
        const userName = e.name ;
        const avatar = e.avatar || `https://i.pravatar.cc/150?u=${userId}`;
        const message = e.message;
        const isAdmin = e.sender ;

        // Tìm item trong danh sách theo data-user-id
        let chatItem = listContainer.querySelector(`a.chat-list-item[data-user-id="${String(userId)}"]`);

        if (!chatItem) {
            // Nếu chưa có thì tạo mới
            chatItem = document.createElement('a');
            chatItem.href = `/admin/chat/${userId}`;
            chatItem.className = "chat-list-item";
            chatItem.setAttribute("data-user-id", userId);

            chatItem.innerHTML = `
                <div class="chat-avatar">
                    <img src="${avatar}" alt="Avatar">
                </div>
                <div class="chat-info">
                    <div class="user-name">${userName}</div>
                    <div class="last-message"></div>
                </div>
                <div class="chat-meta text-end"></div>
            `;

            listContainer.prepend(chatItem);
        } else {
            // Nếu đã có thì đưa user này lên đầu
            listContainer.prepend(chatItem);
        }

        // Cập nhật avatar + tên
        chatItem.querySelector(".chat-avatar img").src = avatar;
        chatItem.querySelector(".user-name").textContent = userName;

        // Cập nhật tin nhắn cuối
        const lastMessageEl = chatItem.querySelector(".last-message");
        if(isAdmin === 'admin') {
        lastMessageEl.innerHTML = (isAdmin ? `<span class="text-muted">Bạn: </span>` : '') + message;
        }
        else {
            lastMessageEl.innerHTML = (isAdmin ? `<span class="text-muted">${userName}: </span>` : '') + message;
        }
        // Cập nhật thời gian
        const chatMeta = chatItem.querySelector(".chat-meta");
        chatMeta.innerHTML = `<div class="small">vừa xong</div>`;

        // Badge chưa đọc
        if (parseInt(currentUserId) !== parseInt(userId)) {
            let unread = chatItem.querySelector(".unread-indicator");
            if (!unread) {
                unread = document.createElement("span");
                unread.className = "unread-indicator";
                unread.title = "1 chưa đọc";
                chatMeta.appendChild(unread);
            }
        } else {
            const unread = chatItem.querySelector(".unread-indicator");
            if (unread) unread.remove();
        }
    });
