<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id', // Carteira de origem (pode ser nulo para ajustes admin?)
        'related_transaction_id', // Para ligar transferências, estornos, etc.
        'type', // 'deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'conversion_from', 'conversion_to', 'investment_buy', 'investment_sell', 'fee', 'gift_card_purchase', 'gift_card_redeem', 'goldstay_buy', 'goldstay_sell', 'goldstay_deposit', 'goldstay_withdraw', 'admin_adjustment'
        'status', // 'pending', 'completed', 'failed', 'cancelled', 'processing'
        'currency', // Moeda da transação principal (fiat)
        'amount', // Valor na moeda principal (pode ser negativo)
        'asset_id', // ID do Ativo (para transações de investimento)
        'quantity', // Quantidade do ativo (para investimentos)
        'price_per_unit', // Preço por unidade do ativo (para investimentos)
        'fee_currency', // Moeda da taxa (pode ser diferente)
        'fee_amount', // Valor da taxa
        'description', // Descrição manual ou automática
        'metadata', // JSON para detalhes extras (ex: dados do destinatário, hash da tx blockchain)
        'balance_before', // Saldo antes da transação (informativo)
        'balance_after', // Saldo depois da transação (informativo)
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'quantity' => 'decimal:8',
        'price_per_unit' => 'decimal:8',
        'fee_amount' => 'decimal:8',
        'balance_before' => 'decimal:8',
        'balance_after' => 'decimal:8',
        'metadata' => 'array',
    ];

    // --- Relacionamentos ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'related_transaction_id');
    }

    // --- Accessors (Exemplo) ---

    /**
     * Retorna um label legível para o tipo de transação.
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->type) {
                 'deposit' => 'Depósito Recebido',
                 'withdrawal' => 'Saque Realizado',
                 'transfer_in' => 'Transferência Recebida',
                 'transfer_out' => 'Transferência Enviada',
                 'conversion_from' => 'Conversão (Débito)',
                 'conversion_to' => 'Conversão (Crédito)',
                 'investment_buy' => 'Compra de Ativo',
                 'investment_sell' => 'Venda de Ativo',
                 'fee' => 'Taxa de Serviço',
                 'gift_card_purchase' => 'Compra Gift Card',
                 'gift_card_redeem' => 'Resgate Gift Card',
                 'goldstay_buy' => 'Compra GoldStay',
                 'goldstay_sell' => 'Venda GoldStay',
                 'goldstay_deposit' => 'Depósito GoldStay',
                 'goldstay_withdraw' => 'Saque GoldStay',
                 'admin_adjustment' => 'Ajuste Administrativo',
                 default => ucfirst(str_replace('_', ' ', $this->type)),
            }
        );
    }

     /**
      * Retorna uma cor (Tailwind) para o tipo de transação (exemplo).
      */
     protected function typeColor(): Attribute
     {
         return Attribute::make(
             get: fn () => match ($this->type) {
                  'deposit', 'transfer_in', 'conversion_to', 'investment_sell', 'gift_card_redeem', 'goldstay_sell', 'goldstay_deposit' => 'bg-green-800 text-green-300',
                  'withdrawal', 'transfer_out', 'conversion_from', 'investment_buy', 'fee', 'gift_card_purchase', 'goldstay_buy', 'goldstay_withdraw' => 'bg-red-800 text-red-300',
                  'admin_adjustment' => 'bg-yellow-800 text-yellow-300',
                  default => 'bg-gray-700 text-gray-300',
             }
         );
     }
}