@extends('admin.layouts.app')
<style>
    .row{
         background-image: linear-gradient(rgb(148, 114, 87), rgb(198, 210, 223));
         border-radius: 10px;
    }
</style>
@section('content')
<div class="row m-5">
    <div class="col-md-4 mt-2">
        @include('admin.chat.partials.list')
    </div>
    <div class="col-md-8 mt-2">
        @include('admin.chat.partials.show')
    </div>
</div>
@endsection

@section('scripts')
@if(isset($user))
<script>
    window.chatUserId = {{ $user->id }};
</script>
<script src="{{ asset('js/chat-realtime.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBox = document.getElementById('chat-box');
        if (chatBox) {
            setTimeout(() => {
                chatBox.scrollTop = chatBox.scrollHeight;
            }, 10);
        }
    });
</script>
@endif
@endsection
