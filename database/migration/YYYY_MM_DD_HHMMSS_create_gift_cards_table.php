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
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('currency', 3);
            $table->decimal('initial_value', 18, 8);
            $table->decimal('balance', 18, 8);
            $table->enum('status', ['active', 'redeemed', 'expired', 'cancelled'])->default('active')->index();
            $table->foreignId('purchased_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('redeemed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('recipient_email')->nullable()->index();
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};