<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;
use App\Events\NewMessageEvent;

class AdminChatController extends Controller
{
    public function index($userId = null)
    {
        $users = Chat::select('user_id', \DB::raw('MAX(created_at) as latest'))
            ->with(['user', 'latestMessage'])
            ->groupBy('user_id')
            ->orderByDesc('latest')
            ->get();

        $user = null;
        $chats = [];

        if ($userId) {
            $user = User::findOrFail($userId);
            $chats = Chat::where('user_id', $userId)->orderBy('created_at')->get();
        }

        return view('admin.chat.index', compact('users', 'user', 'chats'));
    }

    public function send(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = User::findOrFail($userId);

        broadcast(new NewMessageEvent($request->message, $user->id, 'admin',null))->toOthers();

        Chat::create([
            'user_id' => $user->id,
            'message' => $request->message,
            'sender'  => 'admin',
        ]);

        return back()->with('success', 'Tin nhắn đã gửi!');
    }
}
