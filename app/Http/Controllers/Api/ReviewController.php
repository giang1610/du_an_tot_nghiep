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
        // Validate cơ bản
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240', // Mỗi file <= 10MB
        ]);

        $user = auth()->user();
        $order = Order::findOrFail($request->order_id);

        // ✅ 1. Kiểm tra quyền sở hữu đơn hàng
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Bạn không có quyền đánh giá đơn hàng này.'], 403);
        }

        // ✅ 2. Kiểm tra trạng thái đơn hàng
        if ($order->status !== 'completed') {
            return response()->json(['message' => 'Chỉ có thể đánh giá khi đơn đã được nhận.'], 400);
        }

        // ✅ 3. Kiểm tra số lần đánh giá trước đó
        $existingReviews = Review::where([
            ['user_id', '=', $user->id],
            ['order_id', '=', $order->id],
            ['product_variant_id', '=', $request->product_variant_id]
        ])->orderByDesc('created_at')->get();

        $reviewCount = $existingReviews->count();

        if ($reviewCount >= 2) {
            return response()->json(['message' => 'Bạn chỉ được đánh giá tối đa 2 lần cho sản phẩm này.'], 400);
        }

        // ✅ 4. Xác định lần đánh giá
        $reviewRound = $existingReviews->contains('review_round', 1) ? 2 : 1;

        // ✅ 5. Nếu là lần 2: kiểm tra ngày giao hàng >= 7 ngày
        if ($reviewRound === 2) {
            if (empty($order->completed_at)) {
                return response()->json(['message' => 'Không xác định được ngày hoàn tất đơn hàng.'], 400);
            }

            $daysSinceCompleted = Carbon::parse($order->completed_at)->diffInDays(now());

            if ($daysSinceCompleted < 7) {
                return response()->json([
                    'message' => 'Bạn chỉ có thể đánh giá lần 2 sau 7 ngày kể từ ngày giao hàng.'
                ], 422);
            }
        }

        // ✅ 6. Xử lý media nếu có
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mediaPaths[] = $file->store('reviews', 'public');
            }
        }

        // ✅ 7. Tạo đánh giá
        $review = Review::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'product_variant_id' => $request->product_variant_id,
            'review_round' => $reviewRound,
            'rating' => $request->rating,
            'content' => $request->content,
            'media' => json_encode($mediaPaths),
            'status' => 1, // mặc định hiển thị
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công.',
            'review' => $review
        ], 201);
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

    // Lấy danh sách đánh giá cho 1 variant cụ thể
    public function receivedOrders(Request $request)
    {
        $user = $request->user();
        $variantId = $request->query('product_variant_id');

        $order = Order::where('user_id', $user->id)
            ->where('status', 'completed')
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
