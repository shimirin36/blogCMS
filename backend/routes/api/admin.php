<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::prefix('admin')->group(function () {
    // 管理者ログイン
    Route::post('login', [AuthController::class, 'login']);

    // ログイン後にのみアクセスできるルート
    Route::middleware('auth:admin_api')->group(function () {
        //　自分の情報を取得
        Route::get('me', [AuthController::class, 'me']);

        // ログアウト
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
