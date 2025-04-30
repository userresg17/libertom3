@extends('layouts.app')

@section('title', 'Deposit Funds')
@section('page-title', 'Add Funds to Your Account')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Add Funds to Your Account</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Choose a wallet and enter the amount you would like to deposit.</p>
            </div>
            
            <form id="payment-form" action="{{ route('stripe.process-deposit') }}" method="POST" class="mt-5 space-y-6">
                @csrf
                
                <div>
                    <label for="wallet_id" class="block text-sm font-medium text-gray-700">Select Wallet</label>
                    <select id="wallet_id" name="wallet_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}" data-currency="{{ $wallet->currency }}">
                                {{ $wallet->currency }} Wallet - {{ number_format($wallet->balance, 2, '.', ',') }} {{ $wallet->currency }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount to Deposit</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm" id="currency-symbol">$</span>
                        </div>
                        <input type="number" name="amount" id="amount" min="5" step="0.01" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00" required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm" id="currency-addon">USD</span>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Minimum deposit: <span id="min-deposit">$5.00</span></p>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex flex-col space-y-2">
                        <label for="card-element" class="block text-sm font-medium text-gray-700">Credit or debit card</label>
                        <div id="card-element" class="p-2 border border-gray-300 rounded-md">
                            <!-- Stripe Card Element will be inserted here -->
                        </div>
                        <div id="card-errors" role="alert" class="text-red-600 text-sm"></div>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-md">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-900">Processing Fee:</span>
                        <span id="processing-fee">$0.00</span>
                    </div>
                    <div class="flex justify-between text-sm mt-1 pt-1 border-t border-gray-200">
                        <span class="font-medium text-gray-900">Total Amount:</span>
                        <span id="total-amount" class="font-semibold">$0.00</span>
                    </div>
                </div>
                
                <input type="hidden" name="payment_method_id" id="payment_method_id">
                
                <div class="flex justify-end">
                    <button type="button" id="cancel-button" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" id="submit-button" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Process Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">About Deposits</h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <ul class="list-disc pl-5 space-y-1">
                    <li>Funds are typically available in your account immediately after successful payment.</li>
                    <li>For international cards, your bank may charge additional fees.</li>
                    <li>All transactions are securely processed by Stripe.</li>
                    <li>We accept Visa, Mastercard, American Express, and Discover.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Stripe
        const stripe = Stripe('{{ $stripeKey }}');
        const elements = stripe.elements();
        
        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    color: '#32325d',
                    fontFamily: '"Inter", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        
        // Mount the card element
        cardElement.mount('#card-element');
        
        // Handle real-time validation errors from the card element
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Disable submit button to prevent multiple submissions
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            
            // Create payment method and handle any errors
            stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    email: '{{ Auth::user()->email }}',
                    name: '{{ Auth::user()->name }}'
                }
            }).then(function(result) {
                if (result.error) {
                    // Show error in the form
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    
                    // Re-enable the submit button
                    submitButton.disabled = false;
                    submitButton.textContent = 'Process Deposit';
                } else {
                    // Send payment method ID to server
                    document.getElementById('payment_method_id').value = result.paymentMethod.id;
                    form.submit();
                }
            });
        });
        
        // Update currency symbols when wallet is changed
        const walletSelect = document.getElementById('wallet_id');
        const currencySymbol = document.getElementById('currency-symbol');
        const currencyAddon = document.getElementById('currency-addon');
        const minDeposit = document.getElementById('min-deposit');
        const processingFee = document.getElementById('processing-fee');
        const totalAmount = document.getElementById('total-amount');
        const amountInput = document.getElementById('amount');
        
        function updateCurrency() {
            const selectedOption = walletSelect.options[walletSelect.selectedIndex];
            const currency = selectedOption.dataset.currency;
            
            // Update currency symbols
            currencyAddon.textContent = currency;
            
            // Update currency symbol based on currency
            let symbol = '$';
            switch(currency) {
                case 'EUR':
                    symbol = '€';
                    break;
                case 'GBP':
                    symbol = '£';
                    break;
                case 'JPY':
                    symbol = '¥';
                    break;
                case 'BRL':
                    symbol = 'R$';
                    break;
                case 'ARS':
                    symbol = 'ARS$';
                    break;
                case 'UYU':
                    symbol = 'UYU$';
                    break;
                default:
                    symbol = '$';
            }
            
            currencySymbol.textContent = symbol;
            minDeposit.textContent = `${symbol}5.00`;
            
            // Update fee and total
            updateFeeAndTotal();
        }
        
        function updateFeeAndTotal() {
            const amount = parseFloat(amountInput.value) || 0;
            const selectedOption = walletSelect.options[walletSelect.selectedIndex];
            const currency = selectedOption.dataset.currency;
            
            // Calculate fee (example: 2.9% + $0.30)
            let fee = (amount * 0.029) + 0.30;
            fee = Math.max(fee, 0);
            
            // Calculate total
            const total = amount + fee;
            
            // Format with currency symbol
            let symbol = '$';
            switch(currency) {
                case 'EUR':
                    symbol = '€';
                    break;
                case 'GBP':
                    symbol = '£';
                    break;
                case 'JPY':
                    symbol = '¥';
                    break;
                case 'BRL':
                    symbol = 'R$';
                    break;
                case 'ARS':
                    symbol = 'ARS$';
                    break;
                case 'UYU':
                    symbol = 'UYU$';
                    break;
                default:
                    symbol = '$';
            }
            
            processingFee.textContent = `${symbol}${fee.toFixed(2)}`;
            totalAmount.textContent = `${symbol}${total.toFixed(2)}`;
        }
        
        walletSelect.addEventListener('change', updateCurrency);
        amountInput.addEventListener('input', updateFeeAndTotal);
        
        // Cancel button
        document.getElementById('cancel-button').addEventListener('click', function() {
            window.history.back();
        });
        
        // Initialize currency display
        updateCurrency();
    });
</script>
@endpush