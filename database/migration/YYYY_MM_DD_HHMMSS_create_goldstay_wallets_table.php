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
        Schema::create('goldstay_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); // Um usuário tem UMA carteira GoldStay
            $table->string('polygon_address')->unique(); // Endereço único na Polygon
            $table->decimal('balance', 28, 18)->default(0.000000000000000000); // Precisão alta (18 casas decimais é comum para ERC20)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goldstay_wallets');
    }
};