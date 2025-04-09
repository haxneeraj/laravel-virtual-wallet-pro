<?php

namespace Haxneeraj\LaravelVirtualWalletPro\DataObjects;

class PaymentData
{
    public $owner_type;
    public $owner_id;
    public $owner_from_type = null;
    public $owner_from_id = null;
    public $txid;
    public $amount;
    public $platform_fee = 0;
    public $total = 0;
    public $description = null;
    public $wallet_type;
    public $from_wallet_type = null;
    public $method;
    public $status;
    public $currency;
    public $currency_type;
    public $crypto_wallet_address;
    public $crypto_wallet_address_from;
    public $current_currency_price_usd;
    public $transaction_type;

    // Constructor that accepts an optional array of properties
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    // Setters for each property using fluent interface (method chaining)
    public function setOwnerType($owner_type): self
    {
        $this->owner_type = $owner_type;
        return $this;
    }

    public function setOwnerId($owner_id): self
    {
        $this->owner_id = $owner_id;
        return $this;
    }

    public function setOwnerFromType($owner_from_type): self
    {
        $this->owner_from_type = $owner_from_type;
        return $this;
    }

    public function setOwnerFromId($owner_from_id): self
    {
        $this->owner_from_id = $owner_from_id;
        return $this;
    }

    public function setTxid($txid): self
    {
        $this->txid = $txid;
        return $this;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setPlatformFee($platform_fee): self
    {
        $this->platform_fee = $platform_fee;
        return $this;
    }

    public function calculateTotal(): self
    {
        $this->total = $this->amount + $this->platform_fee;
        return $this;
    }

    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setWalletType($wallet_type): self
    {
        $this->wallet_type = $wallet_type;
        return $this;
    }

    public function setFromWalletType($from_wallet_type): self
    {
        $this->from_wallet_type = $from_wallet_type;
        return $this;
    }


    public function setMethod($method): self
    {
        $this->method = $method;
        return $this;
    }

    public function setStatus($status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setCurrency($currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setCurrencyType($currency_type): self
    {
        $this->currency_type = $currency_type;
        return $this;
    }
    

    public function setCryptoWalletAddress($crypto_wallet_address): self
    {
        $this->crypto_wallet_address = $crypto_wallet_address;
        return $this;
    }

    public function setCryptoWalletAddressFrom($crypto_wallet_address_from): self
    {
        $this->crypto_wallet_address_from = $crypto_wallet_address_from;
        return $this;
    }

    public function setCurrentCurrencyPriceUsd($current_currency_price_usd): self
    {
        $this->current_currency_price_usd = $current_currency_price_usd;
        return $this;
    }

    public function setTransactionType($transaction_type): self
    {
        $this->transaction_type = $transaction_type;
        return $this;
    }

    // Method to set all properties from an associative array
    public function setData(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }
}
