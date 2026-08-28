<?php

namespace App\Providers;

use App\Models\DefenseAssessment;
use App\Observers\DefenseAssessmentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        DefenseAssessment::observe(DefenseAssessmentObserver::class);

        RateLimiter::for('login', function (Request $request): array {
            $identifier = Str::lower(trim((string) $request->input('login')));
            $identifierKey = hash_hmac('sha256', $identifier, (string) config('app.key'));
            $response = function (Request $request, array $headers) use ($identifierKey) {
                Log::channel('security')->warning('auth.login.rate_limited', [
                    'identifier_hash' => $identifierKey,
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                ]);

                return response()->json([
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.',
                ], 429, $headers);
            };

            return [
                Limit::perMinute(30)
                    ->by('ip:'.$request->ip())
                    ->response($response),
                Limit::perMinute(10)
                    ->by('login:'.$identifierKey.'|'.$request->ip())
                    ->response($response),
            ];
        });
    }
}
