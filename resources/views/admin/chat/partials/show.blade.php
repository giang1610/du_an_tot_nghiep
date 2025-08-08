<link rel="stylesheet" href="{{ asset('css/show.css') }}">

@if(!isset($user))
    <div class="text-center mt-5 text-muted">
        <h5 class="text-white">Chọn một khách hàng để bắt đầu trò chuyện</h5>
    </div>
@else
    <div class="card me-3">
        <div class="card-header bg-white d-flex align-items-center">
            <img src="{{ $user->img_thumbnail ? asset('storage/' . $user->img_thumbnail) : 'https://i.pravatar.cc/150?u=' . $user->id }}"
                 alt="Avatar" class="rounded-circle me-2" style="width: 40px; height: 40px;">
            <strong>{{ $user->name ?? 'Khách chưa đăng ký' }}</strong>
        </div>

        <div class="card-body">
            <div class="chat-box mb-3" id="chat-box">
                @forelse ($chats as $chat)
                    <div class="chat-message {{ $chat->sender === 'admin' ? 'admin' : 'user' }}">
                        @if($chat->sender !== 'admin')
                            <img src="{{ $user->img_thumbnail ? asset('storage/' . $user->img_thumbnail) : 'https://i.pravatar.cc/150?u=' . $user->id }}"
                                 alt="User Avatar" class="avatar me-2">
                        @endif

                        <div>
                            <div class="chat-bubble {{ $chat->sender === 'admin' ? 'admin' : 'user' }}">
                                {{ $chat->message }}
                            </div>
                            <div class="chat-time">
                                {{ $chat->created_at->format('H:i d/m/Y') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">Chưa có tin nhắn nào.</p>
                @endforelse
            </div>

            <div id="typing-indicator" class="typing-indicator" style="display: none;">
                <div class="bubble">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>

            <div class="input-group">
                <input type="text" id="message-input" class="form-control" placeholder="Nhập tin nhắn..." required>
                <button id="send-btn" class="btn btn-primary" type="button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const input = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');

        function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            fetch("{{ route('admin.chat.send', ['userId' => $user->id]) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => {
                if (!response.ok) throw new Error("Gửi thất bại");
                input.value = "";
            })
            .catch(error => {
                alert("Lỗi khi gửi tin nhắn");
                console.error(error);
            });
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const chatBox = document.getElementById('chat-box');
            if (chatBox) {
                setTimeout(() => {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }, 10);
            }
        });

        window.chatUserId = {{ $user->id }};
        window.currentSender = 'admin';
    </script>

    <script src="{{ asset('js/chat/chat-realtime.js') }}"></script>
@endif
