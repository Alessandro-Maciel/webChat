<?php

use App\Http\Controllers\Api\MesssageController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user/me', [UserController::class, 'me'])->name('user.me');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/user/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/messages/{user}', [MesssageController::class, 'listMessages'])->name('message.listMessages');
    Route::post('/messages/store', [MesssageController::class, 'store'])->name('message.store');
});
