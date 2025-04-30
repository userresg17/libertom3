<?php

namespace Database\Factories;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GiftCardFactory extends Factory
{
    protected $model = GiftCard::class;

    public function definition(): array
    {
        $currency = fake()->randomElement(['USD', 'BRL', 'EUR']);
        $value = fake()->randomElement([10, 20, 50, 100, 200]);
        $status = fake()->randomElement(['active', 'redeemed', 'expired', 'cancelled']);
        $purchaser = User::factory();
        $redeemer = ($status === 'redeemed') ? (User::factory()) : null;

        return [
            // code é gerado no Model
            'currency' => $currency,
            'initial_value' => $value,
            'balance' => ($status === 'active') ? $value : 0,
            'status' => $status,
            'purchased_by_user_id' => $purchaser->id,
            'redeemed_by_user_id' => $redeemer?->id,
            'recipient_email' => fake()->optional(0.7)->safeEmail(), // 70% chance de ter destinatário
            'message' => fake()->optional(0.5)->sentence(), // 50% chance de ter mensagem
            'expires_at' => ($status === 'expired') ? now()->subMonths(fake()->numberBetween(1, 6)) : now()->addYear(),
            'redeemed_at' => ($status === 'redeemed') ? now()->subDays(fake()->numberBetween(1, 60)) : null,
        ];
    }

     public function active(): static { return $this->state(fn (array $attributes) => ['status' => 'active', 'balance' => $attributes['initial_value'], 'redeemed_by_user_id' => null, 'redeemed_at' => null, 'expires_at' => now()->addYear()]); }
     public function redeemed(): static { return $this->state(fn (array $attributes) => ['status' => 'redeemed', 'balance' => 0, 'redeemed_by_user_id' => User::factory(), 'redeemed_at' => now()->subDays(fake()->numberBetween(1, 60))]); }
     public function expired(): static { return $this->state(fn (array $attributes) => ['status' => 'expired', 'expires_at' => now()->subMonths(fake()->numberBetween(1, 6))]); }
     public function cancelled(): static { return $this->state(fn (array $attributes) => ['status' => 'cancelled', 'balance' => 0]); }
}