<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email không tồn tại'], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        $resetUrl =  "http://localhost:5173/reset-password?token={$token}&email=" . urlencode($user->email);


        Mail::raw("Đây là Thông Báo Từ Website nhấp vào link để đổi mật khẩu: $resetUrl", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Khôi phục mật khẩu');
        });

        return response()->json(['message' => 'Đã gửi email khôi phục mật khẩu.',
        'token' => $token
    ]);
    }
}
