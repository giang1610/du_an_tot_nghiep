<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

use Illuminate\Http\Request;
use App\Models\Color;
use App\Models\Size;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'variants');
    //    $a = now();
    //      dd($a);
        if($request->has('search')){
            $query->where('name','like','%' . $request->search .'%');
        }

    $products = $query->orderBy('id','desc')->paginate(5);
    // dd($products);
    return view('admin.products.index', compact('products'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        $colors     = Color::all();
        $sizes      = Size::all();
        return view('admin.products.create', compact('categories', 'colors', 'sizes'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(ProductRequest $request)
{
    try {
        // Ảnh đại diện sản phẩm
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
        }

        // Tạo sản phẩm cha
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'slug' => $request->slug,
            'thumbnail' => $thumbnailPath,
            'category_id' => $request->category_id,
            'status' => $request->status,
        ]);

        // Gắn màu & size chung nếu có
        if ($request->has('color_id')) {
            $product->colors()->attach($request->color_id); // nhiều màu
        }

        if ($request->has('size_id')) {
            $product->sizes()->attach($request->size_id); // nhiều size
        }

        // Nếu có biến thể thì xử lý thêm
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $index => $variantData) {
                $variant = new ProductVariant([
                    'sku' => $variantData['sku'] ?? null,
                    'price' => $variantData['price'] ?? null,
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'sale_start_date' => $variantData['sale_start_date'] ?? null,
                    'sale_end_date' => $variantData['sale_end_date'] ?? null,
                    'stock' => $variantData['stock'] ?? 0,
                    'color_id' => $variantData['color_id'] ?? null,
                    'size_id' => $variantData['size_id'] ?? null,
                ]);

                if ($request->hasFile("variants.{$index}.image")) {
                    $image = $request->file("variants.{$index}.image");
                    $variant->image = $image->store('variants', 'public');
                }

                $product->variants()->save($variant);
            }
        }

        return redirect()->route('products.index')->with('success', 'Thêm sản phẩm thành công!');
    } catch (\Exception $e) {
        return back()->with('error', 'Đã có lỗi xảy ra: ' . $e->getMessage());
    }
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
