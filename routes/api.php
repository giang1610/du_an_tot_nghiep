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

use App\Http\Controllers\Api\Auth\TokenEmailVerificationController;
use App\Http\Controllers\Api\ProfileController;


// User info
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//route test realtime
use App\Http\Controllers\MessageController;

Route::post('/send-message', [MessageController::class, 'sendMessage']);



// ========== PUBLIC ROUTES ========== //
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);


Route::middleware('auth:sanctum')->post('/email/verify-token', [TokenEmailVerificationController::class, 'verify']);
//route profile
Route::middleware('auth:sanctum')->put('/profile', [ProfileController::class, 'update']);






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
Route::get('/orders/received-product', [ReviewController::class, 'receivedOrders'])->middleware('auth:sanctum');


// ========== PROTECTED ROUTES (auth:sanctum) ========== //
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    // Reviews
    // Gửi đánh giá
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Lấy danh sách đánh giá theo product_id (dùng ?product_id=...)
    Route::get('/reviews', [ReviewController::class, 'getByProductQuery']);

    // Kiểm tra đã nhận hàng
    Route::middleware('auth:sanctum')->get('/orders/received-product', [ReviewController::class, 'receivedOrders']);
    // Cart
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('cart')->group(function () {
            Route::post('/add', [CartController::class, 'addToCart']);
            Route::get('/', [CartController::class, 'viewCart']);
            Route::put('/update-selected/{item_id}', [CartController::class, 'updateSelected']);
            Route::put('/update/{item_id}', [CartController::class, 'updateQuantity']);
            Route::delete('/remove/{item_id}', [CartController::class, 'removeFromCart']);
            Route::get('/total', [CartController::class, 'getCartTotal']);
            // Route::post('/checkout', [CartController::class, 'checkout']); // Đừng quên checkout!

        });
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
        Route::post('/{order}/confirm-received', [OrderController::class, 'confirmReceived']);
        Route::post('/{order}/request-return', [OrderController::class, 'requestReturn']);
    });

    // Momo payment
    Route::prefix('payment')->group(function () {
        // Route::post('/momo', [OrderController::class, 'payViaMomo']);
        Route::post('/momo-notify', [OrderController::class, 'momoWebhook']);
        Route::get('/momo-return', [OrderController::class, 'momoReturn']);
    });
    // Các route khác: logout, cart, review...

});



