<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_is_locked_after_five_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->postJson('/api/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $response = $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertJsonPath('message', 'Terlalu banyak percobaan login. Silakan coba lagi nanti.');

        $user->refresh();

        $this->assertSame(5, $user->failed_login_attempts);
        $this->assertTrue($user->locked_until->isFuture());

        $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertTooManyRequests();

        $this->assertSame(5, $user->fresh()->failed_login_attempts);
    }

    public function test_lockout_uses_progressive_backoff_and_resets_after_success(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [
                'login' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->travel(61)->seconds();

        $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])->assertTooManyRequests();

        $user->refresh();
        $this->assertSame(6, $user->failed_login_attempts);
        $this->assertGreaterThanOrEqual(119, now()->diffInSeconds($user->locked_until));

        $this->travel(121)->seconds();

        $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user']);

        $user->refresh();
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_login_route_is_throttled_by_identifier_and_ip(): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson('/api/login', [
                'login' => 'unknown@example.com',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/login', [
            'login' => 'unknown@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests()->assertHeader('Retry-After');
    }

    public function test_failed_login_is_written_to_the_security_audit_log(): void
    {
        $user = User::factory()->create();

        Log::shouldReceive('channel')
            ->once()
            ->with('security')
            ->andReturnSelf();
        Log::shouldReceive('log')
            ->once()
            ->withArgs(function (string $level, string $event, array $context) use ($user): bool {
                return $level === 'warning'
                    && $event === 'auth.login.failed'
                    && $context['user_id'] === $user->id
                    && isset($context['identifier_hash'], $context['ip_address']);
            });

        $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }
}
