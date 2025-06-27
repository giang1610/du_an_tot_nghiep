<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import các Controller


use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\SizeController;
use App\Http\Requests\CustomEmailVerificationRequest;
use App\Http\Controllers\Api\MomoPaymentController;

// Routes yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    
    // Giỏ hàng
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);


    // Đơn hàng
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    

    // Đánh giá sản phẩm
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Routes xác thực & khôi phục tài khoản
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// Xác minh email
Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('https://online-shop-sigma-eight.vercel.app/login?verified=true');
})->middleware(['signed'])->name('verification.verify.fotn');

// Các route công khai
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/id/{id}', [ProductController::class, 'showById']);
Route::get('/products/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

// Tạo sản phẩm (cần phân quyền riêng)
Route::post('/products', [ProductController::class, 'store']); // TODO: Thêm middleware 'admin'

// Categories & Sizes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);

// Lấy đánh giá sản phẩm
Route::get('/products/{id}/reviews', [ReviewController::class, 'listByProduct']);
