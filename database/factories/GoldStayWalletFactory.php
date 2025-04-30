<?php

namespace Database\Factories;

use App\Models\GoldStayWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GoldStayWalletFactory extends Factory
{
    protected $model = GoldStayWallet::class;

    public function definition(): array
    {
        // Função auxiliar para gerar endereço estilo Ethereum
        $generateAddress = fn() => '0x' . bin2hex(random_bytes(20));

        return [
            'user_id' => User::factory(),
            'polygon_address' => $generateAddress(),
            'balance' => fake()->randomFloat(18, 0.0001, 10000), // Precisão 18
        ];
    }
}