<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Exceptions\ExternalApiException;
use Carbon\Carbon;

class CoraService
{
    /**
     * URL base da API do Banco Cora
     * 
     * @var string
     */
    protected $baseUrl;
    
    /**
     * Token de autenticação da API
     * 
     * @var string
     */
    protected $apiToken;
    
    /**
     * Construtor do serviço
     */
    public function __construct()
    {
        $this->baseUrl = env('CORA_API_URL', 'https://api.cora.com.br/api/v1');
        $this->apiToken = env('CORA_API_KEY');
    }
    
    /**
     * Cria uma conta virtual para o usuário no Banco Cora
     * 
     * @param \App\Models\User $user O usuário para o qual criar a conta
     * @param string $currency Moeda da conta (normalmente 'BRL')
     * @return \App\Models\Wallet A carteira criada para o usuário
     * @throws \App\Exceptions\ExternalApiException
     */
    public function createVirtualAccount($user, $currency = 'BRL')
    {
        try {
            // Chamada à API do Cora para criar conta virtual
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/accounts", [
                    'name' => $user->name,
                    'document_number' => preg_replace('/[^0-9]/', '', $user->document_number),
                    'email' => $user->email,
                    'phone' => preg_replace('/[^0-9]/', '', $user->phone),
                    'type' => 'individual',
                    'address' => [
                        'street' => $user->address->street ?? '',
                        'number' => $user->address->number ?? '',
                        'complement' => $user->address->complement ?? '',
                        'neighborhood' => $user->address->district ?? '',
                        'city' => $user->address->city ?? '',
                        'state' => $user->address->state ?? '',
                        'postal_code' => preg_replace('/[^0-9]/', '', $user->address->postal_code ?? '')
                    ]
                ]);
            
            // Verificar resposta
            if (!$response->successful()) {
                throw new ExternalApiException('Falha ao criar conta virtual: ' . $response->body());
            }
            
            $accountData = $response->json();
            
