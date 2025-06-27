<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Xử lý giá hiển thị cho sản phẩm
     */

    private function processProductPricing($product)
    {
        if ($product->variants && $product->variants->isNotEmpty()) {
            $now = Carbon::now('Asia/Ho_Chi_Minh');
            $minDisplayPrice = null;

            foreach ($product->variants as $variant) {
                $saleStartDate = $variant->sale_start_date ? Carbon::parse($variant->sale_start_date, 'Asia/Ho_Chi_Minh') : null;
                $saleEndDate = $variant->sale_end_date ? Carbon::parse($variant->sale_end_date, 'Asia/Ho_Chi_Minh') : null;

                $isPromotionActive = $saleStartDate && $saleEndDate && $now->between($saleStartDate, $saleEndDate);

                if ($isPromotionActive && $variant->sale_price) {
                    $variant->display_price = $variant->sale_price;
                } else {
                    $variant->display_price = $variant->price;
                    $variant->sale_price = null;
                }

                // Gán giá hiển thị nhỏ nhất
                if (is_null($minDisplayPrice) || $variant->display_price < $minDisplayPrice) {
                    $minDisplayPrice = $variant->display_price;
                }
            }

            // Cập nhật giá gốc của sản phẩm là giá hiển thị nhỏ nhất
            $product->price_original = $minDisplayPrice;
        } else {
            $product->price_original = null;
        }
    }


    public function index(Request $request)
{
    $query = Product::query();

    $query->where('status', 1);

    if ($request->has('category') && $request->category != '') {
        $query->where('category_id', $request->category);
    }

    if ($request->has('price') && $request->price != '') {
        $priceRange = explode('-', $request->price);
        if (count($priceRange) === 2) {
            $min = (int) $priceRange[0];
            $max = (int) $priceRange[1];
            $query->whereHas('variants', function ($q) use ($min, $max) {
                $q->whereBetween('price', [$min, $max]);
            });
        }
    }

    // LỌC THEO SIZE
    if ($request->has('size') && $request->size != '') {
        $sizeId = (int) $request->size;
        $query->whereHas('variants', function ($q) use ($sizeId) {
            $q->where('size_id', $sizeId);
        });
    }

    if ($request->has('search') && $request->search != '') {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $products = $query->with([
        'variants' => function ($q) {
            $q->select(
                'id',
                'product_id',
                'price',
                'sale_price',
                'sale_start_date',
                'sale_end_date'
            );
        },
        'images'
    ])->get();

    foreach ($products as $product) {
        $this->processProductPricing($product);
    }

    return response()->json([
        'success' => true,
        'data' => $products,
    ]);
}


    public function related($category_id, Request $request)
    {
        // Lấy tham số 'exclude' từ query string để loại trừ sản phẩm hiện tại (nếu có)
        $excludeId = $request->query('exclude');
        // Khởi tạo query lấy các sản phẩm cùng danh mục
        $query = Product::where('category_id', $category_id);
        // Nếu có truyền excludeId, loại trừ sản phẩm này khỏi kết quả
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        // Lấy danh sách sản phẩm liên quan, kèm theo các quan hệ: variants.color, variants.size, images
        $relatedProducts = $query->with(['variants.color', 'variants.size', 'images'])->get();

        // Duyệt qua từng sản phẩm để xử lý giá hiển thị (giá khuyến mãi, giá gốc, ...)
        foreach ($relatedProducts as $relatedProduct) {
            $this->processProductPricing($relatedProduct);
        }
        // Trả về kết quả dạng JSON
        return response()->json([
            'success' => true, // Trạng thái thành công
            'data' => $relatedProducts, // Danh sách sản phẩm liên quan
        ]);
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

        // Xử lý giá hiển thị
        $this->processProductPricing($product);

        // Lấy danh sách đánh giá cho sản phẩm (tổng hợp từ các variant)
        $reviews = Review::with(['user:id,name'])
            ->whereHas('productVariant', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->orderByDesc('created_at')
            ->get();

        // Sản phẩm liên quan cùng danh mục
        $related = Product::with(['images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'reviews' => $reviews,
                'related_products' => $related
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