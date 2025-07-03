<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;

// Email xác minh cho client
use App\Http\Controllers\Auth\EmailVerifiFotnController;
use App\Http\Controllers\Auth\NewEmailVerificationController;

Route::get('/', function () {
    return view('welcome');
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

// Đăng xuất
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Xác minh email
Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

// Xác minh mail client (fotn)
Route::get('/verify-email-custom', [EmailVerifiFotnController::class, 'verify'])
    ->middleware(['signed'])->name('verification.verify.fotn');

// Xác minh mail mới client
Route::get('/verify-new-email', [NewEmailVerificationController::class, 'verify'])
    ->name('email.update.verify')->middleware('signed');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'is_admin', 'verified'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin');

    // 👉 Khách hàng
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    // 👉 Doanh thu (tạm thời, chưa có controller)
    Route::get('/revenue', fn() => view('admin.revenue.index'))
     ->name('revenue.index');


    // Cập nhật profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Danh mục
    Route::resource('categories', CategoryController::class);
    Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
    Route::delete('/categories/delete-all', [CategoryController::class, 'deleteAll'])->name('categories.deleteAll');
    Route::post('/categories/restore-all', [CategoryController::class, 'restoreAll'])->name('categories.restoreAll');
    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');

    // Sản phẩm
    Route::resource('products', ProductController::class);
    Route::get('/products/trash', [ProductController::class, 'trash'])->name('products.trash');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::post('/products/restore-all', [ProductController::class, 'restoreAll'])->name('products.restoreAll');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.forceDelete');
    Route::delete('/products/delete-all', [ProductController::class, 'deleteAll'])->name('products.deleteAll');

    // Đơn hàng
    Route::resource('orders', OrderController::class);
    Route::get('/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled');
    Route::get('/pending', [OrderController::class, 'pending'])->name('orders.pending');
    Route::get('/processing', [OrderController::class, 'processing'])->name('orders.processing');
    Route::get('/picking', [OrderController::class, 'picking'])->name('orders.picking');
    Route::get('/shipping', [OrderController::class, 'shipping'])->name('orders.shipping');
    Route::get('/shipped', [OrderController::class, 'shipped'])->name('orders.shipped');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
});

// Trang cảm ơn
Route::get('/thank-you', function () {
    return view('thank-you');
});

// Auth scaffolding
require __DIR__.'/auth.php';
