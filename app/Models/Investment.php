<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Adicionado SoftDeletes

class Investment extends Model
{
    use HasFactory, SoftDeletes; // <-- Adicionado SoftDeletes

    protected $fillable = [
        'user_id',
        'asset_id',
        'quantity', // Quantidade total que o usuário possui
        'average_price', // Preço médio de compra ponderado
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'average_price' => 'decimal:8',
    ];

    // --- Relacionamentos ---
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }

    // --- Accessors (Cálculos para frontend) ---
    /** Calcula o valor atual da holding. */
    protected function currentValue(): Attribute { return Attribute::make( get: fn () => $this->quantity * ($this->asset?->current_price ?? 0) ); }
    /** Calcula o custo total da holding. */
    protected function totalCost(): Attribute { return Attribute::make( get: fn () => $this->quantity * $this->average_price ); }
    /** Calcula o Lucro/Prejuízo (P/L) total em valor. */
    protected function totalPlValue(): Attribute { return Attribute::make( get: fn () => $this->current_value - $this->total_cost ); }
    /** Calcula o Lucro/Prejuízo (P/L) total em percentual. */
    protected function totalPlPercentage(): Attribute { return Attribute::make( get: fn () => ($this->total_cost > 0) ? ($this->total_pl_value / $this->total_cost) * 100 : 0 ); }
     /** Calcula a variação diária em percentual. */
     protected function dailyPlPercentage(): Attribute { return Attribute::make( get: fn () => $this->asset?->variation_24h ?? 0 ); }
}