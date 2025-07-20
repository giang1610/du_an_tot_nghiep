@extends('admin.layouts.app')

@section('content')
<style>
    .chat-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 1rem;
    }

    .chat-box {
        height: 70vh;
        overflow-y: auto;
        background-color: #ffffff;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        scroll-behavior: smooth;
    }

    .chat-message {
        display: flex;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.3s ease-in;
    }

    .chat-message.admin {
        justify-content: flex-end;
        text-align: right;
    }

    .chat-message .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e9ecef;
        flex-shrink: 0;
    }

    .chat-bubble {
        max-width: 70%;
        padding: 0.8rem 1.2rem;
        border-radius: 16px;
        font-size: 0.95rem;
        line-height: 1.4;
        position: relative;
        transition: background-color 0.2s;
    }

    .chat-bubble.admin {
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 4px;
        margin-right: 1rem;
    }

    .chat-bubble.user {
        background-color: #f1f3f5;
        color: #212529;
        border-bottom-left-radius: 4px;
        margin-left: 1rem;
    }

    .chat-bubble:hover {
        filter: brightness(95%);
    }

    .chat-time {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.3rem;
        opacity: 0.8;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }

    .input-group {
        border-radius: 8px;
        overflow: hidden;
    }

    .input-group input {
        border: none;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
    }

    .input-group button {
        border: none;
        padding: 0.8rem 1.5rem;
        font-weight: 500;
    }

    .input-group button:hover {
        background-color: #0b5ed7;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .chat-box {
            height: 60vh;
            padding: 1rem;
        }

        .chat-bubble {
            max-width: 80%;
        }

        .chat-message .avatar {
            width: 36px;
            height: 36px;
        }
    }
</style>

<div class="chat-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Trò chuyện với: {{ $user->name ?? 'Khách chưa đăng ký' }}</h5>
            <a href="{{ route('admin.chat.list') }}" class="btn btn-sm btn-outline-secondary">⬅ Quay lại</a>
        </div>

        <div class="card-body">
            <div class="chat-box mb-3">
                @forelse ($chats as $chat)
                    <div class="chat-message {{ $chat->sender === 'admin' ? 'admin' : '' }}">
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

                        @if($chat->sender === 'admin')
                            <img src="{{ asset('images/admin-avatar.png') }}" alt="Admin" class="avatar ms-2">
                        @endif
                    </div>
                @empty
                    <p class="text-muted text-center">Chưa có tin nhắn nào.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.chat.send') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Nhập tin nhắn..." required>
                    <button class="btn btn-primary" type="submit">Gửi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection