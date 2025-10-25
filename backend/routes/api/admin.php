<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::prefix('admin')->group(function () {

    // -----------------------
    // 🔹 認証前に使えるAPI
    // -----------------------
    Route::post('/register', [AuthController::class, 'register']);   // 新規登録
    Route::post('/login', [AuthController::class, 'login']);         // ログイン（メール＋パスワード、必要ならTOTPも）

    // -----------------------
    // 🔒 認証後に使えるAPI
    // -----------------------
    Route::middleware('auth:admin_api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);            // ログイン中ユーザー情報
        Route::post('/logout', [AuthController::class, 'logout']);   // ログアウト
        Route::post('/2fa/enable', [AuthController::class, 'enable2FA']); // 2FA有効化
    });
});
