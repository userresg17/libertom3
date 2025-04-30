@extends('layouts.app')

@section('title', 'Complete Payment Authentication')
@section('page-title', 'Additional Authentication Required')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Complete Payment Authentication</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Your bank requires additional verification for this payment. Please complete the authentication process below.</p>
            </div>
            
            <div class="mt-6 flex items-center justify-center">
                <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-6 w-full max-w-md">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="text-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Secure Payment Verification</h4>
                        <p class="text-sm text-gray-500 mt-1">Depositing {{ number_format($amount, 2) }} {{ $wallet->currency }} to your {{ $wallet->currency }} wallet</p>
                    </div>
                    
                    <div id="authentication-element">
                        <!-- Stripe authentication element will be mounted here -->
                    </div>
                    
                    <div id="auth-message" class="mt-4 text-center text-sm">
                        <p>Please wait while we connect to your bank...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stripe = Stripe('{{ $stripeKey }}');
        const clientSecret = '{{ $clientSecret }}';
        const returnUrl = '{{ route("stripe.return", ["wallet_id" => $wallet->id]) }}';
        
        const authMessage = document.getElementById('auth-message');
        
        // Show authentication interface
        stripe.handleNextAction({
            clientSecret: clientSecret,
            returnUrl: returnUrl
        }).then(function(result) {
            if (result.error) {
                // Authentication failed, show error
                authMessage.innerHTML = `
                    <p class="text-red-600">Authentication failed: ${result.error.message}</p>
                    <div class="mt-4">
                        <a href="{{ route('wallets.show', $wallet->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Return to Wallet
                        </a>
                    </div>
                `;
            } else {
                // Authentication succeeded or is being processed
                if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                    // Payment is successful
                    authMessage.innerHTML = `
                        <p class="text-green-600">Payment was successful!</p>
                        <div class="mt-4">
                            <a href="{{ route('wallets.show', $wallet->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Return to Wallet
                            </a>
                        </div>
                    `;
                } else {
                    // Payment is being processed
                    authMessage.innerHTML = `
                        <p class="text-yellow-600">Your payment is being processed.</p>
                        <div class="mt-4">
                            <a href="{{ route('wallets.show', $wallet->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Return to Wallet
                            </a>
                        </div>
                    `;
                }
            }
        }).catch(function(error) {
            // Handle unexpected errors
            console.error('Stripe authentication error:', error);
            authMessage.innerHTML = `
                <p class="text-red-600">An unexpected error occurred. Please try again.</p>
                <div class="mt-4">
                    <a href="{{ route('wallets.show', $wallet->id) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Return to Wallet
                    </a>
                </div>
            `;
        });
    });
</script>
@endpush