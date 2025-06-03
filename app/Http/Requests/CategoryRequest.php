<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/|unique:categories,slug',
        'status' => 'required|boolean',
    ];
}

public function messages()
{
    return [
        'name.required' => 'Không được để trống tên danh mục.',
        'name.string' => 'Tên danh mục phải là chuỗi ký tự.',
        'name.max' => 'Tên danh mục tối đa 255 ký tự.',

        'slug.required' => 'Đường dẫn danh mục không được để trống.',
        'slug.string' => 'Đường dẫn danh mục phải là chuỗi ký tự.',
        'slug.max' => 'Đường dẫn danh mục tối đa 255 ký tự.',
        'slug.regex' => 'Đường dẫn danh mục chỉ chứa chữ thường, số và dấu gạch ngang.',
        'slug.unique' => 'Đường dẫn danh mục đã tồn tại, vui lòng chọn tên khác.',

        'status.required' => 'Trạng thái không được để trống.',
        'status.boolean' => 'Trạng thái phải là giá trị hoạt động hoặc tạm dừng.',
    ];
}

}
