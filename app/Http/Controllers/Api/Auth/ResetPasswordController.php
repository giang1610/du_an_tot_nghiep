<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password as PasswordRules;

class ResetPasswordController extends Controller
{
    /**
     * Reset the user's password using a token.
     * The client only sends the token and the new password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
       
        $request->validate([
            'token' => 'required|string',
            'password' => ['required', 'confirmed', PasswordRules::min(8)],
        ]);

        return DB::transaction(function () use ($request) {
            
           
            $record = DB::table('password_reset_tokens')
                        ->where('token', $request->token)
                        ->first();

            // If no record is found, the token is invalid.
            if (!$record) {
                 return response()->json(['message' => 'Token không hợp lệ.'], 400);
            }

            $expiresInMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 15);
            if (Carbon::parse($record->created_at)->addMinutes($expiresInMinutes)->isPast()) {
                DB::table('password_reset_tokens')->where('token', $request->token)->delete();
                return response()->json(['message' => 'Token đã hết hạn. Vui lòng yêu cầu lại.'], 400);
            }

           //gán user
            $user = User::where('email', $record->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Không tìm thấy người dùng tương ứng với token này.'], 404);
            }

          
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.'], 422);
            }

            
            $user->password = Hash::make($request->password);
            $user->save();

           //xóa token
            DB::table('password_reset_tokens')->where('email', $record->email)->delete();

            return response()->json(['message' => 'Mật khẩu đã được cập nhật thành công.']);
        });
    }
}