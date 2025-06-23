<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\OrderItem;
use App\Models\ProductVariant;

class CommentController extends Controller
{
    public function rate(Request $request, $productId)
    {
        $user = $request->user();

        // Lấy danh sách variant_id thuộc sản phẩm
        $variantIds = ProductVariant::where('product_id', $productId)->pluck('id');

        // Kiểm tra người dùng đã mua hàng chưa
        $hasPurchased = OrderItem::whereIn('product_variant_id', $variantIds)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->whereIn('status', ['delivered', 'completed']);
            })->exists();

        if (!$hasPurchased) {
            return response()->json([
                'message' => 'Bạn chỉ có thể đánh giá sau khi đã nhận hàng.'
            ], 403);
        }

        // Validate dữ liệu
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Tạo comment
        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'content' => $validated['content'],
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công!',
            'data' => $comment->load('user'),
        ]);
    }
}
