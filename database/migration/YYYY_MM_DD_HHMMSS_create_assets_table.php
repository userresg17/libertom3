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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // Ticker/Símbolo único
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->index(); // 'stock', 'etf', 'bond'
            $table->string('logo_url')->nullable();
            $table->decimal('current_price', 18, 8)->nullable(); // Preço atual
            $table->decimal('variation_24h', 8, 4)->nullable(); // Variação % em 24h
            $table->decimal('operating_fee', 8, 4)->nullable()->comment('Taxa de Operação/Custódia em %');
            $table->boolean('is_active')->default(true)->index(); // Se o ativo pode ser negociado
            $table->json('rules')->nullable()->comment('Regras de variação automática');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};