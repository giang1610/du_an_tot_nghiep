<?php

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
    }
}
