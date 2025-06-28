<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function show($id)
    {
        $variant = ProductVariant::with(['product', 'size', 'color'])->find($id);
        if (!$variant) {
            return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm'], 404);
        }
        return response()->json(['variant' => $variant]);
    }
}
