<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarkedCallController;
use App\Http\Controllers\GptController;

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

Route::get('/solaris/getNewRequests', [\App\Http\Controllers\MarkedCallController::class, 'solaris'])->name('api.solaris.getNewRequests');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('integrationPassword')->group(function () {
    Route::post('/marked_call/start',
        [\App\Http\Controllers\MarkedCallController::class, 'startMarked']
    )->name('api.marked_call.start');
});

Route::post('/gpt/save', [GptController::class, 'save'])->name('api.gpt.save');
