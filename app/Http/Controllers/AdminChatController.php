<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\User;

class AdminChatController extends Controller
{
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $chats = Chat::where('user_id', $userId)->orderBy('created_at')->get();

        return view('admin.chat', compact('user', 'chats'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $chat = Chat::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
            'sender'  => 'admin',
        ]);

        return back()->with('success', 'Tin nhắn đã gửi!');
    }
    public function listUsers()
    {
    $users = Chat::select('user_id', \DB::raw('MAX(created_at) as latest'))
    ->with('user')
    ->groupBy('user_id')
    ->orderByDesc('latest')
    ->get();


    return view('admin.chat.index', compact('users'));
    }

}
