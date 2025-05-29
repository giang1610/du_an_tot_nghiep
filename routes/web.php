<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

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
Route::group(['prefix' => 'categories'], function () {
    
    
    
    
});
