<?php

namespace Haxneeraj\LaravelVirtualWalletPro\Traits;

use Haxneeraj\LaravelVirtualWalletPro\Exceptions\InvalidWalletException;
use Haxneeraj\LaravelVirtualWalletPro\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Haxneeraj\LaravelVirtualWalletPro\DataObjects\PaymentData;
use Haxneeraj\LaravelVirtualWalletPro\Models\WalletTransaction;
use App\Enums\WalletTypeEnum;
use App\Enums\CurrencyTypeEnum;
use App\Enums\TransactionTypeEnum;

/**
 * Trait DepositTrait
 *
 * Provides methods for depositing funds into the Wallet model,
 * including handling various wallet types and creating transaction records.
 * 
 * @package haxneeraj\laravel-virtual-wallet-pro
 * @author Neeraj Saini
 * @email hax-neeraj@outlook.com
 * @github https://github.com/haxneeraj/
 * @linkedin https://www.linkedin.com/in/hax-neeraj/
 * @version 1.0.0
 * @license MIT
 */
trait DepositTrait
{
    /**
     * Deposits the specified amount into the wallet or wallets
     * and creates the necessary transaction records.
     *
     * @param PaymentData $paymentData
     * @return void
     */
    public function deposit(PaymentData $paymentData): void
    {
        DB::transaction(function () use ($paymentData) {
            // Single wallet case
            if ($paymentData->wallet_type != ''){
                if (!in_array($paymentData->wallet_type, WalletTypeEnum::values())){
                    throw new InvalidWalletException(); // Throws exception if invalid wallet type 
                }

                
                $wallet = Wallet::where(['wallet_type' => $paymentData->wallet_type, 'owner_id' => $paymentData->owner_id])->first();

                // If status is "approved" then only increment balance
                if($paymentData?->status == 'approved')
                {
                    $wallet->balance += $paymentData->amount;
                    $wallet->save(); // Save wallet after balance increment
                }
                
                $wallet_transaction = WalletTransaction::create([
                    'owner_type'      => $paymentData->owner_type,
                    'owner_id'        => $paymentData->owner_id,
                    'owner_from_type' => $paymentData->owner_from_type,
                    'owner_from_id'   => $paymentData->owner_from_id,
                    'txid'            => $paymentData->txid,
                    'amount'          => $paymentData->amount,
                    'platform_fee'    => $paymentData->platform_fee,
                    'total'           => $paymentData->total,
                    'description'     => $paymentData->description,
                    'wallet_type'     => $paymentData->wallet_type,
                    'from_wallet_type'=> $paymentData->from_wallet_type,
                    'currency'        => $wallet->currency,
                    'currency_type'   => $wallet->currency_type,
                    'transaction_method' => $paymentData->method,
                    'transaction_type' => $paymentData->transaction_type,
                    'status'          => $paymentData->status,
                ]); // Create wallet transaction

                if ($paymentData->currency_type == CurrencyTypeEnum::CRYPTOCURRENCY){
                    $wallet_transaction->cryptoTransaction()->create([
                        'txid'                       => $paymentData->txid,
                        'crypto_wallet_address'      => $paymentData->crypto_wallet_address,
                        'crypto_wallet_address_from' => $paymentData->crypto_wallet_address_from,
                        'current_currency_price_usd' => $paymentData->current_currency_price_usd,
                        'currency'                   => $paymentData->currency,
                        'transaction_type'           => TransactionTypeEnum::DEPOSIT,
                    ]); // Create crypto transaction if deposit is in cryptocurrency
                }
            
            // Multiple wallets case
            }
            else{

                $wallets = Wallet::where(['owner_id' => $paymentData->owner_id])->get();
                $remainAmount = $paymentData->amount;

                foreach ($wallets as $wallet){
                    $amountToAdd = min($remainAmount, $wallet->maxCapacity() - $wallet->balance); // Assuming `maxCapacity` is a defined method

                    if($paymentData?->status == 'approved')
                    {
                        $wallet->balance += $amountToAdd;
                        $wallet->save();
                    }

                    $wallet_transaction = WalletTransaction::create([
                        'owner_type'      => $paymentData->owner_type,
                        'owner_id'        => $paymentData->owner_id,
                        'owner_from_type' => $paymentData->owner_from_type,
                        'owner_from_id'   => $paymentData->owner_from_id,
                        'txid'            => $paymentData->txid,
                        'amount'          => $amountToAdd,
                        'platform_fee'    => $paymentData->platform_fee,
                        'total'           => $paymentData->total,
                        'description'     => $paymentData->description,
                        'wallet_type'     => $paymentData->wallet_type,
                        'from_wallet_type'=> $paymentData->from_wallet_type,
                        'currency'        => $wallet->currency,
                        'currency_type'   => $wallet->currency_type,
                        'transaction_method' => $paymentData->method,
                        'transaction_type' => $paymentData->transaction_type,
                        'status'          => $paymentData->status,
                        'profit_id'       => $paymentData->profit_id,
                        'recall_transaction_id'       => $paymentData->recall_transaction_id,
                    ]); // Create wallet transaction for each wallet

                    if ($paymentData->currency_type == CurrencyTypeEnum::CRYPTOCURRENCY){
                        $wallet_transaction->cryptoTransaction()->create([
                            'txid'                       => $paymentData->txid,
                            'crypto_wallet_address'      => $paymentData->crypto_wallet_address,
                            'crypto_wallet_address_from' => $paymentData->crypto_wallet_address_from,
                            'current_currency_price_usd' => $paymentData->current_currency_price_usd,
                            'currency'                   => $paymentData->currency,
                            'transaction_type'           => TransactionTypeEnum::DEPOSIT,
                        ]); // Create crypto transaction for each wallet
                    }

                    $remainAmount -= $amountToAdd;
                    if ($remainAmount <= 0){
                        break;
                    }
                }

                if ($remainAmount > 0){
                    // Log or handle case where not all the amount could be deposited
                }
            }
        });
    }
}
