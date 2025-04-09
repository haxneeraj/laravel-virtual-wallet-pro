<?php

namespace Haxneeraj\LaravelVirtualWalletPro\Traits;

use Haxneeraj\LaravelVirtualWalletPro\Exceptions\InsufficientBalanceException;
use Haxneeraj\LaravelVirtualWalletPro\Exceptions\InvalidWalletException;
use Illuminate\Support\Facades\DB;
use Haxneeraj\LaravelVirtualWalletPro\DataObjects\PaymentData;
use Haxneeraj\LaravelVirtualWalletPro\Models\WalletTransaction;
use App\Enums\WalletTypeEnum;
use App\Enums\WalletStatusEnums;
use App\Enums\CurrencyTypeEnum;
use App\Enums\TransactionTypeEnum;

/**
 * Trait PayTrait
 *
 * Provides methods for interacting with the Wallet model, including querying by various criteria and calculating balances.
 * 
 * @package haxneeraj\laravel-virtual-wallet-pro
 * @author Neeraj Saini
 * @email hax-neeraj@outlook.com
 * @github https://github.com/haxneeraj/
 * @linkedin https://www.linkedin.com/in/hax-neeraj/
 * @version 1.0.0
 * @license MIT
 */
trait PayTrait
{
    /**
     * Processes the payment by deducting the amount from the wallet or wallets
     * and creating the necessary transaction records.
     *
     * @param PaymentData $paymentData
     * @return void
     */
    public function pay(PaymentData $paymentData): void
    {        
        DB::transaction(function () use ($paymentData) {
            // Single wallet case
            if(!empty($paymentData->wallet_type)){
                if (!in_array($paymentData->wallet_type, WalletTypeEnum::values())){                  
                    throw new InvalidWalletException(); // Throws exception if invalid wallet type 
                }

                $wallet = $this->wallets()->where('wallet_type', $paymentData->wallet_type)->first();

                if (!$wallet){
                    throw new InvalidWalletException(); // Throws exception if wallet not found
                }

                if($wallet->status->value != WalletStatusEnums::ACTIVE->value){
                    throw new \Exception("Wallet is not active"); 
                }

                if (!$this->hasSufficientBalanceByWalletType($paymentData->total, $paymentData->wallet_type)){
                    throw new InsufficientBalanceException(); // Throws exception if insufficient balance 
                }
                
                $wallet->balance -= $paymentData->total;
                $wallet->save(); // Save wallet after balance deduction 

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
                        'transaction_type'           => TransactionTypeEnum::WITHDRAW,
                    ]); // Create crypto transaction if payment is in cryptocurrency 
                }
            
                
            }   
            // Multiple wallets case   
            else{
                if (!$this->hasSufficientBalance($paymentData->amount)){
                    throw new InsufficientBalanceException(); // Throws exception if insufficient balance 
                }

                $wallets = $this->wallets;
                $remainAmount = $paymentData->total;

                foreach ($wallets as $wallet){
                    if (!$wallet->hasBalance() || $wallet->status != WalletStatusEnums::ACTIVE){
                        continue;
                    }

                    $amountToDeduct = min($wallet->balance, $remainAmount);
                    $wallet->balance -= $amountToDeduct;
                    $wallet->save(); // Deduct from multiple wallets if needed 

                    $wallet_transaction = WalletTransaction::create([
                        'owner_type'          => $paymentData->owner_type,
                        'owner_id'            => $paymentData->owner_id,
                        'owner_from_type'     => $paymentData->owner_from_type,
                        'owner_from_id'       => $paymentData->owner_from_id,
                        'txid'                => $paymentData->txid,
                        'amount'              => $amountToDeduct,
                        'platform_fee'        => $paymentData->platform_fee,
                        'total'               => $paymentData->total,
                        'description'         => $paymentData->description,
                        'wallet_type'         => $paymentData->wallet_type,
                        'from_wallet_type'    => $paymentData->from_wallet_type,
                        'currency'            => $wallet->currency,
                        'currency_type'       => $wallet->currency_type,
                        'transaction_type'    => $paymentData->transaction_type,
                        'transaction_method'  => $paymentData->method,
                        'profit_id'       => $paymentData->profit_id,
                        'recall_transaction_id'       => $paymentData->recall_transaction_id,
                        'status'              => $paymentData->status,
                    ]); // Create wallet transaction for each wallet 

                    if ($paymentData->currency_type == CurrencyTypeEnum::CRYPTOCURRENCY){
                        $wallet_transaction->cryptoTransaction()->create([
                            'txid'                       => $paymentData->txid,
                            'crypto_wallet_address'      => $paymentData->crypto_wallet_address,
                            'crypto_wallet_address_from' => $paymentData->crypto_wallet_address_from,
                            'current_currency_price_usd' => $paymentData->current_currency_price_usd,
                            'currency'                   => $paymentData->currency,
                            'transaction_type'           => TransactionTypeEnum::WITHDRAW,
                        ]); // Create crypto transaction for each wallet 
                    }

                    $remainAmount -= $amountToDeduct;
                    if ($remainAmount <= 0){
                        break;
                    }
                }

                if ($remainAmount > 0){
                    throw new InsufficientBalanceException(); // Throws exception if not enough balance in all wallets 
                }
            }
        });
    }

    public function subtractBalance(PaymentData $paymentData): void
    {        
        DB::transaction(function () use ($paymentData) {
            // Single wallet case
            if ($paymentData->wallet_type == '' || !in_array($paymentData->wallet_type, WalletTypeEnum::values())):
                throw new InvalidWalletException();
            endif;

            $wallet = $this->wallets()->where('wallet_type', $paymentData->wallet_type)->first();

            if (!$wallet):
                throw new InvalidWalletException(); // Throws exception if wallet not found
            endif;

            if($wallet->status->value != WalletStatusEnums::ACTIVE->value):
                throw new \Exception("Wallet is not active"); 
            endif;
            
            $wallet->balance -= $paymentData->total;
            $wallet->save(); // Save wallet after balance deduction 

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

            if ($paymentData->currency_type == CurrencyTypeEnum::CRYPTOCURRENCY):
                $wallet_transaction->cryptoTransaction()->create([
                    'txid'                       => $paymentData->txid,
                    'crypto_wallet_address'      => $paymentData->crypto_wallet_address,
                    'crypto_wallet_address_from' => $paymentData->crypto_wallet_address_from,
                    'current_currency_price_usd' => $paymentData->current_currency_price_usd,
                    'currency'                   => $paymentData->currency,
                    'transaction_type'           => TransactionTypeEnum::WITHDRAW,
                ]); // Create crypto transaction if payment is in cryptocurrency 
            endif;
        });
    }
}
