<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Usuario::factory()->create([
            'email' => 'user@example.com',
            'senha' => 'password123'
        ]);

        // Login para obter token
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'senha' => 'password123'
        ]);

        $this->token = $response->json('token');
    }

    public function test_can_create_order()
    {
        $orderData = [
            'descricao' => 'Notebook Dell',
            'valor' => 3500.50,
            'moeda' => 'BRL'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'order' => [
                    'id', 'descricao', 'valor', 'moeda',
                    'valor_brl', 'valor_usd', 'created_at'
                ]
            ]);

        $this->assertDatabaseHas('pedidos', [
            'descricao' => 'Notebook Dell',
            'valor' => 3500.50,
            'moeda' => 'BRL',
            'usuario_id' => $this->user->id
        ]);
    }

    public function test_can_list_orders()
    {
        Pedido::factory()->count(10)->create(['usuario_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/orders?page=1&limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total'
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_show_order()
    {
        $order = Pedido::factory()->create(['usuario_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'descricao', 'valor', 'moeda',
                'valor_brl', 'valor_usd', 'created_at'
            ]);
    }

    public function test_can_update_own_order()
    {
        $order = Pedido::factory()->create(['usuario_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/orders/' . $order->id, [
            'descricao' => 'Updated Description',
            'valor' => 4000.00,
            'moeda' => 'USD'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Pedido atualizado com sucesso']);

        $this->assertDatabaseHas('pedidos', [
            'id' => $order->id,
            'descricao' => 'Updated Description',
            'valor' => 4000.00,
            'moeda' => 'USD'
        ]);
    }

    public function test_cannot_update_other_user_order()
    {
        $otherUser = Usuario::factory()->create();
        $order = Pedido::factory()->create(['usuario_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/orders/' . $order->id, [
            'descricao' => 'Updated Description'
        ]);

        $response->assertStatus(403);
    }

    public function test_can_delete_own_order()
    {
        $order = Pedido::factory()->create(['usuario_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Pedido excluído com sucesso']);

        $this->assertDatabaseMissing('pedidos', ['id' => $order->id]);
    }

    public function test_cannot_delete_other_user_order()
    {
        $otherUser = Usuario::factory()->create();
        $order = Pedido::factory()->create(['usuario_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(403);
    }

    public function test_order_validation()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/orders', [
            'descricao' => '',
            'valor' => 'invalid',
            'moeda' => 'INVALID'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['descricao', 'valor', 'moeda']);
    }
}