<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\ExternalApiException;
use Illuminate\Http\Client\PendingRequest; // Para tipar o cliente HTTP configurado

class CoraService
{
    private string $baseUrl;
    private string $apiKey; // Assumindo API Key direta por enquanto
    // private string $clientId;
    // private string $clientSecret;
    // private string $authToken;

    public function __construct()
    {
        $this->baseUrl = config('services.cora.base_url');
        $this->apiKey = config('services.cora.api_key');
        // $this->clientId = config('services.cora.client_id');
        // $this->clientSecret = config('services.cora.client_secret');

        // TODO: Implementar obtenção de token OAuth2 se Cora usar esse fluxo
        // $this->authToken = $this->getAuthToken();

        if (empty($this->apiKey)) { // Ou $this->authToken
            Log::critical("Cora API Key (CORA_API_KEY) não está configurada.");
            // Considerar lançar uma exceção aqui se a chave for essencial sempre
        }
    }

    /**
     * Retorna uma instância do cliente HTTP pré-configurada com autenticação.
     */
    private function httpClient(): PendingRequest
    {
        if (empty($this->apiKey)) { // Ou $this->authToken
             throw new ExternalApiException("API Key/Token da Cora não configurado.", 503);
        }
         // Usar withToken se for Bearer Token, ou ajustar conforme autenticação Cora
        return Http::baseUrl($this->baseUrl)
                   ->withToken($this->apiKey)
                   ->timeout(15) // Timeout de 15 segundos
                   ->acceptJson()
                   ->asJson();
    }

    /**
     * Cria uma conta virtual/de pagamento na Cora.
     *
     * @param User $user O usuário Libertom.
     * @return array Dados da conta criada (formato depende da API Cora).
     * @throws ExternalApiException Em caso de erro na API.
     */
    public function createVirtualAccount(User $user): array
    {
        $endpoint = '/contas'; // <<<---- ENDPOINT FICTÍCIO - USAR O REAL DA DOC CORA
        Log::info("CoraService: Creating virtual account", ['user_id' => $user->id]);

        // --- Payload Fictício ---
        $payload = [
            'document' => $user->document_number ?? '11122233344', // Precisa ter CPF/CNPJ no User
            'name' => $user->name,
            'email' => $user->email,
            'type' => 'PAYMENT_ACCOUNT', // Verificar tipo correto na Cora
            'address' => [ /* ... dados de endereço do usuário ... */ ],
            // ... outros dados obrigatórios ...
        ];
        // --- Fim Payload Fictício ---

        try {
            $response = $this->httpClient()->post($endpoint, $payload);

            if (!$response->successful()) {
                 Log::error("CoraService: Failed to create virtual account", [
                     'user_id' => $user->id, 'status' => $response->status(), 'body' => $response->body()
                 ]);
                 // Tentar extrair mensagem de erro da Cora
                 $errorMessage = $response->json('error.message', "Erro ao criar conta virtual ({$response->status()})");
                 throw new ExternalApiException("Cora: " . $errorMessage, $response->status(), null, $response);
            }

            $data = $response->json();
            Log::info("CoraService: Virtual account created successfully", ['user_id' => $user->id, 'response' => $data]);

            // TODO: Salvar dados relevantes da conta (id_cora, agencia, conta) no Wallet BRL ou User
            // Exemplo: $user->wallet('BRL')->update(['gateway_ref_id' => $data['id'], ...]);

            return $data ?? []; // Retorna a resposta completa (ou dados mapeados)

        } catch (\Illuminate\Http\Client\RequestException $e) { // Captura especificamente erros de conexão/timeout
             Log::error("CoraService: Connection exception creating virtual account", ['user_id' => $user->id, 'error' => $e->getMessage()]);
             throw new ExternalApiException("Cora: Erro de comunicação ao criar conta virtual.", 503, $e, $e->response);
        } catch (\Exception $e) {
             if (!$e instanceof ExternalApiException) {
                 Log::error("CoraService: Unexpected exception creating virtual account", ['user_id' => $user->id, 'error' => $e->getMessage()]);
                 throw new ExternalApiException("Cora: Erro inesperado ao criar conta virtual.", 500, $e);
             }
             throw $e; // Relança a ExternalApiException original
        }
    }

