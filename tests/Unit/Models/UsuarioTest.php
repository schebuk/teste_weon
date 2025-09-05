<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_is_hashed_on_creation()
    {
        $user = Usuario::create([
            'nome' => 'Test User',
            'email' => 'test@example.com',
            'senha' => 'plainpassword'
        ]);

        $this->assertNotEquals('plainpassword', $user->senha);
        $this->assertTrue(password_verify('plainpassword', $user->senha));
    }

    public function test_password_is_hashed_on_update()
    {
        $user = Usuario::create([
            'nome' => 'Test User',
            'email' => 'test@example.com',
            'senha' => 'originalpassword'
        ]);

        $originalHash = $user->senha;

        $user->update(['senha' => 'newpassword']);

        $this->assertNotEquals($originalHash, $user->senha);
        $this->assertTrue(password_verify('newpassword', $user->senha));
    }

    public function test_password_is_not_hashed_if_not_changed()
    {
        $user = Usuario::create([
            'nome' => 'Test User',
            'email' => 'test@example.com',
            'senha' => 'password123'
        ]);

        $originalHash = $user->senha;

        $user->update(['nome' => 'Updated Name']);

        $this->assertEquals($originalHash, $user->senha);
    }
}