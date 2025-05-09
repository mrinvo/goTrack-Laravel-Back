<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuthenticationLog;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Log the login attempt
        $log = new AuthenticationLog([
            'user_id' => $user ? $user->id : null,
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_successful' => false,
            'login_at' => now(),
        ]);

        if (!$user || !Hash::check($request->password, $user->password)) {
            $log->save();

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is active
        if (!$user->is_active) {
            $log->save();

            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact an administrator.'],
            ]);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Update log with successful login
        $log->login_successful = true;
        $log->save();

        return $this->success([
            'user' => $user->load('roles', 'permissions'),
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Get authenticated user info
     */
    public function user(Request $request)
    {
        return $this->success($request->user()->load('roles', 'permissions'));
    }

    /**
     * Logout user (revoke the token)
     */
    public function logout(Request $request)
    {
        // Find the latest login log for this user and update logout time
        if ($request->user()) {
            $log = AuthenticationLog::where('user_id', $request->user()->id)
                ->whereNull('logout_at')
                ->orderBy('login_at', 'desc')
                ->first();

            if ($log) {
                $log->logout_at = now();
                $log->save();
            }

            $request->user()->currentAccessToken()->delete();
        }

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success(null, __($status));
        }

        return $this->error(__($status), 400);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success(null, __($status));
        }

        return $this->error(__($status), 400);
    }
}