    /**
     * Obtém o saldo disponível da conta Cora.
     *
     * @param string $coraAccountId ID da conta na Cora (obtido ao criar ou de config).
     * @return float Saldo atual em BRL.
     * @throws ExternalApiException
     */
    public function getBalance(string $coraAccountId): float
    {
        $endpoint = "/contas/{$coraAccountId}/saldo"; // <<<---- ENDPOINT FICTÍCIO
        Log::debug("CoraService: Getting balance", ['cora_account_id' => $coraAccountId]);

         try {
             $response = $this->httpClient()->get($endpoint);

             if (!$response->successful()) {
                 Log->error("CoraService: Failed to get balance", ['cora_account_id' => $coraAccountId, 'status' => $response->status()]);
                 $errorMessage = $response->json('error.message', "Erro ao obter saldo ({$response->status()})");
                 throw new ExternalApiException("Cora: " . $errorMessage, $response->status(), null, $response);
             }

            // --- Extração Fictícia ---
            $balance = (float) ($response->json('available_balance.amount') ?? // Verificar path real
                                $response->json('saldo_disponivel') ??
                                0.0);
             // --- Fim Extração Fictícia ---

            Log::debug("CoraService: Balance retrieved", ['cora_account_id' => $coraAccountId, 'balance' => $balance]);
            return $balance;

        } catch (\Illuminate\Http\Client\RequestException $e) {
             Log::error("CoraService: Connection exception getting balance", ['cora_account_id' => $coraAccountId, 'error' => $e->getMessage()]);
             throw new ExternalApiException("Cora: Erro de comunicação ao obter saldo.", 503, $e, $e->response);
        } catch (\Exception $e) {
             if (!$e instanceof ExternalApiException) {
                 Log::error("CoraService: Unexpected exception getting balance", ['cora_account_id' => $coraAccountId, 'error' => $e->getMessage()]);
                throw new ExternalApiException("Cora: Erro inesperado ao obter saldo.", 500, $e);
             }
             throw $e;
        }
    }

