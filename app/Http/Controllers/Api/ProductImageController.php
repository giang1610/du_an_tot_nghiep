<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // Upload ảnh sản phẩm
    public function upload(Request $request, $productId)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $product = Product::findOrFail($productId);

        $path = $request->file('image')->store('product_images', 'public');

        $image = ProductImage::create([
            'product_id' => $product->id,
            'url' => '/storage/' . $path,
            'is_default' => 0,
        ]);

        return response()->json(['data' => $image]);
    }

    // Xoá ảnh sản phẩm
    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);

        // Xoá file khỏi storage (nếu muốn)
        $filePath = str_replace('/storage/', '', $image->url);
        Storage::disk('public')->delete($filePath);

        $image->delete();

        return response()->json(['message' => 'Ảnh đã được xoá']);
    }

    // Đặt ảnh làm mặc định
    public function setDefault($id)
    {
        $image = ProductImage::findOrFail($id);

        // Xoá mặc định của các ảnh khác cùng sản phẩm
        ProductImage::where('product_id', $image->product_id)->update(['is_default' => 0]);

        $image->is_default = 1;
        $image->save();

        return response()->json(['message' => 'Đặt ảnh làm mặc định thành công']);
    }
}
