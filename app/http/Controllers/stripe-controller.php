<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    protected $stripeService;

    /**
     * Create a new controller instance.
     *
     * @param StripeService $stripeService
     * @return void
     */
    public function __construct(StripeService $stripeService)
    {
        $this->middleware('auth');
        $this->stripeService = $stripeService;
    }

    /**
     * Show the payment form
     *
     * @return \Illuminate\View\View
     */
    public function showPaymentForm()
    {
        $user = Auth::user();
        $wallets = $user->wallets;
        
        return view('dashboard.payment.deposit', [
            'wallets' => $wallets,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    /**
     * Process a deposit request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processDeposit(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:5',
            'payment_method_id' => 'required|string'
        ]);

        $user = Auth::user();
        $wallet = Wallet::findOrFail($request->wallet_id);
        
        // Ensure wallet belongs to user
        if ($wallet->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Invalid wallet selected.');
        }

        try {
            // Create or get Stripe customer
            if (!$user->stripe_customer_id) {
                $customerId = $this->stripeService->createCustomer($user);
                
                if (!$customerId) {
                    return redirect()->back()->with('error', 'Failed to create payment profile.');
                }
                
                // Save the customer ID to user record
                $user->stripe_customer_id = $customerId;
                $user->save();
            }
            
            // Add payment method to customer if it's new
            $this->stripeService->addPaymentMethod($user->stripe_customer_id, $request->payment_method_id);
            
            // Create payment
            $result = $this->stripeService->createCharge(
                $request->amount,
                $wallet->currency,
                $request->payment_method_id,
                $user->stripe_customer_id,
                "Libertom deposit to {$wallet->currency} wallet"
            );

            if (!$result['success']) {
                return redirect()->back()->with('error', 'Payment failed: ' . ($result['error'] ?? 'Unknown error'));
            }

            // If payment requires additional authentication
            if (isset($result['requires_action']) && $result['requires_action']) {
                return redirect()->route('stripe.handle-auth', [
                    'client_secret' => $result['client_secret'],
                    'wallet_id' => $wallet->id,
                    'amount' => $request->amount
                ]);
            }

            // Payment successful - create transaction and update balance
            $transaction = new Transaction([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'currency' => $wallet->currency,
                'description' => 'Deposit via credit/debit card',
                'status' => 'completed',
                'provider' => 'stripe',
                'provider_transaction_id' => $result['payment_intent']->id
            ]);
            $transaction->save();
            
            // Update wallet balance
            $wallet->balance += $request->amount;
            $wallet->save();
            
            return redirect()->route('wallets.show', $wallet->id)
                ->with('success', "Successfully deposited {$request->amount} {$wallet->currency} to your account.");
                
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $request->amount
            ]);
            
            return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle additional payment authentication
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function handleAuthentication(Request $request)
    {
        $request->validate([
            'client_secret' => 'required|string',
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric'
        ]);
        
        $wallet = Wallet::findOrFail($request->wallet_id);
        
        return view('dashboard.payment.authenticate', [
            'clientSecret' => $request->client_secret,
            'stripeKey' => config('services.stripe.key'),
            'wallet' => $wallet,
            'amount' => $request->amount
        ]);
    }

    /**
     * Handle successful payment return
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handlePaymentReturn(Request $request)
    {
        $request->validate([
            'payment_intent' => 'required|string',
            'payment_intent_client_secret' => 'required|string',
            'wallet_id' => 'nullable|exists:wallets,id'
        ]);
        
        try {
            // Verify payment intent status
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent);
            
            if ($paymentIntent->status === 'succeeded') {
                // Find relevant transaction and mark as completed
                $transaction = Transaction::where('provider', 'stripe')
                    ->where('provider_transaction_id', $paymentIntent->id)
                    ->first();
                
                if ($transaction) {
                    $transaction->status = 'completed';
                    $transaction->save();
                    
                    // Update wallet balance if needed
                    if ($transaction->type === 'deposit') {
                        $wallet = Wallet::find($transaction->wallet_id);
                        if ($wallet) {
                            $wallet->balance += $transaction->amount;
                            $wallet->save();
                        }
                    }
                    
                    return redirect()->route('wallets.show', $transaction->wallet_id)
                        ->with('success', "Payment completed successfully!");
                }
                
                // If no matching transaction found but payment succeeded
                $walletId = $request->wallet_id;
                if ($walletId) {
                    return redirect()->route('wallets.show', $walletId)
                        ->with('success', "Payment completed successfully!");
                }
                
                return redirect()->route('dashboard')
                    ->with('success', "Payment completed successfully!");
            } else {
                // Payment not successful
                return redirect()->route('dashboard')
                    ->with('error', "Payment not completed. Status: {$paymentIntent->status}");
            }
        } catch (ApiErrorException $e) {
            Log::error('Error verifying payment: ' . $e->getMessage(), [
                'payment_intent' => $request->payment_intent
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Failed to verify payment completion.');
        }
    }
    
    /**
     * Process a withdrawal request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processWithdrawal(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:10',
            'bank_account_id' => 'required|string',
            'transaction_password' => 'required|string'
        ]);
        
        $user = Auth::user();
        $wallet = Wallet::findOrFail($request->wallet_id);
        
        // Verify wallet belongs to user
        if ($wallet->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Invalid wallet selected.');
        }
        
        // Verify transaction password
        if (!$user->verifyTransactionPassword($request->transaction_password)) {
            return redirect()->back()->with('error', 'Invalid transaction password.');
        }
        
        // Check sufficient balance
        if ($wallet->balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient balance for this withdrawal.');
        }
        
        // Process payout
        $result = $this->stripeService->createPayout(
            $wallet,
            $request->amount,
            $request->bank_account_id
        );
        
        if (!$result['success']) {
            return redirect()->back()->with('error', 'Withdrawal failed: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return redirect()->route('wallets.show', $wallet->id)
            ->with('success', "Withdrawal of {$request->amount} {$wallet->currency} has been initiated.");
    }

    /**
     * Process an international money transfer
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processInternationalTransfer(Request $request)
    {
        $request->validate([
            'source_wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:10',
            'currency' => 'required|string|size:3',
            'recipient_name' => 'required|string|max:255',
            'recipient_country' => 'required|string|size:2',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'routing_number' => 'required|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift' => 'nullable|string|max:255',
            'transaction_password' => 'required|string'
        ]);
        
        $user = Auth::user();
        $wallet = Wallet::findOrFail($request->source_wallet_id);
        
        // Verify wallet belongs to user
        if ($wallet->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Invalid wallet selected.');
        }
        
        // Verify transaction password
        if (!$user->verifyTransactionPassword($request->transaction_password)) {
            return redirect()->back()->with('error', 'Invalid transaction password.');
        }
        
        // Check sufficient balance
        if ($wallet->balance < $request->amount) {
            return redirect()->back()->with('error', 'Insufficient balance for this transfer.');
        }
        
        // Process international transfer
        $recipient = [
            'name' => $request->recipient_name,
            'email' => $request->recipient_email ?? null,
            'country' => $request->recipient_country
        ];
        
        $bankDetails = [
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'routing_number' => $request->routing_number,
            'iban' => $request->iban,
            'swift' => $request->swift
        ];
        
        $result = $this->stripeService->createInternationalTransfer(
            $wallet,
            $recipient,
            $request->amount,
            $request->currency,
            $bankDetails
        );
        
        if (!$result['success']) {
            return redirect()->back()->with('error', 'Transfer failed: ' . ($result['error'] ?? 'Unknown error'));
        }
        
        return redirect()->route('sendmoney')
            ->with('success', "Your international transfer of {$request->amount} {$wallet->currency} " . 
                "({$result['converted_amount']} {$request->currency}) to {$request->recipient_name} has been initiated.");
    }

    /**
     * Handle Stripe webhooks
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook.secret');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $signature, $webhookSecret
            );
            
            // Handle specific event types
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;
                case 'payout.paid':
                    $this->handlePayoutPaid($event->data->object);
                    break;
                case 'payout.failed':
                    $this->handlePayoutFailed($event->data->object);
                    break;
                // Add other event handlers as needed
            }
            
            return response()->json(['status' => 'success']);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Webhook handling error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook handling error'], 500);
        }
    }
    
    /**
     * Handle successful payment intent
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $transaction = Transaction::where('provider', 'stripe')
            ->where('provider_transaction_id', $paymentIntent->id)
            ->first();
        
        if ($transaction) {
            // Update transaction status
            $transaction->status = 'completed';
            $transaction->save();
            
            // Update wallet balance if needed
            if ($transaction->type === 'deposit' && $transaction->status !== 'completed') {
                $wallet = Wallet::find($transaction->wallet_id);
                if ($wallet) {
                    $wallet->balance += $transaction->amount;
                    $wallet->save();
                }
            }
        } else {
            // Create transaction record if it doesn't exist
            // This could happen if webhook arrives before our system creates the record
            Log::info('Received payment_intent.succeeded webhook for unknown transaction', [
                'payment_intent_id' => $paymentIntent->id
            ]);
        }
    }
    
    /**
     * Handle failed payment intent
     *
     * @param object $paymentIntent
     * @return void
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $transaction = Transaction::where('provider', 'stripe')
            ->where('provider_transaction_id', $paymentIntent->id)
            ->first();
        
        if ($transaction) {
            $transaction->status = 'failed';
            $transaction->save();
        }
    }
    
    /**
     * Handle successful payout
     *
     * @param object $payout
     * @return void
     */
    protected function handlePayoutPaid($payout)
    {
        $transaction = Transaction::where('provider', 'stripe')
            ->where('provider_transaction_id', $payout->id)
            ->first();
        
        if ($transaction) {
            $transaction->status = 'completed';
            $transaction->save();
        }
    }
    
    /**
     * Handle failed payout
     *
     * @param object $payout
     * @return void
     */
    protected function handlePayoutFailed($payout)
    {
        $transaction = Transaction::where('provider', 'stripe')
            ->where('provider_transaction_id', $payout->id)
            ->first();
        
        if ($transaction) {
            $transaction->status = 'failed';
            $transaction->save();
            
            // Refund the wallet since the payout failed
            $wallet = Wallet::find($transaction->wallet_id);
            if ($wallet) {
                $wallet->balance += abs($transaction->amount);
                $wallet->save();
                
                // Create a refund transaction
                Transaction::create([
                    'user_id' => $transaction->user_id,
                    'wallet_id' => $wallet->id,
                    'type' => 'refund',
                    'amount' => abs($transaction->amount),
                    'currency' => $wallet->currency,
                    'description' => 'Refund of failed withdrawal',
                    'status' => 'completed',
                    'provider' => 'stripe',
                    'provider_transaction_id' => $payout->id . '_refund',
                    'related_transaction_id' => $transaction->id
                ]);
            }
        }
    }
}
