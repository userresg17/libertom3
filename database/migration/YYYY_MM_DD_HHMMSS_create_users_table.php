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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('transaction_password')->nullable(); // Senha de transação
            $table->enum('status', ['active', 'blocked', 'pending_kyc'])->default('pending_kyc');
            $table->enum('kyc_status', ['pending', 'approved', 'rejected', 'resubmission_requested'])->nullable()->index();
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('preferred_currency', 3)->default('USD'); // Moeda preferida para exibição
            $table->timestamp('last_login_at')->nullable();
            $table->ipAddress('last_login_ip')->nullable();
            // Adicionar colunas para telefone, endereço, data de nascimento etc. se necessário
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Se precisar de exclusão lógica
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};