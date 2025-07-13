@extends('admin.layouts.app')

@section('content')
<style>
    .chat-box {
        height: 70vh;
        overflow-y: auto;
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: .25rem;
    }

    .chat-message {
        display: flex;
        margin-bottom: 1.2rem;
    }

    .chat-message.admin {
        justify-content: flex-end;
        text-align: right;
    }

    .chat-message .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .chat-bubble {
        max-width: 70%;
        padding: 0.6rem 1rem;
        border-radius: 12px;
        font-size: 0.95rem;
        position: relative;
    }

    .chat-bubble.admin {
        background-color: #0d6efd;
        color: white;
        border-bottom-right-radius: 0;
    }

    .chat-bubble.user {
        background-color: #e2e3e5;
        color: #212529;
        border-bottom-left-radius: 0;
    }

    .chat-time {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 4px;
    }
</style>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Trò chuyện với: {{ $user->name ?? 'Khách chưa đăng ký' }}</h5>
        <a href="{{ route('admin.chat.list') }}" class="btn btn-sm btn-outline-secondary">⬅ Quay lại</a>
    </div>

    <div class="card-body">
        {{-- Chat history --}}
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

        {{-- Send message form --}}
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
@endsection
