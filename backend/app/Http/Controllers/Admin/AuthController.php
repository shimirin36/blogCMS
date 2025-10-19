<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'メールアドレスまたはパスワードが違います。'], 401);
        }

        // 2段階認証チェック
        if (!empty($admin->google2fa_secret)) {
            $google2fa = app(Authenticator::class)->boot($request);
            if (!$google2fa->verifyGoogle2FA($admin->google2fa_secret, $request->otp)) {
                return response()->json(['message' => 'ワンタイムコードが無効です。'], 401);
            }
        }

        $token = JWTAuth::fromUser($admin);

        return response()->json([
            'token' => $token,
            'admin' => $admin,
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
