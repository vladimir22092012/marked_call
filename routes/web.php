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
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'auth'])->name('auth');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [\App\Http\Controllers\MarkedCallController::class, 'form'])->name('marked_call.form');
    Route::get('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
    Route::get('/start', [\App\Http\Controllers\MarkedCallController::class, 'startMarked'])->name('start');
    Route::get('/status', [\App\Http\Controllers\MarkedCallController::class, 'statusMarked'])->name('status');
});

Route::group(['middleware' => ['auth', 'admin']], function () {
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/createUser', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('createUser');
});
