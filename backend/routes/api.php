<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

// 分割したAPIルートを読み込む
require __DIR__ . '/api/admin.php';
// require __DIR__ . '/api/user.php';
// require __DIR__ . '/api/public.php';

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Hello from Laravel API!',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

Route::get('/posts', function () {
    return response()->json([
        ['id' => 1, 'title' => '初めての投稿', 'content' => 'Laravel APIから取得したサンプルです。'],
        ['id' => 2, 'title' => 'Docker連携', 'content' => 'NuxtとLaravelがつながりました！'],
    ]);
});

Route::prefix('admin')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:admin_api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
