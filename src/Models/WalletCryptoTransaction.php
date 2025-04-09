<?php

namespace Haxneeraj\LaravelVirtualWalletPro\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\TransactionTypeEnum;

class WalletCryptoTransaction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * The table name is dynamically set via the configuration file for
     * the virtual wallet package.
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     * 
     * These fields can be filled via mass assignment using
     * methods like `create()` or `update()`.
     */
    protected $fillable = [
        'wallet_transaction_id',
        'txid',
        'crypto_wallet_address',
        'crypto_wallet_address_from',
        'current_currency_price_usd',
        'currency',
        'transaction_type',
    ];

    /**
     * The attributes that aren't mass assignable.
     * 
     * Preventing 'id' from being overwritten during mass assignment.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     * 
     * The 'transaction_type' field is cast to the TransactionTypeEnum enum class.
     */
    protected $casts = [
        'transaction_type' => TransactionTypeEnum::class,  // Casting transaction_type to TransactionTypeEnum
    ];

    /**
     * Class constructor.
     * 
     * Set the table name dynamically from the configuration file.
     * The config file 'laravel-virtual-wallet' contains the table names, and here
     * we're setting the table to the value specified for 'crypto_transactions'.
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('laravel-virtual-wallet.tables.crypto_transactions');

        // Call the parent constructor
        parent::__construct($attributes);
    }

    /**
     * Define a belongs-to relationship with the WalletTransaction model.
     * 
     * This method establishes the relationship between a crypto transaction
     * and its associated wallet transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}
