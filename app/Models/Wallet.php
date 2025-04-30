<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Adicionado SoftDeletes

class Wallet extends Model
{
    use HasFactory, SoftDeletes; // <-- Adicionado SoftDeletes

    protected $fillable = [
        'user_id',
        'currency',
        'balance',
        'iban',             // <-- Confirmado campo
        'decimal_places',   // <-- Adicionado campo
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:8', // <-- Manter alta precisão interna
        'is_active' => 'boolean',
        'decimal_places' => 'integer', // <-- Cast adicionado
    ];

    // --- Relacionamentos ---
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Accessors ---

    /** Retorna o saldo formatado usando as casas decimais definidas. */
    protected function formattedBalance(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->balance, $this->decimal_places ?? 2)
        );
    }

    /**
     * Retorna o saldo equivalente em USD (exemplo, precisa de serviço de conversão).
     * ESTE É UM EXEMPLO, a lógica real deve usar CurrencyConversionService
     */
    // protected function balanceInUsd(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             if ($this->currency === 'USD') return $this->balance;
    //             // Lógica FAKE - Chamar CurrencyConversionService aqui
    //             $rate = 0.2; if ($this->currency === 'BRL') $rate = 0.19; if ($this->currency === 'EUR') $rate = 1.08;
    //             return $this->balance * $rate;
    //         }
    //     );
    // }
}