<?php

namespace App\Http\Controllers\Api;
use Illuminate\Auth\Events\Registered;
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8', 
        ]);

        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Kích hoạt sự kiện Registered.
        // Laravel sẽ tự động gửi email xác thực nếu User model implement MustVerifyEmail.
        event(new Registered($user));


        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản của bạn.',
            'user' => [ // Trả về thông tin cơ bản của người dùng
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at, // Ban đầu sẽ là null
            ],
           
        ], 201); 
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
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
                    ],
                    'token' => $token,
                ]);
        }

        public function verifyEmail(Request $request)
        {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
            }

            if ($user->email_verified_at) {
                return response()->json(['message' => 'Email đã được xác thực.']);
            }

            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Email đã được xác thực thành công.']);
        }


        public function logout(Request $request)
        {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Đăng xuất thành công']);
        }
}