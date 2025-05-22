<?php

namespace App\Http\Controllers\API\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        // Tìm bản ghi token tương ứng
        $record = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->where('token', $request->token)
                    ->first();

        if (!$record) {
            return response()->json(['message' => 'Token không hợp lệ hoặc đã hết hạn.'], 400);
        }

        // Kiểm tra token có quá 15 phút không
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            // Xoá token đã hết hạn
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token đã hết hạn. Vui lòng yêu cầu lại.'], 400);
        }

        // Tìm người dùng
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng.'], 404);
        }

        // Kiểm tra nếu mật khẩu mới trùng với mật khẩu hiện tại
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu mới không được trùng với mật khẩu trước đây. Vui lòng nhập mật khẩu khác.'], 400);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        // Xoá token sau khi dùng
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mật khẩu đã được cập nhật thành công.']);
    }
}
