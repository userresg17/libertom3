<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Extende Authenticatable
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Usar Spatie para permissões de admin

class AdminUser extends Authenticatable // Nome da classe pode ser só Admin se preferir
{
    use HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'admin'; // Especifica o guard para Spatie Permissions se usar múltiplos guards

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_users'; // Nome da tabela se diferente da convenção

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // --- Relacionamentos ---

    /**
     * Get the audit logs created by this admin user.
     */
    public function auditLogs()
    {
        // Se o AuditLog guardar admin_user_id
        // return $this->hasMany(AuditLog::class, 'admin_user_id');

        // Alternativa: Se AuditLog usa morphs (polimórfico)
         return $this->morphMany(AuditLog::class, 'causer');
    }

}