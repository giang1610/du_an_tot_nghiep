<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Requests\CustomEmailVerificationRequest; 
use Illuminate\Http\Request;
use App\Models\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('categories', CategoryController::class);
Route::get('trash', [CategoryController::class, 'trash'])->name('categories.trash');
Route::post('restore/{id}', [CategoryController::class, 'restore'])->name('categories.restore');
Route::delete('force-delete/{id}', [CategoryController::class, 'forceDelete'])->name('categories.forceDelete');
Route::post('categories/restore-all', [CategoryController::class, 'restoreAll'])->name('categories.restoreAll');
Route::delete('force-delete-all', [CategoryController::class, 'deleteAll'])->name('categories.deleteAll');

Route::resource('products', ProductController::class);



Route::get('/email/verify/{id}/{hash}', function (CustomEmailVerificationRequest $request) { 
    
    $request->fulfill(); // Gọi fulfill từ CustomEmailVerificationRequest

    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/email-verified-successfully?user_id=' . $request->route('id'));

})->middleware(['signed'])->name('verification.verify');






