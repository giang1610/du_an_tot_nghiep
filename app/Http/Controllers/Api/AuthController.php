<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        $verifyUrl = "http://localhost:5173/verify-email?email=" . urlencode($user->email);

        Mail::send([], [], function ($message) use ($user, $verifyUrl) {
            $message->to($user->email)
                ->subject('Xác nhận email đăng ký')
                ->html(
                    '<p>Đây là Thông Báo Từ Website. Vui lòng nhấn vào nút bên dưới để xác nhận email:</p>
                    <a href="' . $verifyUrl . '" style="display:inline-block;padding:10px 20px;background:#007bff;color:#fff;text-decoration:none;border-radius:5px;">Xác nhận email</a>
                    <p>Nếu bạn không đăng ký tài khoản, vui lòng bỏ qua email này.</p>'
                );
        });

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => $user,
            #token này để đăng nhập ngay sau khi đăng ký
            'token' => $token,

        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); 
        return response()->json(['message' => 'Đăng xuất thành công']);
    }
}
