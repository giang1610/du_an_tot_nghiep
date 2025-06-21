<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Tính toán giá hiển thị của sản phẩm (gồm giá khuyến mãi nếu có)
     */
    private function processProductPricing($product)
    {
        if ($product->variants && $product->variants->isNotEmpty()) {
            $now = Carbon::now('Asia/Ho_Chi_Minh');
            $minDisplayPrice = null;

            foreach ($product->variants as $variant) {
                $saleStart = $variant->sale_start_date ? Carbon::parse($variant->sale_start_date) : null;
                $saleEnd = $variant->sale_end_date ? Carbon::parse($variant->sale_end_date) : null;
                $isOnSale = $saleStart && $saleEnd && $now->between($saleStart, $saleEnd);

                $variant->display_price = $isOnSale && $variant->sale_price
                    ? $variant->sale_price
                    : $variant->price;

                if (!$isOnSale) $variant->sale_price = null;

                if (is_null($minDisplayPrice) || $variant->display_price < $minDisplayPrice) {
                    $minDisplayPrice = $variant->display_price;
                }
            }

            $product->price_original = $minDisplayPrice;
        } else {
            $product->price_original = null;
        }
    }

    /**
     * Lấy danh sách sản phẩm với bộ lọc
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('status', 1);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('price')) {
            [$min, $max] = explode('-', $request->price);
            $query->whereHas('variants', fn($q) => $q->whereBetween('price', [(int)$min, (int)$max]));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->with([
            'variants:id,product_id,price,sale_price,sale_start_date,sale_end_date',
        ])->get();

        foreach ($products as $product) {
            $this->processProductPricing($product);
        }

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Lấy chi tiết sản phẩm theo slug
     */
    public function showBySlug($slug)
    {
        $product = Product::with([
            'variants.size:id,name',
            'variants.color:id,name',
            'variants.images:id,url,product_variant_id,is_default',
            'comments.user:id,name,email',
            'images:id,url,product_id,is_default',
            'category:id,name,slug'
        ])->where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $this->processProductPricing($product);

        $related = Product::with('images:id,url,product_id,is_default')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        foreach ($related as $item) {
            $this->processProductPricing($item);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $related
            ]
        ]);
    }

    /**
     * Lấy chi tiết sản phẩm theo ID (dùng nội bộ)
     */
    public function showById($id)
    {
        $product = Product::with('variants')->find($id);
        if (!$product) return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Danh sách sản phẩm liên quan theo category_id
     */
    public function related($category_id, Request $request)
    {
        $excludeId = $request->query('exclude');

        $query = Product::where('category_id', $category_id);
        if ($excludeId) $query->where('id', '!=', $excludeId);

        $relatedProducts = $query->with([
            'variants.color:id,name',
            'variants.size:id,name',
            'images:id,url,product_id,is_default'
        ])->get();

        foreach ($relatedProducts as $item) {
            $this->processProductPricing($item);
        }

        return response()->json(['success' => true, 'data' => $relatedProducts]);
    }

    /**
     * Lấy tất cả bình luận cho sản phẩm
     */
    public function comments($id)
    {
        $comments = Comment::with('user:id,name,email')
            ->where('product_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $comments]);
    }

    /**
     * Gửi bình luận sản phẩm
     */
    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        $comment->load('user:id,name,email');

        return response()->json([
            'success' => true,
            'message' => 'Bình luận thành công.',
            'data' => $comment
        ], 201);
    }
}
