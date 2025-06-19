<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\SizeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Trang chủ
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
//Hiển thị ảnh 
Route::post('/products', [ProductController::class, 'store']);
//Chi tiết
Route::get('/products/slug/{slug}', [ProductController::class, 'getBySlug']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
//Tài khoản
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
//Reset pass
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
//Hiển thị comment

// Route::middleware('auth:sanctum')->post('/comments', [CommentController::class, 'store']);

Route::middleware('auth:sanctum')->post('/products/{id}/comments', [ProductController::class, 'storeComment']);
//Sản phẩm liên quan
Route::get('/products/{id}/comments', [CommentController::class, 'getByProduct']);

Route::get('/products/related/{category_id}', [ProductController::class, 'related']);

Route::get('/categories', [CategoryController::class, 'index']);
//Lọc theo size
Route::get('/sizes', [SizeController::class, 'index'])
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::delete('/cart/remove/{item_id}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{item_id}', [CartController::class, 'updateQuantity']);
    Route::get('/cart/total', [CartController::class, 'getCartTotal']);
});

