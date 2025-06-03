<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Lọc theo danh mục
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Lọc theo khoảng giá, format: "min-max"
        if ($request->has('price') && $request->price != '') {
            $priceRange = explode('-', $request->price);
            if (count($priceRange) === 2) {
                $min = (int)$priceRange[0];
                $max = (int)$priceRange[1];
                $query->whereBetween('price', [$min, $max]);
            }
        }

        // Tìm kiếm theo tên sản phẩm
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->get();
      
        return response()->json([
            'success' => true,
            'data' => $products,
           
        ]);
    }

    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại',
            ], 404);
        }

        // Lấy sản phẩm liên quan cùng danh mục, trừ sản phẩm hiện tại, giới hạn 4 sản phẩm
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $product,
            'related' => $relatedProducts,
        ]);
    }

    public function related($category_id, Request $request)
    {
        $excludeId = $request->query('exclude');  // lấy query param ?exclude=1

        $query = Product::where('category_id', $category_id);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $relatedProducts = $query->get();

        return response()->json([
            'data' => $relatedProducts,
        ]);
    }

    public function comments($id)
    {
        $comments = Comment::with('user')
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $comments]);
    }

    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $request->validate(['content' => 'required|string']);

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'content' => $request->content,
        ]);
        $comment->load('user');

        return response()->json(['data' => $comment]);
    }
}
