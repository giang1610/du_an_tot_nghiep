<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép truy cập request này
    }

    public function rules(): array
    {
        // Dựa vào route name hoặc action name để phân biệt từng hàm
        $method = $this->route()->getActionMethod();

        return match ($method) {
            'addToCart' => [
                'product_variant_id' => 'required|integer|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
                'color_id' => 'required|integer|exists:colors,id',
                'size_id' => 'required|integer|exists:sizes,id',
                'note' => 'nullable|string|max:255',
            ],
            'updateQuantity' => [
                'quantity' => 'sometimes|integer|min:1',
                'color_id' => 'required|integer|exists:colors,id',
                'size_id' => 'required|integer|exists:sizes,id',
                'selected' => 'sometimes|boolean',
                'note' => 'nullable|string|max:255',
            ],
            default => []
        };
    }
    public function messages(): array
    {
        return [
            'addToCarrt' => [            
                'product_variant_id.required' => 'Vui lòng chọn sản phẩm.',
                // 'product_variant_id.exists' => 'Sản phẩm không tồn tại.',
                'quantity.required' => 'Hãy chọn số lượng sản phẩm.',
                // 'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng tối thiểu là 1.',
                'color_id.required' => 'Vui lòng chọn màu sắc.',
                // 'color_id.exists' => 'Màu sắc không tồn tại.',
                'size_id.required' => 'Vui lòng chọn kích thước.',
                // 'size_id.exists' => 'Kích thước không tồn tại.',
                'note.max' => 'Ghi chú tối đa 255 ký tự.',
            ],
            'updateQuantity' => [
                'quantity.sometimes' => 'Hãy điền số lượng muốn sửa.',
                'quantity.integer' => 'Số lượng phải là số nguyên.',
                'quantity.min' => 'Số lượng tối thiểu là 1.',
                'color_id.required' => 'Vui lòng chọn màu sắc của sản phẩm muốn sửa.',
                // 'color_id.exists' => 'Màu sắc không tồn tại.',
                'size_id.required' => 'Vui lòng chọn kích thước của sản phẩm muốn sửa.',
                // 'size_id.exists' => 'Kích thước không tồn tại.',
                // 'selected.boolean' => 'Trạng thái chọn phải là true hoặc false.',
                'note.max' => 'Ghi chú tối đa 255 ký tự.',
            ]
        ];
    }
}
