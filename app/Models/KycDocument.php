<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;


class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'selfie', 'doc_front', 'doc_back', 'proof_of_address' etc.
        'file_path', // Caminho no storage (ex: 'kyc/user_1/doc_front_timestamp.jpg')
        'status', // Status específico do documento (se necessário, ex: 'uploaded', 'verified', 'rejected')
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    // --- Relacionamentos ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // --- Accessors ---

    /**
     * Retorna a URL completa (temporária ou pública) para o arquivo.
     * A lógica exata depende da configuração do seu Filesystem (local, s3, etc.)
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            // ATENÇÃO: URL Temporária é mais segura para S3 privado.
            // Se o disco for público, Storage::url() pode ser usado diretamente.
            // Ajuste conforme sua configuração de storage.
            get: fn () => $this->file_path ? Storage::temporaryUrl($this->file_path, now()->addMinutes(15)) : null
            // get: fn () => $this->file_path ? Storage::url($this->file_path) : null // Se disco público
        );
    }

     /**
      * Alias para compatibilidade com a view que usei (temporaryUrl).
      * Remova ou ajuste conforme necessário.
      */
     public function temporaryUrl(): ?string
     {
         return $this->file_url;
     }

    // --- Bootable Methods ---

    protected static function boot()
    {
        parent::boot();

        // Deletar o arquivo físico quando o registro do documento for deletado
        static::deleted(function ($document) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}