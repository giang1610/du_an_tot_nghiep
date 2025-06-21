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

        // Thêm điều kiện lọc sản phẩm đang hoạt động
        $query->where('status', 1);

        // Lọc theo danh mục
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Lọc theo khoảng giá (dựa trên giá gốc)
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

        // Tìm kiếm theo tên sản phẩm
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Lấy dữ liệu sản phẩm cùng biến thể
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
            }
        ])->get();

        // Xử lý giá hiển thị cho từng sản phẩm
        foreach ($products as $product) {
            $this->processProductPricing($product);
        }
        // Map thêm đường dẫn ảnh

        return response()->json([
            'success' => true,
            'data' => $products,


        ]);
    }


    public function show($slug, Request $request)
    {
        // Lấy sản phẩm theo slug, đồng thời lấy các quan hệ liên quan như:
        // - comments.user: Lấy thông tin user của từng bình luận
        // - category: Lấy thông tin danh mục sản phẩm
        // - variants.color, variants.size: Lấy thông tin màu sắc, kích thước của từng biến thể
        // - variants.images, images: Lấy danh sách ảnh của từng biến thể và ảnh chính sản phẩm
        $product = Product::where('slug', $slug)
            ->with([
                'comments.user' => function ($query) {
                    $query->select('id', 'name', 'email');
                },
                'category' => function ($query) {
                    $query->select('id', 'name', 'slug');
                },
                'variants.color' => function ($query) {
                    $query->select('id', 'name');
                },
                'variants.size' => function ($query) {
                    $query->select('id', 'name');
                },
                'variants.images' => function ($query) {
                    $query->select('id', 'url', 'product_id', 'product_variant_id', 'is_default');
                },
                'images' => function ($query) {
                    $query->select('id', 'url', 'product_id', 'product_variant_id', 'is_default');
                }
            ])
            ->first();

        // Nếu không tìm thấy sản phẩm, trả về lỗi 404
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // Xử lý giá hiển thị cho sản phẩm chính
        $this->processProductPricing($product);

        // Lấy 5 sản phẩm liên quan
        // Lấy sản phẩm liên quan cùng danh mục, trừ sản phẩm hiện tại, giới hạn 5 sản phẩm
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'variants.color' => function ($query) {
                    $query->select('id', 'name');
                },
                'variants.size' => function ($query) {
                    $query->select('id', 'name');
                },
                'images' => function ($query) {
                    $query->select('id', 'url', 'product_id', 'product_variant_id', 'is_default');
                }
            ])
            ->take(5)
            ->get();
        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $relatedProducts
            ]
        ], 200);
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
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
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
    public function getBySlug($slug)
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại',
            ], 404);
        }

        // Lấy sản phẩm liên quan
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $product,
            'related' => $related,
        ]);
    }
    public function showBySlug($slug)
    {
        $product = Product::with([
            'comments.user',
            'variants.color',
            'variants.size',
        ])->where('slug', $slug)->firstOrFail();

        // Lấy danh sách các sản phẩm liên quan
        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'description' => $product->description,
                'img' => $product->img,
                'variants' => $product->variants, // <--- phần quan trọng
                'comments' => $product->comments,
            ],
            'related' => $related,
        ]);
    }


}
