@extends('admin.layouts.app')

@section('content')

<style>
    .chat-list-container {
        max-height: 75vh;
        overflow-y: auto;
    }
    .chat-list-item {
        display: flex;
        align-items: center;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #eee;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s;
    }
    .chat-list-item:hover {
        background-color: #f1f3f5;
    }
    .chat-list-item.active {
        background-color: #e7f1ff;
        border-right: 3px solid #0d6efd;
    }
    .chat-avatar img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 50%;
    }
    .chat-info {
        flex-grow: 1;
        margin-left: 1rem;
        overflow: hidden;
    }
    .chat-info .user-name {
        font-weight: 600;
        margin-bottom: 2px;
    }
    .chat-info .last-message {
        font-size: 0.875rem;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .chat-meta {
        text-align: right;
        font-size: 0.75rem;
        color: #6c757d;
        min-width: 80px;
    }
    .unread-indicator {
        display: inline-block;
        margin-top: 4px;
        width: 10px;
        height: 10px;
        background-color: #0d6efd;
        border-radius: 50%;
    }
</style>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Khách hàng đã nhắn tin</h5>
        <div class="input-group" style="max-width: 260px;">
        </div>
    </div>

    <div class="card-body p-0">
        @if($users->isEmpty())
            <div class="alert alert-secondary m-3 text-center">Chưa có khách hàng nào nhắn tin.</div>
        @else
            <div class="chat-list-container list-group list-group-flush">
                @foreach($users as $item)
                    <a href="{{ route('admin.chat', $item->user_id) }}"
                       class="chat-list-item {{ request()->route('userId') == $item->user_id ? 'active' : '' }}">
                       
                        <div class="chat-avatar">
                            <img
                                src="{{ $item->user->img_thumbnail ? asset('storage/' . $item->user->img_thumbnail) : 'https://i.pravatar.cc/150?u=' . $item->user_id }}"
                                alt="Avatar">
                        </div>

                        <div class="chat-info">
                            <div class="user-name">{{ $item->user->name ?? 'Khách chưa đăng ký' }}</div>
                            <div class="last-message">
                                @if(isset($item->latest_message))
                                    @if($item->latest_message->sender === 'admin')
                                        <span class="text-muted">Bạn: </span>
                                    @endif
                                    {{ $item->latest_message->message }}
                                @else
                                    <em>Chưa có tin nhắn.</em>
                                @endif
                            </div>
                        </div>

                        <div class="chat-meta text-end">
                            @if($item->latest)
                                <div class="small">
                                    {{ \Carbon\Carbon::parse($item->latest)->diffForHumans(['short' => true]) }}
                                </div>
                            @endif

                            @if(isset($item->unread_count) && $item->unread_count > 0)
                                <span class="unread-indicator" title="{{ $item->unread_count }} chưa đọc"></span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection
