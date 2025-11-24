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
        // Create wallets table
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Create wallet_cryptos table
        Schema::create('wallet_cryptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->foreignId('cryptocurrency_id')->constrained('cryptos')->onDelete('cascade');
            $table->decimal('quantity', 20, 8)->default(0);
            $table->decimal('avg_buy_price', 15, 2)->default(0);
            $table->unique(['wallet_id', 'cryptocurrency_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_cryptos');
        Schema::dropIfExists('wallets');
    }
};
