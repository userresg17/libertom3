<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['stock', 'etf', 'bond']);
        return [
            'symbol' => fake()->unique()->lexify(strtoupper('??????')), // Símbolo maior
            'name' => fake()->company() . ($type === 'stock' ? ' Inc.' : ($type === 'etf' ? ' ETF' : ' Bond')),
            'description' => fake()->paragraph(),
            'type' => $type,
            'logo_url' => fake()->imageUrl(64, 64, 'business', true), // Logo quadrado
            'current_price' => fake()->randomFloat(2, 5, 2500),
            'variation_24h' => fake()->randomFloat(2, -5, 5),
            'operating_fee' => fake()->randomFloat(2, 0.05, 1.2),
            'is_active' => true,
            'rules' => null,
        ];
    }

     public function stock(): static { return $this->state(fn (array $attributes) => ['type' => 'stock']); }
     public function etf(): static { return $this->state(fn (array $attributes) => ['type' => 'etf']); }
     public function inactive(): static { return $this->state(fn (array $attributes) => ['is_active' => false]); }
     public function withRules(): static {
         return $this->state(fn (array $attributes) => [
             'rules' => [['time' => '09:00', 'percentage_change' => '0.5'], ['time' => '17:00', 'percentage_change' => '-0.2']]
         ]);
     }
}