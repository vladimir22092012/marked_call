<?php

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
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\AuthController::class, 'form'])->name('login');
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'auth'])->name('auth');
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/home', [\App\Http\Controllers\MarkedCallController::class, 'form'])->name('marked_call.home');
    Route::get('/', [\App\Http\Controllers\MarkedCallController::class, 'form'])->name('marked_call.form');
    Route::post('/start', [\App\Http\Controllers\MarkedCallController::class, 'startMarked'])->name('start');
    Route::get('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
});

Route::group(['middleware' => ['auth:sanctum', 'admin']], function () {
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/createUser', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('createUser');
});
