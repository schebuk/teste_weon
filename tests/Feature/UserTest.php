<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Usuario::factory()->create([
            'email' => 'admin@example.com',
            'senha' => 'password123'
        ]);

        // Login para obter token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'senha' => 'password123'
        ]);

        $this->token = $response->json('token');
    }

    public function test_can_create_user()
    {
        $userData = [
            'nome' => 'John Doe',
            'email' => 'john@example.com',
            'senha' => 'password123'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'nome', 'email', 'created_at']
            ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'john@example.com',
            'nome' => 'John Doe'
        ]);
    }

    public function test_cannot_create_user_with_duplicate_email()
    {
        Usuario::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/users', [
            'nome' => 'Test User',
            'email' => 'existing@example.com',
            'senha' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_list_users()
    {
        Usuario::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users?page=2&limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total'
            ])
            ->assertJson([
                'current_page' => 2,
                'per_page' => 5
            ]);
    }

    public function test_can_show_user()
    {
        $user = Usuario::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'nome' => $user->nome,
                'email' => $user->email
            ]);
    }

    public function test_can_update_user()
    {
        $user = Usuario::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/' . $user->id, [
            'nome' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuário atualizado com sucesso'
            ]);

        $this->assertDatabaseHas('usuarios', [
            'id' => $user->id,
            'nome' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_can_delete_user()
    {
        $user = Usuario::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Usuário excluído com sucesso']);

        $this->assertDatabaseMissing('usuarios', ['id' => $user->id]);
    }

    public function test_unauthorized_access_to_protected_routes()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);
    }
}