    /**
     * Cria uma cobrança PIX.
     *
     * @param float $amount Valor em BRL.
     * @param string $description Descrição.
     * @param string $libertomTxId ID único da transação na Libertom para referência.
     * @param string|null $payerDoc CPF/CNPJ do pagador (opcional).
     * @return array Dados da cobrança criada (txid, qr_code_payload, status).
     * @throws ExternalApiException
     */
    public function createPixCharge(float $amount, string $description, string $libertomTxId, ?string $payerDoc = null): array
    {
        $endpoint = '/cob'; // <<<---- ENDPOINT FICTÍCIO - Pode ser /cob ou /cobv
        Log::info("CoraService: Creating PIX charge", ['libertom_tx_id' => $libertomTxId, 'amount' => $amount]);

        // --- Payload Fictício ---
         $payload = [
             // 'txid' => $libertomTxId, // Permitir que Cora gere ou usar o nosso? Verificar doc.
             'calendario' => ['expiracao' => config('libertom.cora.pix_charge_expiry_seconds', 3600)],
             'valor' => ['original' => number_format($amount, 2, '.', '')],
             'chave' => config('services.cora.pix_key'), // Chave da conta Libertom na Cora
             'solicitacaoPagador' => substr($description, 0, 140), // Limitar tamanho
             'infoAdicionais' => [
                  ['nome' => 'LibertomID', 'valor' => $libertomTxId],
              ],
         ];
         // if ($payerDoc) { $payload['devedor'] = [ /* 'cpf' ou 'cnpj' => $payerDoc */ ]; }
         // --- Fim Payload Fictício ---

        try {
             $response = $this->httpClient()->post($endpoint, $payload); // Ou PUT se usar txid próprio?

            if (!$response->successful()) {
                 Log::error("CoraService: Failed to create PIX charge", ['libertom_tx_id' => $libertomTxId, 'status' => $response->status(), 'body' => $response->body()]);
                 $errorMessage = $response->json('error.message', "Erro ao criar cobrança PIX ({$response->status()})");
                 throw new ExternalApiException("Cora: " . $errorMessage, $response->status(), null, $response);
            }

            $data = $response->json();
            Log::info("CoraService: PIX charge created", ['libertom_tx_id' => $libertomTxId, 'cora_txid' => $data['txid'] ?? null]);

            // TODO: Criar/Atualizar Transaction Libertom com status 'pending', cora_txid, qr_code_payload

             // --- Mapeamento Fictício ---
             $chargeData = [
                 'provider' => 'cora',
                 'charge_id' => $data['txid'] ?? null, // ID Cora
                 'libertom_tx_id' => $libertomTxId,
                 'qr_code_payload' => $data['pixCopiaECola'] ?? $data['location'] ?? null, // Verificar campo correto
                 'status' => $data['status'] ?? 'ATIVA', // Status inicial
             ];
             // --- Fim Mapeamento Fictício ---

            return $chargeData;

        } catch (\Illuminate\Http\Client\RequestException $e) {
             Log::error("CoraService: Connection exception creating PIX charge", ['libertom_tx_id' => $libertomTxId, 'error' => $e->getMessage()]);
             throw new ExternalApiException("Cora: Erro de comunicação ao criar cobrança PIX.", 503, $e, $e->response);
        } catch (\Exception $e) {
             if (!$e instanceof ExternalApiException) {
                  Log::error("CoraService: Unexpected exception creating PIX charge", ['libertom_tx_id' => $libertomTxId, 'error' => $e->getMessage()]);
                 throw new ExternalApiException("Cora: Erro inesperado ao criar cobrança PIX.", 500, $e);
             }
             throw $e;
        }
    }

    /**
     * Verifica o status de uma cobrança PIX ou TED na Cora.
     *
     * @param string $coraTxOrTransferId ID da transação/cobrança na Cora.
     * @param string $type 'charge' ou 'transfer' para buscar no endpoint correto.
     * @return array Resposta da API com o status e detalhes.
     * @throws ExternalApiException
     */
    public function getChargeOrTransferStatus(string $coraTxOrTransferId, string $type = 'charge'): array
    {
        // <<<---- ENDPOINT FICTÍCIO - Verificar endpoint para consulta de PIX e TED ---->>>
        $endpoint = ($type === 'charge') ? "/cob/{$coraTxOrTransferId}" : "/transferencias/{$coraTxOrTransferId}";
        Log::debug("CoraService: Getting status", ['id' => $coraTxOrTransferId, 'type' => $type]);

         try {
              $response = $this->httpClient()->get($endpoint);

             if (!$response->successful()) {
                 Log::error("CoraService: Failed to get status", ['id' => $coraTxOrTransferId, 'type' => $type, 'status' => $response->status()]);
                 // 404 pode ser normal se a tx não existe ainda
                 if ($response->status() === 404) {
                     throw new ExternalApiException("Cora: Recurso não encontrado ({$response->status()})", 404, null, $response);
                 }
                 $errorMessage = $response->json('error.message', "Erro ao verificar status ({$response->status()})");
                 throw new ExternalApiException("Cora: " . $errorMessage, $response->status(), null, $response);
             }

             $data = $response->json();
             Log::debug("CoraService: Status retrieved", ['id' => $coraTxOrTransferId, 'status' => $data['status'] ?? 'UNKNOWN']);
             // TODO: Atualizar status da Transaction Libertom correspondente

             return $data ?? []; // Retorna a resposta completa

         } catch (\Illuminate\Http\Client\RequestException $e) {
             Log::error("CoraService: Connection exception getting status", ['id' => $coraTxOrTransferId, 'type' => $type, 'error' => $e->getMessage()]);
             throw new ExternalApiException("Cora: Erro de comunicação ao verificar status.", 503, $e, $e->response);
         } catch (\Exception $e) {
             if (!$e instanceof ExternalApiException) {
                  Log::error("CoraService: Unexpected exception getting status", ['id' => $coraTxOrTransferId, 'type' => $type, 'error' => $e->getMessage()]);
                 throw new ExternalApiException("Cora: Erro inesperado ao verificar status.", 500, $e);
             }
             throw $e;
         }
    }


