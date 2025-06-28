<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Auth\EmailVerificationRequest as LaravelEmailVerificationRequest;
use Illuminate\Support\Facades\Auth; // Thêm dòng này
use App\Models\User; // Thêm dòng này, đảm bảo namespace User model của bạn là đúng

class CustomEmailVerificationRequest extends LaravelEmailVerificationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Lấy ID người dùng từ route
        $userId = $this->route('id');

        $user = User::find($userId); // Hoặc Auth::getProvider()->retrieveById($userId);


        if (! $user) {
       
            return false;
        }

       
        if (! hash_equals((string) $this->route('id'), (string) $user->getKey())) {
            return false;
        }

        if (! hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Bạn có thể giữ nguyên hoặc tùy chỉnh nếu cần
        return parent::rules();
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill(): void
    {
        // Đảm bảo người dùng được load đúng cách trước khi gọi markEmailAsVerified
        $user = User::find($this->route('id')); // Hoặc $this->user() nếu authorize đã đảm bảo nó không null
        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            // event(new Verified($user)); // Dòng này đã được xử lý trong markEmailAsVerified
        }
    }
}
