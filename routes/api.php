<?php

use App\Http\Controllers\AuthController;
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

Route::get('401',  [AuthController::class, 'unauthorized'])->name('login');

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/user', [AuthController::class, 'create']);

// Route::put('/user', [UserController::class, 'update']);
// Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
// Route::post('/user/cover', [AuthController::class, 'updateCover']);

// Route::get('/feed', [FeedController::class, 'read']);
// Route::get('/user/feed', [FeedController::class, 'userFeed']);
// Route::get('/user/{id}/feed', [FeedController::class, 'userFeed']);

// Route::get('/user', [UserController::class, 'read']);
// Route::get('/user/{id}', [UserController::class, 'read']);

// Route::post('/feed', [FeedController::class, 'create']);

// Route::post('/post/{id}/like', [PostController::class, 'like']);
// Route::post('/post/{id}/comment', [PostController::class, 'comment']);

// Route::get('/search', [SearchController::class, 'search']);
