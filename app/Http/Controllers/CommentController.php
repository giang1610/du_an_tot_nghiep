<?php

<<<<<<< HEAD
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để bình luận.'
            ], 401);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        $comment = new Comment();
        $comment->product_id = $productId;
        $comment->user_id = Auth::id();
        $comment->content = $request->content;
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Bình luận thành công.',
            'comment' => $comment->load('user')
        ]);
=======
namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // Lấy bình luận của 1 sản phẩm
    public function getByProduct($id)
    {
        $comments = Comment::with('user:id,name') // load tên người dùng
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    // Thêm bình luận mới
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
>>>>>>> b0ca560353e2869b0feee85a75af71ae0bb9b699
    }
}
