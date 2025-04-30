<?php

namespace App\Services\Admin; // Ou App\Services se for usado por ambos

use App\Models\User;
use App\Models\KycDocument; // Importar
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Para lidar com arquivos
use App\Services\AuditLogService;
// use App\Notifications\KycStatusUpdated; // <-- Criar Notificação
// use App\Services\External\KycProviderService; // <-- Placeholder para integração externa

class KycService // Renomear para KycManagementService?
{
     public function __construct(
         private readonly AuditLogService $auditLogService
         // private readonly KycProviderService $kycProvider // Exemplo
        ) {}

    /**
     * Atualiza o status do KYC de um usuário (Ação do Admin).
     */
    public function updateKycStatus(User $user, array $validatedData): User
    {
        // Lógica implementada na resposta anterior...
        $newStatus = $validatedData['status'];
        $reason = $validatedData['reason'] ?? null;
        Log::info('Admin: Updating KYC status', ['user_id' => $user->id, 'new_status' => $newStatus, 'admin_id' => auth('admin')->id()]);
        $oldStatus = $user->kyc_status;

        $updateData = ['kyc_status' => $newStatus];
        $updateData['kyc_rejection_reason'] = ($newStatus === 'rejected' || $newStatus === 'resubmission_requested') ? $reason : null;

        $user->update($updateData);
        $this->auditLogService->log('kyc_status_updated', $user, ['old' => $oldStatus, 'new' => $newStatus, 'reason' => $reason]);

        // TODO: Enviar notificação para o usuário
        // $user->notify(new KycStatusUpdated($newStatus, $reason));

        return $user->refresh();
    }

     /**
      * Processa a submissão de documentos KYC pelo usuário.
      * @throws \Exception Em caso de falha no upload/processamento.
      */
     public function submitUserDocuments(User $user, array $validatedFiles): bool
     {
          Log::info('User: Submitting KYC documents', ['user_id' => $user->id, 'files' => array_keys($validatedFiles)]);
          $basePath = "kyc/user_{$user->id}";
          $uploadedPaths = [];

          DB::beginTransaction();
          try {
              foreach(['selfie', 'doc_front', 'doc_back'] as $type) {
                  if (isset($validatedFiles[$type])) {
                      $file = $validatedFiles[$type];
                      // Deletar documento antigo do mesmo tipo, se existir
                      $oldDoc = $user->kycDocuments()->where('type', $type)->first();
                      if ($oldDoc) {
                          if ($oldDoc->file_path && Storage::disk('private')->exists($oldDoc->file_path)) { // Usar disco privado
                              Storage::disk('private')->delete($oldDoc->file_path);
                          }
                          $oldDoc->delete();
                      }
                      // Salvar novo arquivo
                      $path = $file->store($basePath, 'private'); // Salvar em disco privado
                       if (!$path) throw new \Exception("Falha ao salvar arquivo: $type");
                       $uploadedPaths[$type] = $path;

                      // Registrar no banco
                      $user->kycDocuments()->create([
                          'type' => $type, 'file_path' => $path, 'status' => 'uploaded',
                      ]);
                  }
              }

              // Atualizar status do usuário
              $user->update([ 'kyc_status' => 'pending', 'kyc_rejection_reason' => null ]);
              $this->auditLogService->log('kyc_submitted', $user, [], $user); // Log com usuário como causador
              DB::commit();

               // TODO: Opcional: Disparar Job para verificação externa assíncrona
               // ProcessKycDocumentsJob::dispatch($user);

               return true;
          } catch (\Exception $e) {
              DB::rollBack();
              Log::error("KYC Upload failed", ['user_id' => $user->id, 'error' => $e->getMessage()]);
              // Tentar deletar arquivos que podem ter sido salvos antes do erro
              foreach ($uploadedPaths as $path) {
                  if (Storage::disk('private')->exists($path)) {
                      Storage::disk('private')->delete($path);
                  }
              }
              throw $e; // Relança para o controller tratar
          }
     }
}