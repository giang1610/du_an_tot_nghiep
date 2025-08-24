<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;

class LoginRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['nullable', 'string', 'email'],
            'password' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập địa chỉ email',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.max' => 'Mật khẩu không được vượt quá :max ký tự',

        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
        $password = $this->input('password');

         
        // Kiểm tra nếu không nhập email hoặc pass
        if (empty($email)) {
            throw ValidationException::withMessages([
                'email' => 'Vui lòng nhập email ',
            ]);
        }
       
        // if (Auth::user()->role == 2) {
        //     return redirect()->route('orders.index');
        // }
        // return redirect()->route('admin.dashboard');

        if (empty($password)) {
            throw ValidationException::withMessages([
                'password' => 'Vui lòng nhập mật khẩu',
            ]);
        }
        // Kiểm tra email có tồn tại không
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Email không tồn tại
            throw ValidationException::withMessages([
                'email' => 'Email không tồn tại.',
            ]);
        }
        // Kiểm tra quyền của user
        if (!in_array($user->role, [1, 2])) {
        throw ValidationException::withMessages([
            'email' => 'Tài khoản của bạn không có quyền đăng nhập hệ thống.',
        ]);
        }

        // Nếu trạng thái == 0, kiểm tra mật khẩu
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            // Sai mật khẩu
            throw ValidationException::withMessages([
                'email' => 'Mật khẩu sai, vui lòng nhập lại',
            ]);
        }

        // Kiểm tra quyền nếu cần (ví dụ ở đây chỉ cho user có ID = 1 đăng nhập)
        // if (Auth::id() !== ) {
        //     Auth::logout(); 
        //     RateLimiter::hit($this->throttleKey()); 
        //     throw ValidationException::withMessages([
        //         'email' => 'Bạn không có quyền đăng nhập vào hệ thống',
        //     ]);
        // }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}