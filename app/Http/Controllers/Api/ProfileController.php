<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:20',
            'email' => 'required|string|email|max:40',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'avatar' => 'nullable|string', // base64 string
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Kiểm tra email trùng người khác
        if (
            $request->email !== $user->email &&
            User::where('email', $request->email)->where('id', '!=', $user->id)->exists()
        ) {
            return response()->json(['message' => 'Email này đã được sử dụng'], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];


        if ($request->avatar) {
            try {
                $base64 = $request->avatar;

                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    $imageData = base64_decode($base64);
                    if ($imageData === false) {
                        return response()->json(['message' => 'Ảnh không hợp lệ'], 422);
                    }

                    $fileName = 'avatars/' . Str::uuid() . '.' . $type;
                    Storage::disk('public')->put($fileName, $imageData);


                    $updateData['img_thumbnail'] = $fileName;
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Không thể xử lý ảnh'], 422);
            }
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'user' => $user,
        ]);
    }
}
