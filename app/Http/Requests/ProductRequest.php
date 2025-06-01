<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép request
    }

    public function rules()
    {
        return [
            // 'name' => 'required|string|max:255',
            // 'description' => 'nullable|string',
            // 'short_description' => 'nullable|string',
            // 'slug' => 'required|string|max:255|unique:products,slug',
            // 'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'category_id' => 'required|exists:categories,id',
            // 'status' => 'required|in:0,1,2',

            // 'variants.*.sku' => 'nullable|string|max:100',
            // 'variants.*.price' => 'nullable|numeric|min:0',
            // 'variants.*.sale_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            // 'variants.*.sale_start_date' => 'nullable|date',
            // 'variants.*.sale_end_date' => 'nullable|date|after_or_equal:variants.*.sale_start_date',
            // 'variants.*.stock' => 'nullable|in:0,1',
            // 'variants.*.image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',

            // 'color_id' => 'nullable|array',
            // 'color_id.*' => 'exists:colors,id',
            // 'size_id' => 'nullable|array',
            // 'size_id.*' => 'exists:sizes,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên sản phẩm.',
            'slug.required' => 'Đường dẫn là bắt buộc.',
            'slug.unique' => 'Đường dẫn này đã tồn tại.',
            'thumbnail.image' => 'Ảnh sản phẩm phải là file ảnh.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục không hợp lệ.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'variants.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0.',
            'variants.*.sale_price.lte' => 'Giá khuyến mãi phải nhỏ hơn hoặc bằng giá gốc.',
            'variants.*.sale_end_date.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }
}

