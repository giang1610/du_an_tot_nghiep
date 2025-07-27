<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $voucherId = $this->route('voucher');
        return [

            'name' => [
            'required',
            'string',
            'max:255',
            Rule::unique('vouchers', 'name')->ignore($voucherId),
            ],
            'code' => [
                'required',
                'string',
                Rule::unique('vouchers', 'code')->ignore($voucherId),
            ],
            'type' => 'required|in:shipping,product',
            'discount_type' => 'required|in:amount,percent',
            'discount_amount' => [
                'nullable',
                'required_if:discount_type,amount',
                'numeric',
                'min:1',
                'prohibited_if:discount_type,percent',
            ],
            'discount_percent' => [
                'nullable',
                'required_if:discount_type,percent',
                'numeric',
                'min:1',
                'max:100',
                'prohibited_if:discount_type,amount',
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'quantity' => 'required|integer|min:1',
            'usage_limit' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
        'name.required' => 'Tên voucher không được để trống.',
        'name.string' => 'Tên voucher phải là chuỗi ký tự.',
        'name.max' => 'Tên voucher tối đa 255 ký tự.',
        'name.unique' => 'Tên voucher đã tồn tại, vui lòng chọn tên khác.',

        'code.required' => 'Mã voucher không được để trống.',
        'code.string' => 'Mã voucher phải là chuỗi ký tự.',
        'code.unique' => 'Mã voucher đã tồn tại, vui lòng chọn mã khác.',

        'type.required' => 'Loại voucher không được để trống.',
        'type.in' => 'Loại voucher phải là "shipping" hoặc "product".',

        'discount_type.required' => 'Bạn phải chọn loại giảm giá.',
        'discount_type.in' => 'Loại giảm giá không hợp lệ.',

        'discount_amount.required_if' => 'Vui lòng nhập số tiền giảm.',
        'discount_amount.numeric' => 'Số tiền giảm phải là số.',
        'discount_amount.min' => 'Số tiền giảm phải lớn hơn 0.',

        'discount_percent.required_if' => 'Vui lòng nhập phần trăm giảm.',
        'discount_percent.numeric' => 'Phần trăm giảm phải là số.',
        'discount_percent.min' => 'Phần trăm giảm phải lớn hơn 0.',
        'discount_percent.max' => 'Phần trăm giảm không được lớn hơn 100.',

        'start_date.required' => 'Ngày bắt đầu không được để trống.',
        'start_date.date' => 'Ngày bắt đầu phải là ngày hợp lệ.',

        'end_date.required' => 'Ngày kết thúc không được để trống.',
        'end_date.date' => 'Ngày kết thúc phải là ngày hợp lệ.',
        'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',

        'quantity.required' => 'Số lượng không được để trống.',
        'quantity.integer' => 'Số lượng phải là số nguyên.',
        'quantity.min' => 'Số lượng không được nhỏ hơn 1.',

        'usage_limit.required' => 'Giới hạn sử dụng không được để trống.',
        'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',
        'usage_limit.min' => 'Giới hạn sử dụng không được nhỏ hơn 1.',

        'discount_amount.prohibited_if' => 'Không được nhập số tiền giảm khi chọn loại giảm giá theo phần trăm.',
        'discount_percent.prohibited_if' => 'Không được nhập phần trăm giảm khi chọn loại giảm giá theo tiền.',
        ];
    }
}