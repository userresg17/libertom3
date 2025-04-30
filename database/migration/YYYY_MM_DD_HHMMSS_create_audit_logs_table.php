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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Usar morphs para permitir diferentes tipos de causadores (Admin, Sistema, etc.)
            $table->nullableMorphs('causer'); // Cria causer_id (unsignedBigInt) e causer_type (string)
            $table->string('action')->index(); // Ação realizada
            // Usar morphs para permitir diferentes tipos de sujeitos (User, Asset, etc.)
            $table->nullableMorphs('subject'); // Cria subject_id (unsignedBigInt) e subject_type (string)
            $table->json('properties')->nullable(); // Dados antigos/novos, detalhes extras
            $table->ipAddress('ip_address')->nullable(); // IP do causador
            $table->timestamps(); // created_at registrará quando a ação ocorreu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};