    /**
     * Inicia um saque (transferência) da conta Cora para conta bancária externa (TED/PIX).
     *
     * @param float $amount Valor em BRL.
     * @param string $method 'pix' ou 'ted'.
     * @param array $recipientData Dados do destinatário (chave PIX ou dados bancários TED).
     * @param string $libertomTxId ID único da transação na Libertom para referência.
     * @return array Dados da transferência iniciada (ID Cora, status).
     * @throws ExternalApiException
     */
    public function withdrawToBank(float $amount, string $method, array $recipientData, string $libertomTxId): array
    {
         // <<<---- ENDPOINT FICTÍCIO - Pode variar para PIX ou TED ---->>>
         $endpoint = '/transferencias';
         Log::info("CoraService: Initiating bank withdrawal", ['libertom_tx_id' => $libertomTxId, 'amount' => $amount, 'method' => $method]);

         // --- Payload Fictício ---
         $payload = [
             'amount' => ['value' => number_format($amount, 2, '.', '')], // Verificar nome do campo
             'target' => [ // Estrutura pode variar muito
                 'account' => [ /* banco, agencia, conta, tipo */ ],
                 'payee' => [ /* nome, document */ ]
             ],
             'type' => ($method === 'pix') ? 'PIX' : 'TED', // Verificar tipo exato
             'client_ref_id' => $libertomTxId, // Referência interna
         ];
         // Adaptar payload com base nos $recipientData e $method
         // --- Fim Payload Fictício ---

         try {
              $response = $this->httpClient()->post($endpoint, $payload);

             if (!$response->successful()) {
                  Log::error("CoraService: Failed to initiate withdrawal", ['libertom_tx_id' => $libertomTxId, 'status' => $response->status(), 'body' => $response->body()]);
                  $errorMessage = $response->json('error.message', "Erro ao iniciar saque ({$response->status()})");
                  throw new ExternalApiException("Cora: " . $errorMessage, $response->status(), null, $response);
             }

             $data = $response->json();
             Log::info("CoraService: Withdrawal initiated", ['libertom_tx_id' => $libertomTxId, 'cora_transfer_id' => $data['id'] ?? null]);

              // --- Mapeamento Fictício ---
              $withdrawalData = [
                  'provider' => 'cora',
                  'transfer_id' => $data['id'] ?? null, // ID da transferência Cora
                  'status' => $data['status'] ?? 'PROCESSING', // Status inicial
                  'libertom_tx_id' => $libertomTxId,
              ];
              // --- Fim Mapeamento Fictício ---

              // TODO: Atualizar Transaction Libertom com status 'processing' e ID do gateway

             return $withdrawalData;

         } catch (\Illuminate\Http\Client\RequestException $e) {
              Log::error("CoraService: Connection exception initiating withdrawal", ['libertom_tx_id' => $libertomTxId, 'error' => $e->getMessage()]);
              throw new ExternalApiException("Cora: Erro de comunicação ao iniciar saque.", 503, $e, $e->response);
         } catch (\Exception $e) {
              if (!$e instanceof ExternalApiException) {
                   Log::error("CoraService: Unexpected exception initiating withdrawal", ['libertom_tx_id' => $libertomTxId, 'error' => $e->getMessage()]);
                  throw new ExternalApiException("Cora: Erro inesperado ao iniciar saque.", 500, $e);
              }
              throw $e;
         }
    }

