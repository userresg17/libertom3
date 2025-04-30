<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Adicionado SoftDeletes

class GiftCard extends Model
{
    use HasFactory, SoftDeletes; // <-- Adicionado SoftDeletes

    protected $fillable = [
        'code',
        'currency',
        'initial_value',
        'balance',
        'status', // 'active', 'redeemed', 'expired', 'cancelled'
        'purchased_by_user_id',
        'redeemed_by_user_id',
        'recipient_email',
        'message',
        'expires_at',
        'redeemed_at',
    ];

    protected $casts = [
        'initial_value' => 'decimal:8',
        'balance' => 'decimal:8',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    // --- Relacionamentos ---
    public function purchaser(): BelongsTo { return $this->belongsTo(User::class, 'purchased_by_user_id'); }
    public function redeemer(): BelongsTo { return $this->belongsTo(User::class, 'redeemed_by_user_id'); }

    // --- Bootable Methods ---
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($giftCard) {
            if (empty($giftCard->code)) { $giftCard->code = self::generateUniqueCode(); }
            if (is_null($giftCard->balance)) { $giftCard->balance = $giftCard->initial_value; }
        });
    }

    // --- Métodos Customizados ---
    /** Gera um código de gift card único. */
    public static function generateUniqueCode(): string
    {
        do { $code = strtoupper( Str::random(4).'-'.Str::random(4).'-'.Str::random(4).'-'.Str::random(4) ); }
        while (self::where('code', $code)->exists());
        return $code;
    }
    /** Verifica se o gift card pode ser resgatado. */
     public function canBeRedeemed(): bool
     { return $this->status === 'active' && (!$this->expires_at || $this->expires_at->isFuture()); }
}