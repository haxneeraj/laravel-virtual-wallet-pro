<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\TransactionTypeEnum;

return new class () extends Migration {
    
    /**
     * Run the migrations to create the 'crypto_transactions' table.
     *
     * This migration creates a table to store cryptocurrency transactions, with
     * fields for tracking transaction information such as txid (transaction ID),
     * wallet addresses, currency prices, and transaction types/methods.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('laravel-virtual-wallet.tables.crypto_transactions'), function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign key to the 'transactions' table
            // Links this crypto transaction to a general wallet transaction
            $table->foreignId('wallet_transaction_id')->constrained(config('laravel-virtual-wallet.tables.transactions'))->cascadeOnDelete();

            // Transaction ID for the cryptocurrency transaction (must be unique)
            $table->string('txid', 255)->unique();

            // The address of the crypto wallet involved in the transaction
            $table->string('crypto_wallet_address', 255);

            // The crypto wallet address from which the funds were sent (optional)
            $table->string('crypto_wallet_address_from', 255)->nullable();

            // Current price of the cryptocurrency in USD at the time of the transaction
            $table->decimal('current_currency_price_usd', 16, 2)->default(0);

            // The currency involved in the transaction (e.g., BTC, ETH)
            $table->string('currency');

            // Type of the transaction (e.g., Withdraw or Deposit)
            // The values are defined in the TransactionTypeEnum class
            $table->enum('transaction_type', TransactionTypeEnum::values());

            // Timestamps for when the transaction was created/updated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations to drop the 'crypto_transactions' table.
     * 
     * This method drops the 'crypto_transactions' table if it exists.
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('laravel-virtual-wallet.tables.crypto_transactions'));
    }
};
