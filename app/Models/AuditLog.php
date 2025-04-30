<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'causer_type', // Model do causador (ex: App\Models\AdminUser)
        'causer_id', // ID do causador
        'action', // Descrição da ação (ex: 'user_blocked', 'balance_adjusted', 'asset_created')
        'subject_type', // Model do objeto afetado (ex: App\Models\User)
        'subject_id', // ID do objeto afetado
        'properties', // JSON com detalhes/dados antigos e novos
        'ip_address', // IP de onde a ação foi originada
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the parent causer model (AdminUser, User, System, etc.).
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent subject model (User, Asset, Setting, etc.).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}