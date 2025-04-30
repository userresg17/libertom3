<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Adicionado SoftDeletes

class Asset extends Model
{
    use HasFactory, SoftDeletes; // <-- Adicionado SoftDeletes

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'type', // 'stock', 'etf', 'bond'
        'logo_url',
        'current_price',
        'variation_24h', // Variação percentual nas últimas 24h
        'operating_fee', // Taxa de operação/custódia (%)
        'is_active', // Para permitir/bloquear negociação
        'rules', // JSON para armazenar regras de variação automática
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'variation_24h' => 'decimal:4',
        'operating_fee' => 'decimal:4',
        'is_active' => 'boolean',
        'rules' => 'array',
    ];

    // --- Relacionamentos ---
    public function investments(): HasMany { return $this->hasMany(Investment::class); }
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }

    // --- Métodos Customizados ---
    /** Aplica as regras de variação automática de preço (Chamado por Job). */
    public function applyAutomaticVariationRules() { /* ... Lógica ... */ }
}