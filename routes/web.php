<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Requests\CustomEmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;

//use của fotn
use App\Http\Controllers\Auth\EmailVerifiFotnController;
use App\Http\Controllers\Auth\NewEmailVerificationController;

use App\Http\Controllers\AdminChatController;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/





// Trang chủ
Route::get('/', function () {
    return view('welcome');
});

// Đăng ký, đăng nhập, quên mật khẩu
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Logout 123
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
// Email Verification
Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

//xác minh mail client
Route::get('/verify-email-custom', [EmailVerifiFotnController::class, 'verify'])
    ->middleware(middleware: ['signed'])
    ->name('verification.verify.fotn');

//cập nhật mail client nếu có nhu cầu và xác minh
Route::get('/verify-new-email', [NewEmailVerificationController::class, 'verify'])
    ->name('email.update.verify')
    ->middleware('signed');




// Admin routes
//xóa danh mục
//thùng rác
Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
// //xóa vĩnh viễn
// Route::delete('/categories/delete-all', [CategoryController::class, 'deleteAll'])->name('categories.deleteAll');
//khôi phục tất cả
Route::post('/categories/restore-all', [CategoryController::class, 'restoreAll'])->name('categories.restoreAll');

//khôi phục từng danh mục
Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
// //xóa vĩnh viễn từng danh mục
// Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');

//xóa sản phẩm
//thùng rác
Route::get('/products/trash', [ProductController::class, 'trash'])->name('products.trash');
//khôi phục từng sản phẩm
Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
//khôi phục tất cả sản phẩm
Route::post('/products/restore-all', [ProductController::class, 'restoreAll'])->name('products.restoreAll');
//xóa vĩnh viễn từng sản phẩm
// Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.forceDelete');
// //xóa vĩnh viễn tất cả sản phẩm
// Route::delete('/products/delete-all', [ProductController::class, 'deleteAll'])->name('products.deleteAll');
 
Route::prefix('admin')->middleware(['auth', 'is_admin', 'verified'])->group(function () {
    Route::get('/', function () {
        return Auth::user()->role == 1
            ? redirect()->route('admin.dashboard')
            : redirect()->route('orders.index');
    })->name('admin.dashboard');
    // nhân viên
    Route::get('users/staff', [UserController::class, 'staff'])->name('users.staff');
    Route::get('users/createStaff', [UserController::class, 'createStaff'])->name('users.createStaff');
    Route::post('users/createStaff', [UserController::class, 'storeStaff'])->name('users.storeStaff');
    //cập nhật profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('categories', CategoryController::class); // Đảm bảo route categories.index tồn tại
    Route::resource('products', ProductController::class);
    Route::resource('vouchers', VoucherController::class);
    Route::resource('reviews', ReviewController::class);
    Route::resource('users', UserController::class);
   

    Route::resource('orders', OrderController::class);
    // Route::resource('pending', OrderController::class);
    Route::get('/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled');
    Route::get('/pending', [OrderController::class, 'pending'])->name('orders.pending');
    Route::get('/processing', [OrderController::class, 'processing'])->name('orders.processing');
    Route::get('/picking', [OrderController::class, 'picking'])->name('orders.picking');
    Route::get('/shipping', [OrderController::class, 'shipping'])->name('orders.shipping');
    Route::get('/shipped', [OrderController::class, 'shipped'])->name('orders.shipped');
    Route::get('/completed', [OrderController::class, 'completed'])->name('orders.completed');
    Route::get('/returned', [OrderController::class, 'returned'])->name('orders.returned');
    Route::get('/failed', [OrderController::class, 'failed'])->name('orders.failed');
    Route::get('/returning', [OrderController::class, 'returning'])->name('orders.returning');
    Route::get('/return_requested', [OrderController::class, 'return_requested'])->name('orders.return_requested');
    Route::get('/returned', [OrderController::class, 'returned'])->name('orders.returned');
    Route::put('/admin/orders/{order}/change-phone-address', [OrderController::class, 'changePhoneAddress'])->name('orders.change_phone_address');


    // Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
    // Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Admin xử lý yêu cầu hoàn đơn
    Route::post('/admin/orders/{id}/handle-return', [OrderController::class, 'handleReturn'])
        ->middleware(['auth:sanctum', 'is_admin']);

    Route::post('/orders/{id}/handle-return', [OrderController::class, 'handleReturn'])->name('orders.handleReturn');

    // Admin chat routes
    //     Route::get('/chat', [AdminChatController::class, 'listUsers'])->name('admin.chat.list');
    //     Route::get('/chat/{userId}', [AdminChatController::class, 'index'])->name('admin.chat');
    // Route::post('/chat/send/{userId}', [AdminChatController::class, 'send'])->name('admin.chat.send');
    Route::get('/chat/{userId?}', [AdminChatController::class, 'index'])->name('admin.chat');
    Route::post('/chat/send/{userId}', [AdminChatController::class, 'send'])->name('admin.chat.send');


    Route::get('revenue', [ReportController::class, 'revenueReport'])->name('admin.reports.revenue');
    // Route::get('/revenue', [ReportController::class, 'advancedReport'])->name('admin.reports.revenue');
     Route::get('reports/revenue/export', [ReportController::class, 'exportRevenueReport'])
         ->name('reports.revenue.export');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/dashboard/export-excel', [DashboardController::class, 'exportExcelDashboard'])->name('dashboard.exportExcel');

});
// NÊN ĐẶT NGOÀI group `admin`





Route::get('/thank-you', function () {
    return view('thank-you');
});

require __DIR__ . '/auth.php';
