<?php

namespace App\Services; // <- Diretório base para services comuns

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request; // Para pegar IP
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Registra uma ação de auditoria.
     *
     * @param string $action Ação realizada (ex: 'user_blocked').
     * @param Model|null $subject O modelo que sofreu a ação (ex: User).
     * @param array $properties Detalhes extras (ex: dados antigos/novos). NÂO LOGAR DADOS SENSÍVEIS.
     * @param Model|null $causer O usuário/admin que causou a ação (default: admin logado, depois user web).
     */
    public function log(string $action, ?Model $subject = null, array $properties = [], ?Model $causer = null): void
    {
        if (is_null($causer)) {
            $causer = Auth::guard('admin')->user() ?? Auth::user();
        }

        // Filtrar propriedades para não logar dados sensíveis como senhas, tokens, etc.
        $filteredProperties = $this->filterSensitiveProperties($properties);

        try {
            AuditLog::create([
                'action' => $action,
                'subject_id' => $subject?->getKey(),
                'subject_type' => $subject ? get_class($subject) : null,
                'causer_id' => $causer?->getKey(),
                'causer_type' => $causer ? get_class($causer) : null,
                'properties' => !empty($filteredProperties) ? $filteredProperties : null,
                'ip_address' => Request::ip(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to create audit log", [
                'action' => $action,
                'subject_class' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'causer_class' => $causer ? get_class($causer) : null,
                'causer_id' => $causer?->getKey(),
                'error' => $e->getMessage()
            ]);
            // Não relançar exceção aqui para não quebrar o fluxo principal por causa do log
        }
    }

    /**
     * Filtra chaves comuns de dados sensíveis do array de propriedades.
     */
    private function filterSensitiveProperties(array $properties): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'api_key', 'client_secret', 'transaction_password', 'current_password'];
        // Pode ser mais elaborado, verificando chaves aninhadas se necessário
        return array_filter($properties, function ($key) use ($sensitiveKeys) {
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    return false; // Remove a chave se contiver parte sensível
                }
            }
            return true;
        }, ARRAY_FILTER_USE_KEY);
    }
}