<?php

// use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });



    // Các route yêu cầu người dùng phải đăng nhập VÀ đã xác thực email (verified)
    Route::middleware('verified')->group(function () {

    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
//Reset pass
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);


Route::middleware('auth:sanctum')->post('/products/{id}/comments', [CommentController::class, 'store']);
Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);

});

