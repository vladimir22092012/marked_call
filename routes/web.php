<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarkedCallController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GptController;
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

Route::group(['middleware' => ['auth:sanctum', 'permissions']], function () {
    Route::get('/home', [MarkedCallController::class, 'form'])->name('marked_call.home');
    Route::get('/', [MarkedCallController::class, 'form'])->name('marked_call.form');
    Route::get('/gpt/settings', [GptController::class, 'settings'])->name('gpt.settings');
    Route::post('/start', [MarkedCallController::class, 'startMarked'])->name('start');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/create', [AdminController::class, 'form'])->name('users.form');
    Route::post('/save', [AdminController::class, 'save'])->name('users.save');
    Route::get('/delete/{user}', [AdminController::class, 'delete'])->name('users.delete');
});

