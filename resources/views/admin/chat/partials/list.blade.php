
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<div class="card shadow-sm">
    <div class="card-header  d-flex justify-content-between"   >
        <h5 class="mb-0" >Đoạn chat</h5>
        <div class="input-group" style="max-width: 260px;">
            <input type="text" class="form-control m-1 pa" id="search-input" placeholder="Tìm kiếm khách hàng...">
            <button class="btn btn-primary" type="button" id="search-btn"><i class="fas fa-search"></i></button>
        </div>
    </div>

    <div class="card-body p-0">
        @if($users->isEmpty())
            <div class="alert alert-secondary m-3 text-center ">Chưa có khách hàng nào nhắn tin.</div>
        @else
            <div class="chat-list-container list-group list-group-flush">
                @foreach($users as $item)
                    <a href="{{ route('admin.chat', $item->user_id) }}"
                       class="chat-list-item {{ isset($user) && $user->id == $item->user_id ? 'active' : '' }}">

                        <div class="chat-avatar">
                            <img
                                src="{{ $item->user->img_thumbnail ? asset('storage/' . $item->user->img_thumbnail) : 'https://i.pravatar.cc/150?u=' . $item->user_id }}"
                                alt="Avatar">
                        </div>

                        <div class="chat-info">
                            <div class="user-name">{{ $item->user->name ?? 'Khách chưa đăng ký' }}</div>
                            <div class="last-message">
                                @if(isset($item->latestMessage))
                                    @if($item->latestMessage->sender === 'admin')
                                        <span class="text-muted">Bạn: </span>
                                      
                                    @endif
                                    {{ $item->latestMessage->message }}
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
