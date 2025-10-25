<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            // フロント側で2FAを有効化したい場合のみ true を送る
            'enable_2fa' => 'nullable|boolean',
        ]);

        $google2fa = new Google2FA();
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
            'name' => $request->name,
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

        $email = strtolower((string)($credentials['email'] ?? ''));
        $ip = (string) $request->ip();
        $pwdKey = 'admin_login:pwd:' . sha1($email . '|' . $ip);
        $twofaKey = 'admin_login:2fa:' . sha1($email . '|' . $ip);

        // まずメールから管理者を特定
        $user = \App\Models\Admin::where('email', $credentials['email'] ?? null)->first();

        // ユーザー未登録の明示
        if (! $user) {
            return response()->json([
                'error' => 'Account not found',
                'not_found' => true,
                'status' => 'not_found',
            ], 404);
        }

        // DBベースのロック・凍結確認（ユーザーが存在する場合のみ）
        if ($user->is_suspended) {
            return response()->json([
                'error' => 'Account suspended',
                'suspended' => true,
                'status' => 'suspended',
            ], 423);
        }
        if ($user->temporary_lock_until && now()->lt($user->temporary_lock_until)) {
            return response()->json([
                'error' => 'Temporarily locked',
                'locked' => true,
                'lockout_seconds' => now()->diffInSeconds($user->temporary_lock_until, false),
                'status' => 'locked',
            ], 429);
        }

        // 次にRateLimiter（メール+IP）によるロックを判定
        if (RateLimiter::tooManyAttempts($pwdKey, 5) || RateLimiter::tooManyAttempts($twofaKey, 5)) {
            $secondsPwd = RateLimiter::availableIn($pwdKey);
            $seconds2fa = RateLimiter::availableIn($twofaKey);
            $lockSeconds = max($secondsPwd, $seconds2fa);
            return response()->json([
                'error' => 'Too many attempts. Please try again later.',
                'locked' => true,
                'lockout_seconds' => $lockSeconds,
                'status' => 'locked',
            ], 429);
        }
        if (! Hash::check($credentials['password'] ?? '', $user->password)) {
            RateLimiter::hit($pwdKey, 300); // 5分間（IP+メール）
            // DBカウント（ユーザーが存在する時のみ）
            if ($user) {
                $user->failed_login_count = (int)$user->failed_login_count + 1;
                $user->total_failed_count = (int)$user->total_failed_count + 1;

                $total = (int)$user->total_failed_count;
                if ($total >= 10) {
                    $user->is_suspended = true;
                    $user->save();
                    return response()->json([
                        'error' => 'Account suspended',
                        'suspended' => true,
                        'status' => 'suspended',
                    ], 423);
                }

                if ($total >= 5 && $total % 5 === 0) {
                    $user->temporary_lock_until = now()->addMinutes(5);
                    $user->save();
                    return response()->json([
                        'error' => 'Temporarily locked',
                        'locked' => true,
                        'lockout_seconds' => now()->diffInSeconds($user->temporary_lock_until, false),
                        'status' => 'locked',
                    ], 429);
                }
                $user->save();
            }
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // 2FAを設定している場合のみ、コードを確認
        if (!empty($user->google2fa_secret)) {
            // コードが送信されていない場合は「2FAが必要」と返す
            if (! $request->filled('code')) {
                // パスワードは正しいのでパスワード側の失敗回数はクリア
                RateLimiter::clear($pwdKey);
                $user->failed_login_count = 0;
                $user->save();
                return response()->json([
                    'message' => 'Two-factor authentication required',
                    'two_factor_required' => true,
                    'status' => 'pending_2fa',
                    'email' => $user->email, // フロントで使いやすくする
                ], 200);
            }

            $google2fa = new Google2FA();
            if (! $google2fa->verifyKey($user->google2fa_secret, $request->input('code'))) {
                RateLimiter::hit($twofaKey, 300); // 5分間（IP+メール）
                // DBカウント
                $user->twofa_failed_count = (int)$user->twofa_failed_count + 1;
                $user->total_failed_count = (int)$user->total_failed_count + 1;

                $total = (int)$user->total_failed_count;
                if ($total >= 10) {
                    $user->is_suspended = true;
                    $user->save();
                    return response()->json([
                        'error' => 'Account suspended',
                        'suspended' => true,
                        'status' => 'suspended',
                    ], 423);
                }
                if ($total >= 5 && $total % 5 === 0) {
                    $user->temporary_lock_until = now()->addMinutes(5);
                    $user->save();
                    return response()->json([
                        'error' => 'Temporarily locked',
                        'locked' => true,
                        'lockout_seconds' => now()->diffInSeconds($user->temporary_lock_until, false),
                        'status' => 'locked',
                    ], 429);
                }

                $user->save();
                return response()->json(['error' => 'Invalid 2FA code'], 401);
            }
            // 2FA成功: カウンタをクリア
            RateLimiter::clear($twofaKey);
            $user->twofa_failed_count = 0;
            $user->save();
        }

        // ここに来ていれば完全に検証済み。失敗カウンタをクリア
        RateLimiter::clear($pwdKey);
        $user->failed_login_count = 0;
        // 成功時に一時ロックが残っていればクリア（通常は入らないが安全のため）
        if ($user->temporary_lock_until && now()->gte($user->temporary_lock_until)) {
            $user->temporary_lock_until = null;
        }
        $user->save();

        // 検証完了後にトークンを発行
        $token = JWTAuth::fromUser($user);

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

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($admin->google2fa_secret, $request->code)) {
            return response()->json(['error' => 'Invalid 2FA code'], 401);
        }

        $token = JWTAuth::fromUser($admin);

        return response()->json([
            'message' => '2FA verified successfully.',
            'token' => $token,
            'admin' => $admin
        ]);
    }

    public function enable2FA(Request $request)
    {
        /** @var \App\Models\Admin|null $user */
        $user = auth('admin_api')->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $google2fa = new Google2FA();

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
        /** @var \App\Models\Admin|null $user */
        $user = auth('admin_api')->user();
        return response()->json($user);
    }
}
