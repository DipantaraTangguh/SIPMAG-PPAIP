<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $identifier = trim($validated['login']);
        $user = $this->findUser($identifier);

        if (! $user) {
            Hash::check(
                $validated['password'],
                config('auth.login_security.dummy_password_hash')
            );
            $this->logLoginEvent('warning', 'auth.login.failed', $request, $identifier);

            $this->throwInvalidCredentials();
        }

        $result = DB::transaction(function () use ($user, $validated): array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->locked_until?->isFuture()) {
                return [
                    'status' => 'locked',
                    'user' => $lockedUser,
                    'retry_after' => max(1, now()->diffInSeconds($lockedUser->locked_until)),
                ];
            }

            if (! Hash::check($validated['password'], $lockedUser->password)) {
                return $this->recordFailedAttempt($lockedUser);
            }

            $lockedUser->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_failed_login_at' => null,
                'last_login_at' => now(),
            ])->save();

            return [
                'status' => 'authenticated',
                'user' => $lockedUser,
            ];
        });

        if ($result['status'] === 'locked') {
            $this->logLoginEvent(
                'warning',
                'auth.login.locked',
                $request,
                $identifier,
                $result['user'],
                ['retry_after' => $result['retry_after']]
            );

            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.',
                'retry_after' => $result['retry_after'],
            ], 429)->header('Retry-After', (string) $result['retry_after']);
        }

        if ($result['status'] === 'invalid') {
            $this->logLoginEvent(
                'warning',
                'auth.login.failed',
                $request,
                $identifier,
                $result['user'],
                ['failed_attempts' => $result['failed_attempts']]
            );

            $this->throwInvalidCredentials();
        }

        /** @var User $user */
        $user = $result['user'];

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $this->logLoginEvent('info', 'auth.login.succeeded', $request, $identifier, $user);

        return response()->json([
            'user' => UserResource::make($user->loadMissing(['student.dpm', 'lecturer']))->resolve($request),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => UserResource::make($request->user()->loadMissing(['student.dpm', 'lecturer']))->resolve($request),
        ]);
    }

    private function findUser(string $identifier): ?User
    {
        $user = User::query()->where('email', $identifier)->first();

        if ($user) {
            return $user;
        }

        return Student::query()
            ->where('nim', $identifier)
            ->first()
            ?->user;
    }

    private function recordFailedAttempt(User $user): array
    {
        $failedAttempts = $user->failed_login_attempts + 1;
        $lockThreshold = config('auth.login_security.lock_after_attempts');
        $lockedUntil = null;

        if ($failedAttempts >= $lockThreshold) {
            $backoffStep = min($failedAttempts - $lockThreshold, 4);
            $lockSeconds = min(
                config('auth.login_security.initial_lock_seconds') * (2 ** $backoffStep),
                config('auth.login_security.maximum_lock_seconds')
            );
            $lockedUntil = now()->addSeconds($lockSeconds);
        }

        $user->forceFill([
            'failed_login_attempts' => $failedAttempts,
            'locked_until' => $lockedUntil,
            'last_failed_login_at' => now(),
        ])->save();

        return [
            'status' => $lockedUntil ? 'locked' : 'invalid',
            'user' => $user,
            'failed_attempts' => $failedAttempts,
            'retry_after' => $lockedUntil ? now()->diffInSeconds($lockedUntil) : null,
        ];
    }

    private function throwInvalidCredentials(): never
    {
        throw ValidationException::withMessages([
            'login' => ['Kredensial yang diberikan tidak valid.'],
        ]);
    }

    private function logLoginEvent(
        string $level,
        string $event,
        Request $request,
        string $identifier,
        ?User $user = null,
        array $context = []
    ): void {
        Log::channel('security')->log($level, $event, [
            'user_id' => $user?->id,
            'identifier_hash' => hash_hmac(
                'sha256',
                Str::lower($identifier),
                (string) config('app.key')
            ),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            ...$context,
        ]);
    }
}
