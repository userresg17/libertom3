<?php

namespace Database\Factories;

use App\Models\Investment;
use App\Models\User;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestmentFactory extends Factory
{
    protected $model = Investment::class;

    public function definition(): array
    {
        // Garante que o ativo exista antes de criar o investimento
        $asset = Asset::inRandomOrder()->first() ?? Asset::factory()->create();

        return [
            'user_id' => User::factory(),
            'asset_id' => $asset->id,
            'quantity' => fake()->randomFloat(8, 0.001, 1000), // Maior precisão para quantidade
            'average_price' => fake()->randomFloat(8, $asset->current_price * 0.8, $asset->current_price * 1.2), // Preço médio perto do atual
        ];
    }
}