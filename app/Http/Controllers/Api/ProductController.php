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

    // Lấy danh sách bình luận của sản phẩm theo ID
    public function comments($id)
    {
        // Lấy danh sách bình luận, kèm thông tin user (id, name, email) cho từng bình luận
        $comments = Comment::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'email');
            }
        ])
            ->where('product_id', $id) // Lọc theo ID sản phẩm
            ->orderBy('created_at', 'desc') // Sắp xếp mới nhất lên đầu
            ->get();
        // Trả về danh sách bình luận dạng JSON
        return response()->json([
            'success' => true,
            'data' => $comments
        ], 200);
    }
    // Lưu bình luận cho sản phẩm
    public function storeComment(Request $request, $id)
    {
        // Lấy thông tin user đang đăng nhập
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        // Validate dữ liệu gửi lên
        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);
        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }
        // Tạo bình luận mới
        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);
        // Load thêm thông tin user cho bình luận vừa tạo
        $comment->load([
            'user' => function ($query) {
                $query->select('id', 'name', 'email');
            }
        ]);
        // Trả về kết quả thành công và dữ liệu bình luận vừa tạo
        return response()->json([
            'success' => true,
            'message' => 'Bình luận thành công.',
            'data' => $comment
        ], 201);
    }

    public function showBySlug($slug)
    {
        $product = Product::with([
            'variants.size',
            'variants.color',
            'variants.images',
            'comments.user',
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
