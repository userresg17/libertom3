<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->onDelete('set null'); // Pode ser nulo se não afetar carteira fiat direta
            $table->foreignId('related_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->string('type')->index(); // Tipo da transação
            $table->string('status')->default('pending')->index(); // Status
            $table->string('currency', 3)->nullable(); // Moeda principal (fiat)
            $table->decimal('amount', 18, 8)->nullable(); // Valor (+/-) na moeda principal
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('set null'); // Para investimentos
            $table->decimal('quantity', 18, 8)->nullable(); // Quantidade do ativo
            $table->decimal('price_per_unit', 18, 8)->nullable(); // Preço unitário do ativo
            $table->string('fee_currency', 3)->nullable(); // Moeda da taxa
            $table->decimal('fee_amount', 18, 8)->nullable(); // Valor da taxa
            $table->string('description')->nullable(); // Descrição
            $table->json('metadata')->nullable(); // Detalhes extras (recipient, hash, etc)
            $table->decimal('balance_before', 18, 8)->nullable(); // Saldo antes (informativo)
            $table->decimal('balance_after', 18, 8)->nullable(); // Saldo depois (informativo)
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};