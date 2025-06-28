<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

# ==== AUTH ====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

# ==== EMAIL VERIFICATION ====
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json(['message' => 'Xác minh email thành công.']);
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Email xác minh đã được gửi lại.']);
    });
});

# ==== PUBLIC ROUTES ====
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);

# ==== COMMENT (YÊU CẦU LOGIN) ====
Route::middleware('auth:sanctum')->post('/products/{id}/comments', [ProductController::class, 'storeComment']);

# ==== CART (YÊU CẦU LOGIN) ====
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);
});

# ==== ORDER (YÊU CẦU LOGIN + ĐÃ XÁC MINH EMAIL) ====
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Lấy user đang đăng nhập
    Route::get('/user/profile', function (Request $request) {
        return $request->user();
    });

    // Đơn hàng
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
