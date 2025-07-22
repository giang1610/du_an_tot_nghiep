<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GetUserController extends Controller
{
    public function getUser(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token không hợp lệ'], 401);
        }

        $plainToken = substr($authHeader, 7); 

        if (!str_contains($plainToken, '|')) {
            return response()->json(['message' => 'Token sai định dạng'], 401);
        }

        [$tokenId, $tokenPart] = explode('|', $plainToken, 2);

        // Hash phần sau của token để so sánh với cột 'token' trong DB
        $hashedToken = hash('sha256', $tokenPart);

        // Tìm token record
        $tokenRecord = DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->where('token', $hashedToken)
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Token không hợp lệ hoặc đã hết hạn'], 401);
        }

        // Lấy user từ token
        $user = User::find($tokenRecord->tokenable_id);

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        // Trả thông tin user
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'img_thumbnail' => $user->img_thumbnail,
        ]);
    }
}
