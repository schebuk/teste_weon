<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;

class PedidoFactory extends Factory
{
    public function definition()
    {
        return [
            'usuario_id' => Usuario::factory(),
            'descricao' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2, 10, 1000),
            'moeda' => $this->faker->randomElement(['BRL', 'USD']),
        ];
    }
}