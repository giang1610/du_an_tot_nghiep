<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\SizeController;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Email verification
Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('https://online-shop-sigma-eight.vercel.app/login?verified=true');
})->middleware(['signed'])->name('verification.verify.fotn');

// Password Reset
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// Public product APIs
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/id/{id}', [ProductController::class, 'showById']);
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);
Route::get('/products/{id}/comments', [ProductController::class, 'comments']);


// Categories, Sizes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);

// Routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    // Cart
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Đánh giá sản phẩm – chỉ khi đã nhận hàng
    // Route::post('/products/{id}/rate', [CommentController::class, 'rate']);
});
