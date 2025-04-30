    <?php

    namespace App\Services\Gateways;

    use App\Models\User;
    use App\Models\Wallet;
    use App\Models\Transaction;
    use Illuminate\Support\Facades\Log;
    use Stripe\Stripe;
    use Stripe\Customer;
    use Stripe\Charge; // Ou PaymentIntent, dependendo do fluxo
    use Stripe\Payout;
    use Stripe\Exception\ApiErrorException; // Exceção específica do Stripe
    use App\Exceptions\ExternalApiException;

    class StripeService
    {
        /**
         * Configura a chave secreta da API Stripe ao instanciar o serviço.
         */
        public function __construct()
        {
            $secretKey = config('services.stripe.secret');
            if (empty($secretKey)) {
                Log::critical("Stripe Secret Key (STRIPE_SECRET) não está configurada.");
                // Lançar exceção ou permitir continuar dependendo da criticidade
                // throw new \InvalidArgumentException("Stripe Secret Key não configurada.");
            } else {
                Stripe::setApiKey($secretKey);
                Stripe::setAppInfo( // Opcional, mas bom para rastreamento no Stripe
                    'Libertom Platform',
                    config('app.version', '1.0.0'),
                    config('app.url')
                );
            }
        }

        /**
         * Cria um novo cliente no Stripe associado a um usuário Libertom.
         * Armazena o stripe_customer_id no model User.
         *
         * @param User $user O usuário Libertom.
         * @return Customer O objeto Customer do Stripe.
         * @throws ExternalApiException Em caso de erro na API Stripe.
         */
        public function createCustomer(User $user): Customer
        {
            // Verificar se o usuário já possui um ID Stripe para evitar duplicação
            if ($user->stripe_customer_id) {
                 Log::info("StripeService: Customer already exists for user", ['user_id' => $user->id, 'stripe_customer_id' => $user->stripe_customer_id]);
                 // Opcional: Tentar buscar o cliente existente no Stripe para retornar
                 try {
                     return Customer::retrieve($user->stripe_customer_id);
                 } catch (ApiErrorException $e) {
                      Log->warning("StripeService: Failed to retrieve existing customer, creating new one.", ['user_id' => $user->id, 'stripe_customer_id' => $user->stripe_customer_id, 'error' => $e->getMessage()]);
                      // Continua para criar um novo (ou lança exceção?)
                 }
            }

            Log::info("StripeService: Creating customer", ['user_id' => $user->id, 'email' => $user->email]);
            try {
                $customer = Customer::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'metadata' => [ // Metadados úteis para referência cruzada
                        'libertom_user_id' => $user->id,
                        'app_name' => config('app.name'),
                    ],
                    // Adicionar endereço, telefone se necessário e disponível no $user
                    // 'address' => [ ... ],
                    // 'phone' => $user->phone_number,
                ]);

                // Salvar o ID do cliente Stripe no usuário Libertom
                $user->stripe_customer_id = $customer->id;
                $user->save();

                Log::info("StripeService: Customer created successfully", ['user_id' => $user->id, 'stripe_customer_id' => $customer->id]);
                return $customer;

            } catch (ApiErrorException $e) {
                Log::error("StripeService: Failed to create customer", ['user_id' => $user->id, 'error' => $e->getMessage(), 'stripe_code' => $e->getStripeCode()]);
                throw new ExternalApiException("Stripe: Erro ao criar cliente ({$e->getStripeCode()}) - {$e->getMessage()}", $e->getHttpStatus() ?? 500, $e);
            } catch (\Exception $e) {
                 Log::error("StripeService: Unexpected exception creating customer", ['user_id' => $user->id, 'error' => $e->getMessage()]);
                 throw new ExternalApiException("Stripe: Erro inesperado ao criar cliente.", 500, $e);
            }
        }

        /**
         * Cria uma cobrança (Charge ou PaymentIntent) no Stripe.
         * Usado para depósitos com cartão ou outros métodos Stripe.
         *
         * @param float $amount Valor da cobrança (na menor unidade da moeda, ex: centavos).
         * @param string $currency Código ISO da moeda (ex: 'usd', 'brl').
         * @param string $paymentMethodId ID do método de pagamento (gerado pelo frontend com Stripe Elements/JS).
         * @param string|null $customerId ID do cliente Stripe (opcional, mas recomendado).
         * @param string|null $description Descrição da cobrança.
         * @param string|null $libertomTxId ID da transação Libertom para referência.
         * @return Charge|PaymentIntent Objeto Charge ou PaymentIntent do Stripe.
         * @throws ExternalApiException
         */
        public function createCharge(
            int $amountInCents, // <-- VALOR EM CENTAVOS
            string $currency,
            string $paymentMethodId,
            ?string $customerId = null,
            ?string $description = null,
            ?string $libertomTxId = null
        ) // : Charge | \Stripe\PaymentIntent // Union Type PHP 8+
        {
            $currency = strtolower($currency);
            Log::info("StripeService: Creating charge", [
                'amount' => $amountInCents, 'currency' => $currency, 'customer' => $customerId, 'tx_id' => $libertomTxId
            ]);

            // --- Usar PaymentIntents é o fluxo recomendado pelo Stripe ---
            // TODO: Implementar lógica com PaymentIntents
            // 1. Criar PaymentIntent no backend
            // 2. Retornar client_secret para o frontend
            // 3. Frontend confirma o pagamento usando stripe.confirmCardPayment(clientSecret, {payment_method: pm_xxx})
            // 4. Backend recebe webhook 'payment_intent.succeeded' ou 'payment_intent.payment_failed'

            // --- Exemplo com Charges (fluxo mais antigo, pode não suportar SCA/3DS bem) ---
            try {
                $chargeParams = [
                    'amount' => $amountInCents,
                    'currency' => $currency,
                    'payment_method' => $paymentMethodId, // Ou 'source' se usar Tokens antigos
                    'description' => $description ?? 'Cobrança Libertom',
                    'metadata' => [
                        'libertom_transaction_id' => $libertomTxId,
                        // 'libertom_user_id' => $user->id // Se não tiver customerId
                    ],
                    // 'customer' => $customerId, // Associar ao cliente Stripe
                    'confirm' => true, // Tenta capturar imediatamente (pode falhar se precisar 3DS)
                    'capture' => true, // Captura o valor (não apenas autoriza)
                    // 'off_session' => true, // Se for cobrança recorrente/sem usuário presente
                ];
                if ($customerId) {
                    $chargeParams['customer'] = $customerId;
                }

                $charge = Charge::create($chargeParams);

                Log::info("StripeService: Charge created successfully", ['charge_id' => $charge->id, 'status' => $charge->status]);

                // TODO: Criar/Atualizar Transaction Libertom com base no $charge->status
                // Se $charge->status === 'succeeded', marcar como 'completed' e creditar Wallet.
                // Se $charge->status === 'pending' ou 'failed', marcar como 'pending' ou 'failed'.

                return $charge;

            } catch (ApiErrorException $e) {
                // Tratar erros específicos como falha no cartão, etc.
                Log::error("StripeService: Failed to create charge", ['error' => $e->getMessage(), 'stripe_code' => $e->getStripeCode()]);
                // O $e->getCharge() pode conter o objeto Charge com status 'failed'
                // TODO: Criar/Atualizar Transaction Libertom como 'failed'
                throw new ExternalApiException("Stripe: Erro ao criar cobrança ({$e->getStripeCode()}) - {$e->getMessage()}", $e->getHttpStatus() ?? 400, $e);
            } catch (\Exception $e) {
                 Log::error("StripeService: Unexpected exception creating charge", ['error' => $e->getMessage()]);
                 throw new ExternalApiException("Stripe: Erro inesperado ao criar cobrança.", 500, $e);
            }
        }

        /**
         * Cria um Payout (saque) para uma conta bancária conectada ou externa.
         * Requer que o usuário tenha uma conta bancária verificada associada no Stripe
         * (seja via Contas Conectadas ou como fonte externa no Customer).
         *
         * @param float $amount Valor do saque (na menor unidade da moeda).
         * @param string $currency Moeda do saque.
         * @param string $destinationAccountId ID da conta bancária de destino no Stripe (ex: 'ba_xxx' ou conta conectada 'acct_xxx').
         * @param string|null $description Descrição.
         * @param string|null $libertomTxId ID da transação Libertom para referência.
         * @return Payout Objeto Payout do Stripe.
         * @throws ExternalApiException
         */
        public function createPayout(
            int $amountInCents,
            string $currency,
            string $destinationAccountId, // ID da conta bancária/conectada no Stripe
            ?string $description = null,
            ?string $libertomTxId = null
        ): Payout
        {
            $currency = strtolower($currency);
            Log::info("StripeService: Creating payout", [
                'amount' => $amountInCents, 'currency' => $currency, 'destination' => $destinationAccountId, 'tx_id' => $libertomTxId
            ]);

            // TODO: Verificar se a conta de destino é válida e pertence ao usuário (se possível)

            try {
                $payout = Payout::create([
                    'amount' => $amountInCents,
                    'currency' => $currency,
                    'destination' => $destinationAccountId, // ID da conta bancária ou conta conectada
                    'method' => 'standard', // ou 'instant' se disponível e desejado
                    'description' => $description ?? 'Saque Libertom',
                    'metadata' => [
                        'libertom_transaction_id' => $libertomTxId,
                        // 'libertom_user_id' => $user->id
                    ],
                    // 'source_type' => 'bank_account', // Pode ser necessário especificar
                ]);

                Log::info("StripeService: Payout created successfully", ['payout_id' => $payout->id, 'status' => $payout->status]);

                // TODO: Criar/Atualizar Transaction Libertom com status 'processing' ou 'pending'
                // O status final ('paid', 'failed', 'canceled') virá via webhook ('payout.paid', 'payout.failed').

                return $payout;

            } catch (ApiErrorException $e) {
                Log::error("StripeService: Failed to create payout", ['error' => $e->getMessage(), 'stripe_code' => $e->getStripeCode()]);
                 // TODO: Criar/Atualizar Transaction Libertom como 'failed'
                throw new ExternalApiException("Stripe: Erro ao criar payout ({$e->getStripeCode()}) - {$e->getMessage()}", $e->getHttpStatus() ?? 400, $e);
            } catch (\Exception $e) {
                 Log::error("StripeService: Unexpected exception creating payout", ['error' => $e->getMessage()]);
                 throw new ExternalApiException("Stripe: Erro inesperado ao criar payout.", 500, $e);
            }
        }

        // --- Tratamento de Webhooks (Exemplo) ---

        /**
         * Processa um evento de webhook recebido do Stripe.
         * DEVE ser chamado por um controller específico para webhooks.
         *
         * @param string $payload Corpo raw do request.
         * @param string $signature Assinatura do header 'Stripe-Signature'.
         * @return bool Sucesso no processamento.
         * @throws \Stripe\Exception\SignatureVerificationException Se a assinatura for inválida.
         */
        // public function handleWebhook(string $payload, string $signature): bool
        // {
        //     $webhookSecret = config('services.stripe.webhook_secret');
        //     if (empty($webhookSecret)) {
        //         Log::error("StripeService: Webhook secret is not configured.");
        //         return false;
        //     }
        //
        //     try {
        //         // Verificar a assinatura do webhook (ESSENCIAL para segurança)
        //         $event = \Stripe\Webhook::constructEvent(
        //             $payload, $signature, $webhookSecret
        //         );
        //     } catch (\UnexpectedValueException $e) {
        //         // Invalid payload
        //         Log->error("StripeService: Invalid webhook payload.", ['error' => $e->getMessage()]);
        //         return false;
        //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
        //         // Invalid signature
        //         Log::error("StripeService: Invalid webhook signature.", ['error' => $e->getMessage()]);
        //         return false; // Não processar!
        //     }
        //
        //     Log::info("StripeService: Received webhook event", ['type' => $event->type, 'id' => $event->id]);
        //
        //     // Processar o evento com base no tipo
        //     try {
        //         match ($event->type) {
        //             'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event->data->object),
        //             'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event->data->object),
        //             'charge.succeeded' => $this->handleChargeSucceeded($event->data->object), // Se usar Charges
        //             'charge.failed' => $this->handleChargeFailed($event->data->object),       // Se usar Charges
        //             'payout.paid' => $this->handlePayoutPaid($event->data->object),
        //             'payout.failed' => $this->handlePayoutFailed($event->data->object),
        //             'payout.canceled' => $this->handlePayoutCanceled($event->data->object),
        //             // Adicionar outros eventos relevantes (customer created, etc.)
        //             default => Log::warning("StripeService: Unhandled webhook event type received", ['type' => $event->type]),
        //         };
        //         return true; // Evento processado (ou ignorado conscientemente)
        //     } catch (\Exception $e) {
        //          Log::error("StripeService: Error processing webhook event", ['type' => $event->type, 'error' => $e->getMessage()]);
        //          return false; // Indica falha no processamento
        //     }
        // }

        // /** Exemplo: Trata PaymentIntent bem-sucedido */
        // private function handlePaymentIntentSucceeded(\Stripe\PaymentIntent $paymentIntent): void
        // {
        //     $libertomTxId = $paymentIntent->metadata['libertom_transaction_id'] ?? null;
        //     Log::info("StripeWebhook: PaymentIntent Succeeded", ['pi_id' => $paymentIntent->id, 'libertom_tx_id' => $libertomTxId]);
        //     // TODO: Buscar Transaction Libertom pelo libertomTxId ou paymentIntent->id (salvo em metadata?).
        //     // TODO: Verificar se já não foi processada.
        //     // TODO: Atualizar status para 'completed'.
        //     // TODO: Creditar na Wallet do usuário.
        //     // TODO: Criar Transaction 'deposit' (se ainda não existir).
        // }

        // /** Exemplo: Trata Payout pago */
        // private function handlePayoutPaid(\Stripe\Payout $payout): void
        // {
        //     $libertomTxId = $payout->metadata['libertom_transaction_id'] ?? null;
        //     Log::info("StripeWebhook: Payout Paid", ['po_id' => $payout->id, 'libertom_tx_id' => $libertomTxId]);
        //     // TODO: Buscar Transaction Libertom pelo libertomTxId ou payout->id.
        //     // TODO: Atualizar status para 'completed'.
        // }

        // /** Exemplo: Trata Payout falho */
        // private function handlePayoutFailed(\Stripe\Payout $payout): void
        // {
        //     $libertomTxId = $payout->metadata['libertom_transaction_id'] ?? null;
        //     $failureCode = $payout->failure_code;
        //     $failureMessage = $payout->failure_message;
        //     Log::warning("StripeWebhook: Payout Failed", compact('libertomTxId', 'failureCode', 'failureMessage'));
        //     // TODO: Buscar Transaction Libertom.
        //     // TODO: Atualizar status para 'failed'.
        //     // TODO: Estornar valor para a Wallet do usuário? (IMPORTANTE!)
        //     // TODO: Notificar usuário/admin.
        // }

        // ... outros handlers para falhas e cancelamentos ...

    }
    ```

---

Este é o esqueleto para o `StripeService`. Ele configura a chave da API e define os métodos para criar clientes, cobranças e payouts, além de incluir um exemplo de como estruturar o tratamento de webhooks (que é crucial para pagamentos com Stripe).

**Próximos Passos:**

1.  **Instalar SDK:** `composer require stripe/stripe-php`.
2.  **Configurar .env:** Adicionar `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
3.  **Implementar Lógica:** Preencher os `// TODO:` com as chamadas reais à API Stripe, mapeamento de dados e interação com os Models `User` e `Transaction`. Prestar atenção especial ao fluxo de PaymentIntents (recomendado) vs Charges.
4.  **Implementar Webhook Controller:** Criar um controller dedicado para receber e validar os webhooks do Stripe, chamando o método `handleWebhook` do `StripeService`.

Podemos seguir para o `MarketDataService.php` (Twelve Dat