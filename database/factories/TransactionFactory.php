<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = fake()->randomElement([
            'deposit', 'withdrawal', 'transfer_out', 'transfer_in',
            'conversion_from', 'conversion_to', 'investment_buy', 'investment_sell',
            'fee', 'gift_card_purchase', 'gift_card_redeem', 'goldstay_buy',
            'goldstay_sell', 'goldstay_deposit', 'goldstay_withdraw', 'admin_adjustment'
        ]);
        $status = fake()->randomElement(['completed', 'pending', 'failed', 'cancelled']);
        $currency = fake()->randomElement(['USD', 'BRL', 'EUR']);
        $isCredit = in_array($type, ['deposit', 'transfer_in', 'conversion_to', 'investment_sell', 'gift_card_redeem', 'goldstay_sell', 'goldstay_deposit']);
        $amount = $isCredit ? fake()->randomFloat(2, 10, 5000) : fake()->randomFloat(2, -5000, -10);

        $asset = null;
        $quantity = null;
        $price = null;
        $assetId = null;
        if(in_array($type, ['investment_buy', 'investment_sell'])) {
            $asset = Asset::inRandomOrder()->first() ?? Asset::factory()->create(); // Pega um existente ou cria
            $assetId = $asset->id;
            $quantity = fake()->randomFloat(4, 1, 100);
            $price = $asset->current_price ?? fake()->randomFloat(2, 10, 1000);
            $amount = $quantity * $price * ($type === 'investment_buy' ? -1 : 1);
            $currency = 'USD'; // Assume USD para investimentos por padrão
        }

        $wallet = null;
        $userId = User::factory(); // Assume que criará um novo usuário por padrão
        if($assetId == null) { // Se não for investimento, associa a uma carteira fiat
            $wallet = Wallet::factory()->state(['currency' => $currency, 'user_id' => $userId])->create();
        }


        return [
            'user_id' => $userId,
            'wallet_id' => $wallet?->id,
            'related_transaction_id' => null, // Pode ser definido via state
            'type' => $type,
            'status' => $status,
            'currency' => $currency,
            'amount' => $amount,
            'asset_id' => $assetId,
            'quantity' => $quantity,
            'price_per_unit' => $price,
            'fee_currency' => $currency,
            'fee_amount' => fake()->randomFloat(2, 0.1, 5),
            'description' => fake()->sentence(4),
            'metadata' => ['ip_address' => fake()->ipv4(), 'device' => 'web'],
            'balance_before' => null,
            'balance_after' => null,
        ];
    }

     public function type(string $type): static { return $this->state(fn(array $attrs) => ['type' => $type]); }
     public function status(string $status): static { return $this->state(fn(array $attrs) => ['status' => $status]); }
     // Adicionar mais estados conforme necessário
}