            // Criar nova wallet para o usuário no banco de dados
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'currency' => $currency,
                'account_number' => $accountData['account_number'],
                'branch_number' => $accountData['branch_number'],
                'bank_code' => $accountData['bank_code'],
                'reference' => $accountData['id'],
                'balance' => 0,
                'provider' => 'cora',
                'status' => 'active',
                'iban' => $accountData['iban'] ?? null,
                'account_type' => 'checking'
            ]);
            
            // Registrar nos logs
            Log::info("Conta virtual Cora criada com sucesso para o usuário {$user->id}");
            
            return $wallet;
        } catch (ExternalApiException $e) {
            Log::error("Erro ao criar conta virtual Cora: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro interno ao criar conta virtual Cora: {$e->getMessage()}");
            throw new ExternalApiException("Erro interno ao processar requisição de conta virtual: {$e->getMessage()}");
        }
    }
    
    /**
     * Obtém o saldo disponível na conta Cora
     * 
     * @param \App\Models\Wallet $wallet A carteira para verificar o saldo
     * @return float O saldo disponível
     * @throws \App\Exceptions\ExternalApiException
     */
    public function getBalance($wallet)
    {
        try {
            // Chamada à API do Cora para obter saldo
            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/accounts/{$wallet->reference}/balance");
            
            // Verificar resposta
            if (!$response->successful()) {
                throw new ExternalApiException('Falha ao obter saldo: ' . $response->body());
            }
            
            $balanceData = $response->json();
            
            // Atualizar saldo na carteira do usuário
            $wallet->update([
                'balance' => $balanceData['available_balance'],
                'last_updated_at' => Carbon::now()
            ]);
            
            return $balanceData['available_balance'];
        } catch (ExternalApiException $e) {
            Log::error("Erro ao obter saldo Cora: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro interno ao obter saldo Cora: {$e->getMessage()}");
            throw new ExternalApiException("Erro interno ao processar requisição de saldo: {$e->getMessage()}");
        }
    }
    
    /**
     * Cria uma cobrança PIX
     * 
     * @param float $amount Valor da cobrança
     * @param \App\Models\Wallet $wallet Carteira para receber o pagamento
     * @param string $description Descrição da cobrança
     * @param int $expiresInMinutes Tempo de expiração em minutos
     * @return array Dados da cobrança PIX criada
     * @throws \App\Exceptions\ExternalApiException
     */
    public function createPixCharge($amount, $wallet, $description = '', $expiresInMinutes = 60)
    {
        try {
            // Validar o valor
            if ($amount <= 0) {
                throw new \InvalidArgumentException("O valor da cobrança PIX deve ser maior que zero");
            }
            
            // Chamada à API do Cora para criar cobrança PIX
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/pix/charges", [
                    'account_id' => $wallet->reference,
                    'amount' => $amount,
                    'description' => $description,
                    'expiration' => $expiresInMinutes,
                    'customer' => [
                        'name' => $wallet->user->name,
                        'email' => $wallet->user->email,
                        'document' => preg_replace('/[^0-9]/', '', $wallet->user->document_number)
                    ]
                ]);
            
            // Verificar resposta
            if (!$response->successful()) {
                throw new ExternalApiException('Falha ao criar cobrança PIX: ' . $response->body());
            }
            
            $chargeData = $response->json();
            
            // Registrar a transação pendente
            $transaction = Transaction::create([
                'user_id' => $wallet->user_id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'status' => 'pending',
                'amount' => $amount,
                'currency' => $wallet->currency,
                'description' => $description ?: 'Depósito via PIX',
                'provider' => 'cora',
                'provider_transaction_id' => $chargeData['id'],
                'provider_data' => json_encode($chargeData),
                'expires_at' => Carbon::now()->addMinutes($expiresInMinutes)
            ]);
            
            // Adicionar ID da transação aos dados retornados
            $chargeData['transaction_id'] = $transaction->id;
            
            Log::info("Cobrança PIX criada com sucesso: {$chargeData['id']}");
            
            return $chargeData;
        } catch (ExternalApiException $e) {
            Log::error("Erro ao criar cobrança PIX: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro interno ao criar cobrança PIX: {$e->getMessage()}");
            throw new ExternalApiException("Erro interno ao processar cobrança PIX: {$e->getMessage()}");
        }
    }
    
    /**
     * Verifica o status de uma cobrança PIX
     * 
     * @param string $chargeId ID da cobrança no Cora
     * @param \App\Models\Transaction|null $transaction Transação relacionada
     * @return array Dados atualizados da cobrança
     * @throws \App\Exceptions\ExternalApiException
     */
    public function getChargeStatus($chargeId, $transaction = null)
    {
        try {
            // Chamada à API do Cora para verificar status da cobrança
            $response = Http::withToken($this->apiToken)
                ->get("{$this->baseUrl}/pix/charges/{$chargeId}");
            
            // Verificar resposta
            if (!$response->successful()) {
                throw new ExternalApiException('Falha ao verificar status da cobrança: ' . $response->body());
            }
            
            $chargeData = $response->json();
            
            // Se uma transação foi fornecida, atualizar seu status
            if ($transaction) {
                // Mapear status do Cora para status internos do Libertom
                $statusMap = [
                    'ACTIVE' => 'pending',
                    'COMPLETED' => 'completed',
                    'EXPIRED' => 'failed',
                    'CANCELLED' => 'cancelled'
                ];
                
                $newStatus = $statusMap[$chargeData['status']] ?? 'pending';
                
                // Se foi pago, atualizar a carteira e a transação
                if ($newStatus === 'completed' && $transaction->status !== 'completed') {
                    // Atualizar transação
                    $transaction->update([
                        'status' => 'completed',
                        'completed_at' => Carbon::now(),
                        'provider_data' => json_encode($chargeData)
                    ]);
                    
                    // Atualizar saldo da carteira
                    $wallet = $transaction->wallet;
                    $wallet->update([
                        'balance' => $wallet->balance + $transaction->amount
                    ]);
                    
                    Log::info("Pagamento PIX confirmado: {$chargeId}");
                } else {
                    // Atualizar apenas o status da transação
                    $transaction->update([
                        'status' => $newStatus,
                        'provider_data' => json_encode($chargeData)
                    ]);
                }
            }
            
            return $chargeData;
        } catch (ExternalApiException $e) {
            Log::error("Erro ao verificar status da cobrança PIX: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro interno ao verificar status da cobrança PIX: {$e->getMessage()}");
            throw new ExternalApiException("Erro interno ao processar verificação de cobrança PIX: {$e->getMessage()}");
        }
    }
    
    /**
     * Realiza um saque da conta Cora para outro banco
     * 
     * @param \App\Models\Wallet $sourceWallet Carteira de origem
     * @param float $amount Valor a ser sacado
     * @param array $destinationAccount Dados da conta de destino
     * @param string $description Descrição da transação
     * @return array Dados da transferência
     * @throws \App\Exceptions\ExternalApiException
     */
    public function withdrawToBank($sourceWallet, $amount, $destinationAccount, $description = '')
    {
        try {
            // Validar o valor
            if ($amount <= 0) {
                throw new \InvalidArgumentException("O valor do saque deve ser maior que zero");
            }
            
            // Verificar saldo disponível
            $balance = $this->getBalance($sourceWallet);
            if ($balance < $amount) {
                throw new \InvalidArgumentException("Saldo insuficiente para realizar o saque");
            }
            
            // Chamada à API do Cora para realizar a transferência
            $response = Http::withToken($this->apiToken)
                ->post("{$this->baseUrl}/transfers", [
                    'source_account_id' => $sourceWallet->reference,
                    'amount' => $amount,
                    'description' => $description ?: 'Saque para conta bancária',
                    'destination_bank_code' => $destinationAccount['bank_code'],
                    'destination_branch_number' => $destinationAccount['branch_number'],
                    'destination_account_number' => $destinationAccount['account_number'],
                    'destination_account_type' => $destinationAccount['account_type'] ?? 'checking',
                    'destination_document_number' => preg_replace('/[^0-9]/', '', $destinationAccount['document_number']),
                    'destination_name' => $destinationAccount['name'],
                    'transfer_type' => 'ted' // TED ou PIX
                ]);
            
            // Verificar resposta
            if (!$response->successful()) {
                throw new ExternalApiException('Falha ao realizar transferência: ' . $response->body());
            }
            
            $transferData = $response->json();
            
            // Registrar a transação
            $transaction = Transaction::create([
                'user_id' => $sourceWallet->user_id,
                'wallet_id' => $sourceWallet->id,
                'type' => 'withdrawal',
                'status' => 'processing',
                'amount' => $amount,
                'currency' => $sourceWallet->currency,
                'description' => $description ?: 'Saque para conta bancária',
                'provider' => 'cora',
                'provider_transaction_id' => $transferData['id'],
                'provider_data' => json_encode($transferData),
                'destination_data' => json_encode($destinationAccount)
            ]);
            
            // Atualizar o saldo da carteira (diminuir o valor sacado)
            $sourceWallet->update([
                'balance' => $sourceWallet->balance - $amount
            ]);
            
            // Adicionar ID da transação aos dados retornados
            $transferData['transaction_id'] = $transaction->id;
            
            Log::info("Saque bancário iniciado com sucesso: {$transferData['id']}");
            
            return $transferData;
        } catch (ExternalApiException $e) {
            Log::error("Erro ao realizar saque bancário: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro interno ao realizar saque bancário: {$e->getMessage()}");
            throw new ExternalApiException("Erro interno ao processar saque bancário: {$e->getMessage()}");
        }
    }
    
    /**
     * Webhook para processar notificações de pagamentos recebidos
     * 
     * @param array $data Dados recebidos no webhook
     * @return bool Retorna true se processado com sucesso
     */
    public function processWebhook($data)
    {
        try {
            Log::info("Webhook Cora recebido: " . json_encode($data));
            
            // Verificar assinatura do webhook (implementação depende da documentação do Cora)
            $signature = request()->header('X-Cora-Signature');
            // TODO: Implementar verificação de assinatura
            
            // Processar diferentes tipos de eventos
            $eventType = $data['event_type'] ?? '';
            
            switch ($eventType) {
                case 'payment.received':
                    // Processar pagamento recebido
                    $paymentId = $data['data']['payment_id'];
                    $transaction = Transaction::where('provider_transaction_id', $paymentId)
                        ->where('provider', 'cora')
                        ->first();
                    
                    if ($transaction) {
                        // Atualizar o status da transação
                        $transaction->update([
                            'status' => 'completed',
                            'completed_at' => Carbon::now(),
                            'provider_data' => json_encode($data['data'])
                        ]);
                        
                        // Atualizar o saldo da carteira
                        $wallet = $transaction->wallet;
                        $wallet->update([
                            'balance' => $wallet->balance + $transaction->amount
                        ]);
                        
                        Log::info("Pagamento via webhook processado com sucesso: {$paymentId}");
                    } else {
                        // Pagamento sem transação correspondente - possível nova entrada
                        Log::warning("Pagamento recebido sem transação associada: {$paymentId}");
                        // Implementar lógica para lidar com entradas não associadas
                    }
                    break;
                
                case 'transfer.completed':
                    // Processar transferência concluída
                    $transferId = $data['data']['transfer_id'];
                    $transaction = Transaction::where('provider_transaction_id', $transferId)
                        ->where('provider', 'cora')
                        ->first();
                    
                    if ($transaction) {
                        $transaction->update([
                            'status' => 'completed',
                            'completed_at' => Carbon::now(),
                            'provider_data' => json_encode($data['data'])
                        ]);
                        
                        Log::info("Transferência via webhook processada com sucesso: {$transferId}");
                    }
                    break;
                
                case 'transfer.failed':
                    // Processar falha na transferência
                    $transferId = $data['data']['transfer_id'];
                    $transaction = Transaction::where('provider_transaction_id', $transferId)
                        ->where('provider', 'cora')
                        ->first();
                    
                    if ($transaction) {
                        $transaction->update([
                            'status' => 'failed',
                            'provider_data' => json_encode($data['data'])
                        ]);
                        
                        // Estornar o valor para a carteira
                        if ($transaction->type === 'withdrawal') {
                            $wallet = $transaction->wallet;
                            $wallet->update([
                                'balance' => $wallet->balance + $transaction->amount
                            ]);
                        }
                        
                        Log::info("Falha de transferência via webhook processada: {$transferId}");
                    }
                    break;
                
                default:
                    Log::info("Evento webhook não processado: {$eventType}");
                    break;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao processar webhook Cora: {$e->getMessage()}");
            return false;
        }
    }
}
