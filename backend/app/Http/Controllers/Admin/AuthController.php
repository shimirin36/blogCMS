<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('admin_api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth('admin_api')->user();

        // 2FA未設定ならログイン不可
        if (empty($user->google2fa_secret)) {
            // 現在ログイン中の管理者トークンを無効化
            auth('admin_api')->logout();
            return response()->json(['error' => '2FA not enabled. Please enable it first.'], 403);
        }

        // 2FAコード必須
        if (! $request->filled('code')) {
            auth('admin_api')->logout();
            return response()->json(['error' => '2FA code required'], 403);
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        if (! $google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
            auth('admin_api')->logout();
            return response()->json(['error' => 'Invalid 2FA code'], 401);
        }

        return response()->json([
            'token' => $token,
            'admin' => $user,
        ]);
    }

    public function enable2FA(Request $request)
    {
        $user = auth('admin_api')->user();

        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        $secret = $google2fa->generateSecretKey();

        $user->google2fa_secret = $secret;
        $user->save();

        $qrUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'message' => '2FA enabled successfully',
            'secret' => $secret,
            'qr_url' => $qrUrl,
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'ログアウトしました']);
    }

    public function me()
    {
        return response()->json(JWTAuth::user());
    }
}
