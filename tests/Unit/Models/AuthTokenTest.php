<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\AuthToken;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_generation()
    {
        $user = Usuario::factory()->create();

        $token = AuthToken::generateToken($user->id);

        $this->assertNotNull($token->token);
        $this->assertEquals(60, strlen($token->token));
        $this->assertTrue($token->isValid());
    }

    public function test_old_tokens_are_deleted_on_new_token_generation()
    {
        $user = Usuario::factory()->create();

        // Criar token antigo
        $oldToken = AuthToken::create([
            'usuario_id' => $user->id,
            'token' => 'oldtoken123',
            'expires_at' => Carbon::now()->addHours(1)
        ]);

        // Gerar novo token
        $newToken = AuthToken::generateToken($user->id);

        // Verificar que o token antigo foi removido
        $this->assertDatabaseMissing('auth_tokens', ['token' => 'oldtoken123']);
        $this->assertDatabaseHas('auth_tokens', ['token' => $newToken->token]);
    }

    public function test_token_expiration()
    {
        $user = Usuario::factory()->create();

        // Token válido
        $validToken = AuthToken::create([
            'usuario_id' => $user->id,
            'token' => 'validtoken',
            'expires_at' => Carbon::now()->addHour()
        ]);

        // Token expirado
        $expiredToken = AuthToken::create([
            'usuario_id' => $user->id,
            'token' => 'expiredtoken',
            'expires_at' => Carbon::now()->subHour()
        ]);

        $this->assertTrue($validToken->isValid());
        $this->assertFalse($expiredToken->isValid());
    }
}