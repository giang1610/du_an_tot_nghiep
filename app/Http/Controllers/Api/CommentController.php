<?php
// app/Http/Controllers/Api/CommentController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // public function comment(Request $request, $productId)
    // {
    //     // Validate dữ liệu đầu vào
    //     $request->validate([
    //         'content' => 'required|string'
    //     ]);

    //     // Kiểm tra đăng nhập
    //     if (!Auth::check()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Bạn cần đăng nhập để bình luận.'
    //         ], 401);
    //     }

    //     // Kiểm tra sản phẩm tồn tại
    //     $product = Product::find($productId);
    //     if (!$product) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Sản phẩm không tồn tại.'
    //         ], 404);
    //     }

    //     // Lấy user hiện tại
    //     $user = Auth::user();

    //     // Lưu bình luận (không có rating)
    //     $comment = Comment::create([
    //         'user_id' => $user->id,
    //         'product_id' => $productId,
    //         'content' => $request->content,
    //         'rating' => null, // Không có đánh giá
    //     ]);

    //     $comment->load([
    //         'user' => function ($query) {
    //             $query->select('id', 'name', 'email');
    //         }
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Bình luận thành công.',
    //         'data' => $comment
    //     ], 201);
    // }

    public function rate(Request $request, $productId)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'content' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để đánh giá.'
            ], 401);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        // Lấy user hiện tại
        $user = Auth::user();
        // Lấy danh sách id các variant thuộc sản phẩm này
        $variantIds = ProductVariant::where('product_id', $productId)->pluck('id');

        // Kiểm tra user đã mua sản phẩm và đơn hàng đã giao (status = shipped)
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('status', 'shipped')
            ->whereHas('items', function ($query) use ($variantIds) {
                $query->whereIn('product_variant_id', $variantIds);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể đánh giá sau khi đơn hàng đã giao thành công.'
            ], 403);
        }

        // Lưu đánh giá (có thể kèm nội dung)
        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        $comment->load([
            'user' => function ($query) {
                $query->select('id', 'name', 'email');
            }
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đánh giá thành công.',
            'data' => $comment
        ], 201);
    }
}
