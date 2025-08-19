<?php

use App\Http\Controllers\Api\VoucherController;
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
use App\Http\Controllers\Api\Auth\ChangePasswordController;

use App\Http\Controllers\Api\Auth\GetUserController;


// route chat
use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\MomoPaymentController;
use App\Http\Controllers\Api\VnpayPaymentController;

// ========== PUBLIC ROUTES ========== //
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:5,1');


Route::middleware('auth:sanctum')->post('/email/verify-token', [TokenEmailVerificationController::class, 'verify']);
//route profile
Route::middleware('auth:sanctum')->put('/profile', [ProfileController::class, 'update']);
//route updatepass
Route::middleware('auth:sanctum')->put('/change-password', [ChangePasswordController::class, 'change']);


//route chat
Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat/send', [ChatController::class, 'store']);
Route::middleware('auth:api')->post('/chat/typing', [ChatController::class, 'typing']);





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
    // Route::get('user', [GetUserController::class, 'getUser']);

    Route::get('user', [GetUserController::class, 'getUser']);

    // Reviews
    // Gửi đánh giá
    Route::post('/reviews', [ReviewController::class, 'store']);
    // Lấy danh sách đánh giá theo product_id (dùng ?product_id=...)
    Route::get('/reviews', [ReviewController::class, 'getByProductQuery']);
    // Kiểm tra đã nhận hàng
    Route::get('/orders/received-product', [ReviewController::class, 'receivedOrders']);

    // Cart
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('cart')->group(function () {
            Route::post('/add', [CartController::class, 'addToCart']);
            Route::get('/', [CartController::class, 'viewCart']);
            Route::put('/update-selected/{item_id}', [CartController::class, 'updateSelected']);
            Route::put('/update/{item_id}', [CartController::class, 'updateQuantity']);
            Route::delete('/remove/{item_id}', [CartController::class, 'removeFromCart']);
            Route::delete('/remove-selected', [CartController::class, 'removeSelectedItems']);
            Route::delete('/clear', [CartController::class, 'clearCart']);
            Route::get('/total', [CartController::class, 'getCartTotal']);
        });
    });
});

// Orders
Route::middleware('auth:sanctum')->group(function () {

    // Orders
    Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::post('/checkout/validate-voucher', [OrderController::class, 'validateVoucher']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::put('/{order}/update-address', [OrderController::class, 'updateAddress']);
        Route::post('/{id}/confirm-received', [OrderController::class, 'confirmReceived']);
        // Gửi yêu cầu hoàn đơn
        Route::post('/{id}/request-return', [OrderController::class, 'requestReturn']);
    });

    // Payment Momo


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/payment/momo', [MomoPaymentController::class, 'processMomoPayment']);
        Route::post('/momo/retry-payment', [MomoPaymentController::class, 'retryMomoPayment']);

    });

    Route::get('/payment/vnpay/verify', [OrderController::class, 'verifyReturn']);
    // Payment VNPAY
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/vnpay/pay', [OrderController::class, 'processVnpayPayment']);
        Route::post('/vnpay/retry-payment', [OrderController::class, 'retryVnpayPayment']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::post('/vouchers/validate', [VoucherController::class, 'validateVoucher']);
    Route::get('/vouchers/my', [VoucherController::class, 'getUserVouchers']);  // Lấy danh sách voucher của người dùng
    Route::post('/vouchers/suggestions', [VoucherController::class, 'suggest']);
    Route::post('/vouchers/apply', [VoucherController::class, 'apply']);
});
// Không cho vào trong auth:sanctum
Route::post('/payment/momo/webhook', [MomoPaymentController::class, 'momoIpn']); // IPN
Route::get('/payment/momo/return', [MomoPaymentController::class, 'momoReturn']);


Route::get('/payment/vnpay/return', [OrderController::class, 'vnpayReturn']);