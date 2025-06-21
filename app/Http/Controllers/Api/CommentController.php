<?php
// app/Http/Controllers/Api/CommentController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    public function store(Request $request, $productId)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để bình luận.'
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

        // Kiểm tra user đã mua sản phẩm và đơn hàng đã giao (status = shipped)
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('status', 'shipped') // Chỉ cho phép khi đơn đã giao
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chỉ có thể bình luận sau khi đơn hàng đã giao thành công.'
            ], 403);
        }

        // Lưu bình luận
        $comment = new Comment();
        $comment->product_id = $productId;
        $comment->user_id = $user->id;
        $comment->content = $request->content;
        $comment->rating = $request->rating;
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Bình luận thành công.',
            'data' => $comment->load([
                'user' => function ($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
        ], 201);
    }
}
