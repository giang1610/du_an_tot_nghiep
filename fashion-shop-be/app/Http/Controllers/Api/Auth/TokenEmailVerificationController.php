<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendNewEmailVerificationJob;
use App\Models\User;

class TokenEmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = $request->user();

        // Nếu email trùng → xác minh
        if ($user->email === $request->email) {
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email đã xác minh.'], 400);
            }

            $user->markEmailAsVerified();

            return response()->json([
                'message' => 'Email đã được xác minh.',
                'email_verified_at' => $user->email_verified_at,
            ]);
        }

        // Nếu email khác
        $newEmail = $request->email;
        if (User::where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
            return response()->json([
            'message' => 'Email này đã được sử dụng bởi người dùng khác. Vui lòng chọn email khác.'
            ], 422);
        }
       

        SendNewEmailVerificationJob::dispatch($user->id, $newEmail);

        return response()->json([
            'message' => 'Đã gửi email xác minh đến địa chỉ mới. Vui lòng kiểm tra email để hoàn tất.',
        ]);
    }
}
