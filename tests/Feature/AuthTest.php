<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials()
    {
        $user = Usuario::factory()->create([
            'email' => 'test@example.com',
            'senha' => 'password123'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'senha' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'expires_at'
            ]);
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'senha' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Credenciais inválidas']);
    }

    public function test_login_validation()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => '',
            'senha' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'senha']);
    }
}