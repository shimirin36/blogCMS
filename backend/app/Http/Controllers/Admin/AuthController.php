<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            // フロント側で2FAを有効化したい場合のみ true を送る
            'enable_2fa' => 'nullable|boolean',
        ]);

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $secret = null;
        $qrUrl = null;

        // enable_2fa=true の場合のみ、2FA秘密鍵を生成
        if ($request->boolean('enable_2fa')) {
            $secret = $google2fa->generateSecretKey();
            $qrUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $request->email,
                $secret
            );
        }

        $admin = \App\Models\Admin::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'google2fa_secret' => $secret, // nullでもOK
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'two_factor_enabled' => (bool) $secret,
            'secret' => $secret,
            'qr_url' => $qrUrl,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('admin_api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth('admin_api')->user();

        // 2FAを設定している場合のみ、コードを確認
        if (!empty($user->google2fa_secret)) {

            // コードが送信されていない場合は「2FAが必要」と返す
            if (! $request->filled('code')) {
                return response()->json([
                    'message' => 'Two-factor authentication required',
                    'two_factor_required' => true,
                    'status' => 'pending_2fa',
                    'email' => $user->email, // フロントで使いやすくする
                ], 200); // 成功ステータスで返す
            }

            $google2fa = new \PragmaRX\Google2FA\Google2FA();

            if (! $google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
                auth('admin_api')->logout();
                return response()->json(['error' => 'Invalid 2FA code'], 401);
            }
        }

        // 2FA未設定ならそのままログインOK
        return response()->json([
            'token' => $token,
            'admin' => $user,
        ]);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);

        $admin = \App\Models\Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        if (!$google2fa->verifyKey($admin->google2fa_secret, $request->code)) {
            return response()->json(['error' => 'Invalid 2FA code'], 401);
        }

        $token = auth('admin_api')->login($admin);

        return response()->json([
            'message' => '2FA verified successfully.',
            'token' => $token,
            'admin' => $admin
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
