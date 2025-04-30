<?php

namespace Database\Factories;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        $currency = fake()->randomElement(['USD', 'BRL', 'EUR', 'ARS', 'UYU', 'GBP', 'JPY']);
        $decimals = ($currency === 'JPY') ? 0 : 2;

        return [
            'user_id' => User::factory(),
            'currency' => $currency,
            'balance' => fake()->randomFloat($decimals + 2, 10, 10000), // +2 para precisão interna
            'iban' => 'LT' . fake()->iban(null, 'LT'), // IBAN de exemplo (Lituânia)
            'decimal_places' => $decimals,
            'is_active' => true,
        ];
    }

    public function currency(string $currencyCode): static
    {
        $decimals = match(strtoupper($currencyCode)) {
            'JPY' => 0,
            default => 2,
        };
         return $this->state(fn (array $attributes) => [
             'currency' => $currencyCode,
             'decimal_places' => $decimals,
         ]);
    }
}