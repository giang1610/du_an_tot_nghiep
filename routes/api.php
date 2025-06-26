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
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\SizeController;
use App\Http\Requests\CustomEmailVerificationRequest;
use App\Http\Controllers\Api\MomoPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Đây là nơi đăng ký tất cả route API cho ứng dụng của bạn.
| Nhóm các routes theo chức năng để dễ quản lý và tránh trùng lặp.
|
*/

// Route kiểm tra đăng nhập và lấy thông tin user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Nhóm các route yêu cầu đăng nhập (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Thông tin người dùng
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Giỏ hàng
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);


    // Thanh toán
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);

    // Momo payment
    Route::post('/payment/momo', [OrderController::class, 'payViaMomo']);
    Route::post('/payment/momo-notify', [OrderController::class, 'momoNotify']);
    Route::get('/payment/momo-return', [OrderController::class, 'momoReturn']);
});

// Routes liên quan đến xác thực và dự liệu người dùng từ hệ thống
// Đăng xuất
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Đăng ký, đăng nhập
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// xác minh mail -- tuấn anh đẹp
Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {

    $request->fulfill();
    return redirect('https://online-shop-sigma-eight.vercel.app/login?verified=true');


})->middleware(['signed'])->name('verification.verify.fotn');

// Reset password
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// Routes liên quan đến đánh giá sản phẩm
Route::middleware('auth:sanctum')->post('/reviews', [ReviewController::class, 'store']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'listByProduct']);


// Các route công khai (bất cứ ai cũng truy cập được)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/id/{id}', [ProductController::class, 'showById']);
// Route::get('/products/id/{id}', [ProductController::class, 'show']);
// Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']); // Tạo sản phẩm (cần phân quyền admi n)
// Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
// Route::get('/products/{slug}', [ProductController::class, 'showBySlug']); // Trùng này có thể tách ra


// Các chức năng liên quan đến sản phẩm
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);

// Sizes
Route::get('/sizes', [SizeController::class, 'index']);

// routes/api.php
Route::middleware('auth:sanctum')->get('/orders', [OrderController::class, 'index']);

// Route search hoặc các chức năng mở rộng có thể thêm tùy ý