<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\CartController;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Trang chủ
Route::get('/', function () {
    return view('admin.layouts.app');
});

// Đăng ký, đăng nhập, quên mật khẩu
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
// Email Verification
Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
    $request->fulfill();
    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/email-verified-successfully?user_id=' . $request->route('id'));
})->middleware(['signed'])->name('custom.verification.verify');


// Người dùng đã xác thực
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'is_admin'])->group(function () {
    Route::get('/', function () {
        return 'Chào admin!';
    })->name('admin.dashboard');

    Route::resource('categories', CategoryController::class); // Đảm bảo route categories.index tồn tại
    Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
    Route::resource('products', ProductController::class);

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    // Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/admin/orders/{id}/status', [App\Http\Controllers\Admin\OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});



require __DIR__.'/auth.php';
