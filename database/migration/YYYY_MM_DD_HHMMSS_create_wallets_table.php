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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('currency', 3)->index(); // 'USD', 'BRL', etc.
            $table->decimal('balance', 18, 8)->default(0.00000000); // Alta precisão
            $table->string('iban')->nullable()->unique(); // IBAN único por carteira? Ou por usuário? Ajustar conforme regra.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Garante que um usuário só tenha uma carteira por moeda
            $table->unique(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};