    // --- Tratamento de Webhooks (Exemplo) ---

    /**
     * Processa um webhook recebido da Cora.
     * DEVE ser chamado por um controller específico para webhooks.
     *
     * @param array $payload Dados recebidos no corpo do webhook.
     * @param string $signature Assinatura do header (se Cora usar para validação).
     * @return bool Sucesso no processamento.
     */
    // public function handleWebhook(array $payload, string $signature): bool
    // {
    //     Log::info("CoraService: Received webhook", ['payload_type' => $payload['event_type'] ?? 'unknown']);
    //
    //     // TODO: 1. Validar a assinatura do webhook (essencial para segurança!)
    //     // if (! $this->isValidSignature($signature, json_encode($payload))) {
    //     //     Log::warning("CoraService: Invalid webhook signature received.");
    //     //     return false;
    //     // }
    //
    //     // TODO: 2. Processar o evento com base no tipo (payload['event_type']?)
    //     $eventType = $payload['event_type'] ?? null; // Verificar nome real do campo
    //     $data = $payload['data'] ?? []; // Verificar path real dos dados
    //
    //     try {
    //         match ($eventType) {
    //             'pix.received' => $this->handlePixReceived($data),
    //             'transfer.completed', 'transfer.failed' => $this->handleTransferStatusUpdate($data),
    //             default => Log::warning("CoraService: Unhandled webhook event type", ['type' => $eventType]),
    //         };
    //         return true;
    //     } catch (\Exception $e) {
    //         Log::error("CoraService: Error processing webhook", ['type' => $eventType, 'error' => $e->getMessage()]);
    //         return false; // Retorna false para que o webhook seja reenviado (se configurado)
    //     }
    // }

    // /** Exemplo: Trata PIX Recebido */
    // private function handlePixReceived(array $data): void
    // {
    //     $coraTxId = $data['txid'] ?? null;
    //     $amount = $data['valor']['original'] ?? null;
    //     $libertomTxId = $this->findLibertomTxIdFromInfoAdicional($data['infoAdicionais'] ?? []); // Precisa buscar nossa ref
    //     Log::info("CoraWebhook: PIX Received", compact('coraTxId', 'amount', 'libertomTxId'));
    //
    //     // TODO: Buscar Transaction Libertom pelo libertomTxId ou coraTxId.
    //     // TODO: Verificar se já não foi processada.
    //     // TODO: Atualizar status para 'completed'.
    //     // TODO: Creditar na Wallet BRL do usuário associado à transação.
    //     // TODO: Criar nova Transaction 'deposit' para o usuário.
    // }

    // /** Exemplo: Trata atualização de status de Transferência (Saque) */
    // private function handleTransferStatusUpdate(array $data): void
    // {
    //      $coraTransferId = $data['id_transferencia'] ?? null;
    //      $newStatusCora = $data['status'] ?? null; // Ex: 'CONFIRMADO', 'REJEITADO'
    //      $libertomTxId = $data['identificador'] ?? null; // Nossa referência
    //      Log::info("CoraWebhook: Transfer Status Update", compact('coraTransferId', 'newStatusCora', 'libertomTxId'));
    //
    //      // TODO: Buscar Transaction Libertom pelo libertomTxId ou coraTransferId.
    //      // TODO: Mapear status Cora para status Libertom ('completed', 'failed').
    //      // TODO: Atualizar status da Transaction.
    //      // TODO: Se falhou, estornar valor para a Wallet do usuário? (CUIDADO!)
    // }

     // /** Valida assinatura do webhook (lógica depende da Cora) */
     // private function isValidSignature(string $signatureHeader, string $rawPayload): bool { /* ... */ }


}