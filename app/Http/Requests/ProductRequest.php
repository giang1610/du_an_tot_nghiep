<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

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
        // 'description' => 'nullable|string|max:255',
        // 'short_description' => 'nullable|string|max:255',
        // 'slug' => 'required|string|max:255|unique:products,slug,',

        // 'thumbnail' => $this->isMethod('post') ? 'required|image|mimes:jpg,jpeg,png,webp|max:2048' : 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        // 'category_id' => 'required|exists:categories,id',
        // 'status' => 'required|in:0,1,2',
        // 'variants.*.sku' => 'nullable|string|max:100',
        // 'variants.*.price' => 'nullable|numeric|min:0',
        // 'variants.*.sale_price' => 'nullable|numeric|min:0|lt:variants.*.price',
        // 'variants.*.sale_start_date' => 'required_with:variants.*.sale_price|date',
        // 'variants.*.sale_end_date' => 'required_with:variants.*.sale_price|date|after:variants.*.sale_start_date',
        // 'variants.*.stock' => 'nullable|integer|min:0',
        // 'variants.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return [
        'name.required' => 'Tên sản phẩm không được để trống.',
        'description.max' => 'Mô tả không được vượt quá 255 ký tự.',
        'short_description.max' => 'Mô tả ngắn không được vượt quá 255 ký tự.',
        'slug.required' => 'Đường dẫn không được để trống.',
        'slug.unique' => 'Đường dẫn đã tồn tại.',
        'thumbnail.required' => 'Ảnh sản phẩm không được để trống.',
        'thumbnail.image' => 'Ảnh không hợp lệ.',
        'category_id.required' => 'Vui lòng chọn danh mục.',
        'variants.*.price.min' => 'Giá phải lớn hơn hoặc bằng 0.',
        'variants.*.sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
        'variants.*.sale_start_date.required_with' => 'Phải nhập ngày bắt đầu khuyến mãi.',
        'variants.*.sale_end_date.required_with' => 'Phải nhập ngày kết thúc khuyến mãi.',
        'variants.*.sale_end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }
}

