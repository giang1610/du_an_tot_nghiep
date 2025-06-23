<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductController extends Controller
{
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

                if (!$isOnSale) {
                    $variant->sale_price = null;
                }

                if (is_null($minDisplayPrice) || $variant->display_price < $minDisplayPrice) {
                    $minDisplayPrice = $variant->display_price;
                }
            }

            $product->price_original = $minDisplayPrice;
        } else {
            $product->price_original = null;
        }
    }

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

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function related($category_id, Request $request)
    {
        $excludeId = $request->query('exclude');
        $query = Product::where('category_id', $category_id);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $relatedProducts = $query->with(['variants.color', 'variants.size', 'images'])->get();

        foreach ($relatedProducts as $relatedProduct) {
            $this->processProductPricing($relatedProduct);
        }

        return response()->json([
            'success' => true,
            'data' => $relatedProducts,
        ]);
    }
    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập.'
            ], 401);
        }

        // Kiểm tra user đã mua và nhận hàng chưa
        $hasPurchased = OrderItem::where('product_id', $id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'delivered'); // 'delivered' hoặc 'completed'
            })->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ những người đã mua và nhận sản phẩm mới có thể đánh giá.'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }
    }

    public function showBySlug($slug)
    {
        $product = Product::with([
            'variants.size',
            'variants.color',
            'variants.images',
            'images',
            'category'
        ])
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        $this->processProductPricing($product);

        $related = Product::with(['images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        // Xác định xem người dùng hiện tại có thể đánh giá không
        $canComment = false;
        if (Auth::check()) {
            $user = Auth::user();
            $canComment = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('status', 'delivered');
                })->exists();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $related,
                'can_comment' => $canComment
            ]
        ]);
    }

    public function showById($id)
    {
        $product = Product::with(['variants'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }
}
