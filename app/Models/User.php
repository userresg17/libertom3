<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // Se usar Spatie Permissions
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- Adicionado SoftDeletes

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes; // <-- Adicionado HasRoles e SoftDeletes

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'transaction_password', // Senha de transação
        'phone_number',         // <-- Adicionado
        'date_of_birth',        // <-- Adicionado
        'address',              // <-- Adicionado
        'kyc_status',
        'kyc_rejection_reason',
        'status',
        'preferred_currency',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'transaction_password', // Ocultar por padrão
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date', // <-- Adicionado cast
        // 'address' => 'array', // <-- Considerar se for usar JSON para endereço
    ];

    // --- Relacionamentos ---

    /** Get the fiat currency wallets for the user. */
    public function wallets(): HasMany { return $this->hasMany(Wallet::class); }
    /** Get the GoldStay wallet for the user. */
    public function goldStayWallet(): HasOne { return $this->hasOne(GoldStayWallet::class); }
    /** Get the user's investment holdings. */
    public function investments(): HasMany { return $this->hasMany(Investment::class); }
    /** Get the user's transactions. */
    public function transactions(): HasMany { return $this->hasMany(Transaction::class); }
    /** Get the user's KYC documents. */
    public function kycDocuments(): HasMany { return $this->hasMany(KycDocument::class); }
    /** Get the user's purchased gift cards. */
    public function purchasedGiftCards(): HasMany { return $this->hasMany(GiftCard::class, 'purchased_by_user_id'); }
    /** Get the gift cards redeemed by the user. */
    public function redeemedGiftCards(): HasMany { return $this->hasMany(GiftCard::class, 'redeemed_by_user_id'); }


    // --- Métodos Customizados ---

    /** Verifica se o usuário definiu uma senha de transação. */
    public function hasTransactionPassword(): bool
    {
        return !empty($this->transaction_password);
    }

    /** Verifica a senha de transação fornecida (usando Hash). */
    public function verifyTransactionPassword(string $password): bool
    {
        // Primeiro, verifica se a senha de transação existe
        if (!$this->hasTransactionPassword()) {
            return false;
        }
        // Verifica a senha usando Hash::check
        return Hash::check($password, $this->transaction_password);
    }

    /** Retorna um documento KYC específico pelo tipo. */
    public function kycDocument(string $type): ?KycDocument
    {
        return $this->kycDocuments()->where('type', $type)->latest()->first();
    }


    // --- Accessors & Mutators ---

    /** Define a senha de transação (automaticamente hasheada). */
    protected function transactionPassword(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::make($value), // <-- Hasheia automaticamente
        );
    }

    /** Retorna um label legível para o status do KYC. */
    protected function kycStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->kyc_status) {
                'pending' => 'Pendente',
                'approved' => 'Aprovado',
                'rejected' => 'Rejeitado',
                'resubmission_requested' => 'Reenvio Solicitado',
                default => 'Não Iniciado',
            }
        );
    }

     /** Retorna uma cor (Tailwind) para o status do KYC. */
     protected function kycStatusColor(): Attribute
     {
         return Attribute::make(
             get: fn () => match ($this->kyc_status) {
                 'pending' => 'yellow',
                 'approved' => 'green',
                 'rejected' => 'red',
                 'resubmission_requested' => 'yellow',
                 default => 'gray',
             }
         );
     }

      /** Verifica se o usuário está bloqueado. */
      protected function isBlocked(): Attribute
      {
          return Attribute::make(
              get: fn () => $this->status === 'blocked'
          );
      }
}