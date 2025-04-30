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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary(); // Nome da configuração como chave primária
            $table->longText('value')->nullable(); // Valor (longText para acomodar JSON/objetos serializados)
            $table->boolean('serialized')->default(false); // Indica se o valor está serializado
            // Nesses casos, timestamps geralmente não são necessários
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};