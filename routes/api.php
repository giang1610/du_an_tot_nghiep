<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Requests\CustomEmailVerificationRequest;

// User info
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ========== PUBLIC ROUTES ========== //
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

// Email verification
Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('https://online-shop-sigma-eight.vercel.app/login?verified=true');
})->middleware(['signed'])->name('verification.verify');

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/id/{id}', [ProductController::class, 'showById']);
Route::get('/product-variants/{id}', [ProductVariantController::class, 'show']);
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

// Categories & Sizes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);

// Public reviews (view only)
Route::get('/products/{id}/reviews', [ReviewController::class, 'listByProduct']);

// ========== PROTECTED ROUTES (auth:sanctum) ========== //
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/orders/received-product', [ReviewController::class, 'receivedOrders']);
    // Cart
    Route::prefix('cart')->group(function () {
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::get('/', [CartController::class, 'viewCart']);
        Route::put('/update-selected/{item_id}', [CartController::class, 'updateSelected']);
        Route::put('/update/{item_id}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove/{item_id}', [CartController::class, 'removeFromCart']);
        Route::get('/total', [CartController::class, 'getCartTotal']);
        Route::post('/checkout', [CartController::class, 'checkout']); // Đừng quên checkout! ????
    });
});

// Orders
Route::middleware('auth:sanctum')->group(function () {

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/checkout', [OrderController::class, 'checkout']);
       Route::put('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::put('/{order}/update-address', [OrderController::class, 'updateAddress']);
    });

    // Các route khác: logout, cart, review...
    Route::prefix('payment')->group(function () {
        Route::post('/momo', [OrderController::class, 'processMomoPayment']);
        Route::post('/momo-notify', [OrderController::class, 'momoWebhook']);
        Route::get('/momo-return', [OrderController::class, 'momoReturn']);
    });
});

// Payment Momo