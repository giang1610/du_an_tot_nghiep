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

        // Cố gắng lấy người dùng từ ID này
        // Đây là một cách đơn giản để gỡ lỗi, bạn có thể dùng User::find($userId)
        // hoặc Auth::getProvider()->retrieveById($userId)
        $user = User::find($userId); // Hoặc Auth::getProvider()->retrieveById($userId);

        // Dòng dd() để gỡ lỗi. Hãy kiểm tra output của dòng này.
        // Nếu $user ở đây là null, đó là vấn đề.
        // dd('Bên trong CustomEmailVerificationRequest authorize()', [
        //     'userId_from_route' => $userId,
        //     'user_object_found' => $user,
        //     'route_id_matches_user_key' => ($user && hash_equals((string) $userId, (string) $user->getKey())),
        //     'hash_matches_email_verification_hash' => ($user && hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification()))),
        // ]);

        if (! $user) {
            // Nếu không tìm thấy user, không cho phép (hoặc bạn có thể log lỗi)
            return false;
        }

        // Logic authorize gốc từ Illuminate\Foundation\Auth\EmailVerificationRequest
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
