<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use App\Events\UserTypingEvent;
use App\Events\NewMessageEvent;

class ChatController extends Controller
{
  
    public function index(Request $request)
        {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu user_id'
                ], 400);
            }

            $messages = Chat::where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        }


    /**
     * Gửi tin nhắn từ client
     */
    public function store(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        broadcast(new NewMessageEvent($validated['message'], $user->id,'user',$request['avatar']));

        $chat = Chat::create([
            'user_id' => $user->id,
            'sender' => 'user',
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tin nhắn đã được gửi!',
            'data' => $chat
        ], 201);
    }


    public function typing(Request $request)
    {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        broadcast(new UserTypingEvent($user))->toOthers();
        return response()->json(['status' => 'ok']);
    }
}
