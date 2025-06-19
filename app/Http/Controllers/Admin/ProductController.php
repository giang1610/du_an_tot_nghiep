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
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'variants');
        // Kiểm tra xem có tìm kiếm hay không
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
            // Xử lý ảnh đại diện sản phẩm
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                // Nếu có file mới được upload, lưu file đó
                $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
            } else if ($request->filled('thumbnail_old')) {
                // Nếu không có file mới, nhưng có đường dẫn ảnh cũ được gửi lên
                // (Điều này có thể xảy ra khi validation thất bại và người dùng không chọn ảnh mới)
                $thumbnailPath = $request->input('thumbnail_old');
            }
            // Trường hợp người dùng không upload file và cũng không có thumbnail_old
            // $thumbnailPath sẽ là null, đây là hành vi mong muốn.

            // Tạo sản phẩm cha
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'short_description' => $request->short_description,
                'slug' => $request->slug,
                'thumbnail' => $thumbnailPath, // Lưu đường dẫn ảnh chính
                'category_id' => $request->category_id,
                'status' => $request->status,
                'price_products' => $request->price_products,
            ]);

            // Gắn màu & size chung nếu có
            if ($request->has('color_id')) {
                $product->colors()->attach($request->color_id);
            }

            if ($request->has('size_id')) {
                $product->sizes()->attach($request->size_id);
            }

            // Nếu có biến thể thì xử lý thêm
            if ($request->has('variants') && is_array($request->variants)) {
                foreach ($request->variants as $index => $variantData) {
                    $variantImagePath = null;
                    // Kiểm tra xem có file ảnh mới cho biến thể này không
                    if ($request->hasFile("variants.{$index}.image")) {
                        $imageFile = $request->file("variants.{$index}.image");
                        $variantImagePath = $imageFile->store('variants', 'public');
                    } else if (isset($variantData['existing_image']) && $variantData['existing_image']) {
                        // Nếu không có file mới, nhưng có đường dẫn ảnh cũ được gửi lên
                        // (Khi validation thất bại và người dùng không chọn ảnh mới)
                        $variantImagePath = $variantData['existing_image'];
                    }

                    $variant = new ProductVariant([
                        'sku' => $variantData['sku'] ?? null,
                        'price' => $variantData['price'] ?? null,
                        'sale_price' => $variantData['sale_price'] ?? null,
                        'sale_start_date' => $variantData['sale_start_date'] ?? null,
                        'sale_end_date' => $variantData['sale_end_date'] ?? null,
                        'stock' => $variantData['stock'] ?? 0,
                        'color_id' => $variantData['color_id'] ?? null,
                        'size_id' => $variantData['size_id'] ?? null,
                        'image' => $variantImagePath, // Lưu đường dẫn ảnh biến thể
                    ]);

                    $product->variants()->save($variant);
                }
            }

            DB::commit(); // Hoàn tất transaction nếu mọi thứ thành công
            return redirect()->route('products.index')->with('success', 'Thêm sản phẩm thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction nếu có lỗi
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra: ' . $e->getMessage());
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
        $product = Product::with('category', 'variants', 'colors', 'sizes')->findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $colors = Color::all();
        $sizes = Size::all();

        return view('admin.products.edit', compact('product', 'categories', 'colors', 'sizes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
{
    try {
        $product = Product::findOrFail($id);

        // Xử lý ảnh đại diện sản phẩm
        if ($request->hasFile('thumbnail')) {
            if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $product->thumbnail = $request->file('thumbnail')->store('products', 'public');
        } else {
            $product->thumbnail = $request->input('thumbnail_old', $product->thumbnail);
        }

        // Cập nhật thông tin sản phẩm
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'short_description' => $request->short_description,
            'slug' => $request->slug,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'thumbnail' => $product->thumbnail,
            'price_products' => $request->price_products,
        ]);

        // Sync màu và size
        $product->colors()->sync($request->color_id ?? []);
        $product->sizes()->sync($request->size_id ?? []);

        // Xử lý biến thể (update hoặc create)
        $existingVariantIds = [];
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $index => $variantData) {
                $variantId = $variantData['id'] ?? null;

                if ($variantId) {
                    // Cập nhật biến thể cũ
                    $variant = ProductVariant::find($variantId);
                    if ($variant && $variant->product_id === $product->id) {
                        $variant->sku = $variantData['sku'] ?? null;
                        $variant->price = $variantData['price'] ?? null;
                        $variant->sale_price = $variantData['sale_price'] ?? null;
                        $variant->sale_start_date = $variantData['sale_start_date'] ?? null;
                        $variant->sale_end_date = $variantData['sale_end_date'] ?? null;
                        $variant->stock = $variantData['stock'] ?? 0;
                        $variant->color_id = $variantData['color_id'] ?? null;
                        $variant->size_id = $variantData['size_id'] ?? null;

                        // Xử lý ảnh biến thể
                        if ($request->hasFile("variants.{$index}.image")) {
                            if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                                Storage::disk('public')->delete($variant->image);
                            }
                            $variant->image = $request->file("variants.{$index}.image")->store('variants', 'public');
                        } else {
                            $variant->image = $variantData['image_old'] ?? $variant->image ?? null;
                        }

                        $variant->save();
                        $existingVariantIds[] = $variant->id;
                    }
                } else {
                    // Tạo mới biến thể
                    $newVariant = new ProductVariant([
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
                        $newVariant->image = $request->file("variants.{$index}.image")->store('variants', 'public');
                    } else {
                        $newVariant->image = $variantData['image_old'] ?? null;
                    }

                    $product->variants()->save($newVariant);
                    $existingVariantIds[] = $newVariant->id;
                }
            }
        }

        // Xóa biến thể không còn trong form
        $product->variants()->whereNotIn('id', $existingVariantIds)->each(function ($variant) {
            if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                Storage::disk('public')->delete($variant->image);
            }
            $variant->delete();
        });

        return redirect()->route('products.index')->with('success', 'Cập nhật sản phẩm thành công!');
    } catch (\Exception $e) {
        return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Xóa ảnh đại diện nếu có
            if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
                Storage::disk('public')->delete($product->thumbnail);
            }

            // Xóa tất cả biến thể và ảnh của chúng
            foreach ($product->variants as $variant) {
                if ($variant->image && Storage::disk('public')->exists($variant->image)) {
                    Storage::disk('public')->delete($variant->image);
                }
                $variant->delete();
            }

            // Xóa sản phẩm
            $product->delete();

            return redirect()->route('products.index')->with('success', 'Xóa sản phẩm thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
}