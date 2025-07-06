<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string',
        ]);

        $user = auth()->user();
        $order = Order::findOrFail($request->order_id);

        // Kiểm tra quyền sở hữu đơn hàng
        if ($order->user_id !== $user->id) {
            return response()->json(['error' => 'Bạn không có quyền đánh giá đơn hàng này.'], 403);
        }

        // Kiểm tra trạng thái đơn hàng
        if ($order->status !== 'delivered') {
            return response()->json(['error' => 'Chỉ có thể đánh giá khi đơn đã được nhận.'], 400);
        }

        // Kiểm tra số lần đánh giá
        $existingReviews = Review::where([
            ['user_id', '=', $user->id],
            ['order_id', '=', $order->id],
            ['product_variant_id', '=', $request->product_variant_id]
        ])->get();

        if ($existingReviews->count() >= 2) {
            return response()->json(['error' => 'Bạn chỉ được đánh giá tối đa 2 lần cho sản phẩm này.'], 400);
        }

        // Xác định lần đánh giá
        $reviewRound = $existingReviews->contains('review_round', 1) ? 2 : 1;

        // Nếu là lần 2 thì kiểm tra ngày giao hàng >= 7 ngày
        if ($reviewRound === 2) {
            if (empty($order->delivered_at)) {
                return response()->json(['error' => 'Không xác định được ngày giao hàng.'], 400);
            }
            if (Carbon::parse($order->delivered_at)->diffInDays(now()) < 7) {
                return response()->json(['error' => 'Bạn chỉ có thể đánh giá lần 2 sau 7 ngày kể từ ngày giao hàng.'], 400);
            }
        }

        // Tạo review
        $review = Review::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_variant_id' => $request->product_variant_id,
            'review_round' => $reviewRound,
            'rating' => $request->rating,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Đánh giá thành công', 'review' => $review], 201);
    }

    // Lấy danh sách đánh giá cho 1 sản phẩm (theo variant hoặc tổng hợp)
    public function listByProduct($productId)
    {
        $reviews = Review::with(['user:id,name'])
            ->whereHas('productVariant', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $reviews]);
    }
    public function receivedOrders(Request $request)
{
    $user = $request->user();
    $variantId = $request->query('product_variant_id');

    $order = Order::where('user_id', $user->id)
        ->where('status', 'delivered')
        ->whereHas('items', fn($q) => $q->where('product_variant_id', $variantId))
        ->latest()->first();

    return response()->json([
        'received' => !!$order,
        'order_id' => $order?->id
    ]);
}
// Lấy review qua query ?product_id=...
public function getByProductQuery(Request $request)
{
    $productId = $request->query('product_id');
    if (!$productId) {
        return response()->json(['error' => 'Thiếu product_id'], 400);
    }

    return $this->listByProduct($productId);
}


}