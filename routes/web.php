<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {

Route::get('/chat', [ChatController::class, 'index']);

Route::get('/chat/{id}', [ChatController::class, 'show']);

Route::post('/chat/send', [ChatController::class, 'sendMessage']);

Route::post('/profile/avatar', [ProfileController::class, 'upload']);

});