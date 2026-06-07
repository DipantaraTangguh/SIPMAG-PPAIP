<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_uses_http_only_session_cookie_without_returning_a_token(): void
    {
        $user = User::factory()->create();
        $response = $this->statefulPost('/api/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['user'])
            ->assertJsonMissingPath('token');

        $this->assertAuthenticatedAs($user);
        $this->assertStringContainsString(
            'httponly',
            strtolower(implode('; ', $response->headers->all('set-cookie')))
        );

        $this->withHeader('Origin', config('app.url'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_logout_invalidates_the_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->statefulPost('/api/login', [
            'login' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->statefulPost('/api/logout')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withHeader('Origin', config('app.url'))
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_legacy_bearer_tokens_are_not_accepted(): void
    {
        $user = User::factory()->create();
        $tokenId = DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'legacy-token',
            'token' => hash('sha256', 'legacy-plain-text-token'),
            'abilities' => '["*"]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeader('Authorization', "Bearer {$tokenId}|legacy-plain-text-token")
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    private function statefulPost(string $uri, array $data = [])
    {
        return $this->withSession(['_token' => 'test-csrf-token'])
            ->withHeaders([
                'Origin' => config('app.url'),
                'X-CSRF-TOKEN' => 'test-csrf-token',
            ])
            ->postJson($uri, $data);
    }
}
