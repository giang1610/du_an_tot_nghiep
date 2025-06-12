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
        return response()->json([
            'success' => true,
            'data' => $products,

        ]);
    }


    public function show($slug, Request $request)
    {
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

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

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
        $excludeId = $request->query('exclude');
        $query = Product::where('category_id', $category_id);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $relatedProducts = $query->with(['variants.color', 'variants.size', 'images'])->get();

        // Xử lý giá hiển thị
        foreach ($relatedProducts as $relatedProduct) {
            $this->processProductPricing($relatedProduct);
        }

        return response()->json([
            'success' => true,
            'data' => $relatedProducts,
        ]);
    }

    public function comments($id)
    {
        $comments = Comment::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'email');
            }
        ])
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ], 200);
    }

    public function storeComment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại.'
            ], 404);
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);
        $comment->load([
            'user' => function ($query) {
                $query->select('id', 'name', 'email');
            }
        ]);

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
