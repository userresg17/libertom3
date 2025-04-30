<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoldStayWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'polygon_address', // Endereço público na rede Polygon
        'balance', // Saldo em GST (alta precisão)
        // Chave privada NUNCA deve ser armazenada aqui. Gerenciada externamente/serviço custódia.
    ];

    protected $casts = [
        'balance' => 'decimal:18', // GoldStay pode ter alta precisão (como ETH)
    ];

    // --- Relacionamentos ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}