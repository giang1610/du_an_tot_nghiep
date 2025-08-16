<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Tính toán giá hiển thị (display_price) cho từng variant của sản phẩm
     * Nếu có khuyến mãi đang diễn ra, dùng sale_price
     */
    private function processProductPricing($product)
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $minDisplayPrice = null;

        foreach ($product->variants as $variant) {
            $saleStart = $variant->sale_start_date ? Carbon::parse($variant->sale_start_date) : null;
            $saleEnd = $variant->sale_end_date ? Carbon::parse($variant->sale_end_date) : null;

            $isSaleActive = $saleStart && $saleEnd && $now->between($saleStart, $saleEnd);

            if ($isSaleActive && $variant->sale_price) {
                $variant->display_price = $variant->sale_price;
            } else {
                $variant->display_price = $variant->price;
            }

            if (is_null($minDisplayPrice) || $variant->display_price < $minDisplayPrice) {
                $minDisplayPrice = $variant->display_price;
            }
        }

        $product->price_original = $minDisplayPrice;
    }

    /**
     * Trả về danh sách sản phẩm (dùng cho trang chủ + trang lọc)
     */
    public function index(Request $request)
    {
        $query = Product::query()->where('status', 1);

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('price')) {
            $range = explode('-', $request->price);
            if (count($range) === 2) {
                [$min, $max] = $range;
                $query->whereHas('variants', fn($q) => $q->whereBetween('price', [(int) $min, (int) $max]));
            }
        }

        if ($request->filled('size')) {
            $query->whereHas('variants', fn($q) => $q->where('size_id', $request->size));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sort = $request->input('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price_products', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price_products', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->with([
            'variants:id,product_id,price,sale_price,sale_start_date,sale_end_date,size_id,color_id',
            'images:id,product_id,url'
        ])->latest()->get();

        foreach ($products as $product) {
            $this->processProductPricing($product);
        }

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Chi tiết sản phẩm theo slug
     */
    public function showBySlug($slug)
    {
        $product = Product::with([
            'variants.size',
            'variants.color',
            'variants.stock',
            'variants.images',
            'images',
            'category'
        ])->where('slug', $slug)->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $this->processProductPricing($product);
        foreach ($product->variants as $variant) {
            $variant->images_urls = $variant->images->map(function ($img) {
                return asset('storage/' . $img->url);
            });
        }

        $reviews = Review::with('user:id,name')
            ->whereHas('productVariant', fn($q) => $q->where('product_id', $product->id))
            ->latest()->get();

        // ✅ Sửa: chỉ lấy sản phẩm liên quan đang hoạt động, có variant và ảnh
        $related = Product::with([
            'variants:id,product_id,price,sale_price,sale_start_date,sale_end_date,size_id,color_id',
            'images:id,product_id,url'
        ])
            ->where('status', 1)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        foreach ($related as $rp) {
            $this->processProductPricing($rp);

            // ✅ Thêm dòng xử lý ảnh để không bị mất ảnh
            $rp->image_urls = $rp->images->map(function ($img) {
                return asset('storage/' . $img->url);
            });
        }


        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'reviews' => $reviews,
                'related_products' => $related
            ]
        ]);
    }

    /**
     * API: Sản phẩm liên quan theo category_id (dùng cho chi tiết sản phẩm)
     */
    public function related($category_id, Request $request)
    {
        $query = Product::where('category_id', $category_id)
            ->where('status', 1);

        if ($request->filled('exclude')) {
            $query->where('id', '!=', $request->exclude);
        }

        $related = $query->with([
            'variants.color',
            'variants.size',
            'variants.images'
        ])->get();

        foreach ($related as $product) {
            $this->processProductPricing($product);
        }

        return response()->json([
            'success' => true,
            'data' => $related
        ]);
    }

    /**
     * Chi tiết sản phẩm theo ID (dùng cho backend admin)
     */
    public function showById($id)
    {
        $product = Product::with('variants')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }
}
