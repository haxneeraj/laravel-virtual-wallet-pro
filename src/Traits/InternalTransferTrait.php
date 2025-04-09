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
trait InternalTransferTrait
{
    /**
     * InternalTransferTrait the specified amount into the wallet or wallets
     * and creates the necessary transaction records.
     *
     * @param PaymentData $paymentData
     * @return void
     */
    public function internalTransfer(PaymentData $paymentData): void
    {
        DB::transaction(function () use ($paymentData) {
            // Single wallet case
            if ($paymentData->wallet_type != '' && $paymentData->from_wallet_type != '')
            {
                if (!in_array($paymentData->from_wallet_type, WalletTypeEnum::values()) || !in_array($paymentData->wallet_type, WalletTypeEnum::values())):
                    throw new InvalidWalletException(); // Throws exception if invalid wallet type 
                endif;

                
                $wallet = Wallet::where(['wallet_type' => $paymentData->from_wallet_type, 'owner_id' => $paymentData->owner_id])->first();
                $to_wallet = Wallet::where(['wallet_type' => $paymentData->wallet_type, 'owner_id' => $paymentData->owner_id])->first();
                if(!$wallet && !$to_wallet)
                {
                    throw new InvalidWalletException(); // Throws exception if invalid wallet type
                }

                // If status is "approved" then only increment balance
                $wallet->balance -= $paymentData->total;
                $wallet->save(); // Save wallet after balance increment

                if (($wallet->wallet_type->value != WalletTypeEnum::SWING_TRADING_WALLET->value && $wallet->wallet_type->value != WalletTypeEnum::RISK_WALLET->value))
                {
                    $to_wallet->balance += $paymentData->total;
                    $to_wallet->save(); // Save wallet after balance increment
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
                ]); // Create wallet transaction record
            }
            else
            {
                throw new InvalidWalletException(); // Throws exception if invalid wallet type
            }
        });
    }
}
