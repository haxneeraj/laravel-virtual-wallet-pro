<?php 

namespace Haxneeraj\LaravelVirtualWalletPro\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

use Haxneeraj\LaravelVirtualWalletPro\Traits\ModelTrait;
use Haxneeraj\LaravelVirtualWalletPro\Traits\VirtualWalletTrait;
use Haxneeraj\LaravelVirtualWalletPro\Traits\WalletTransactionTrait;
use Haxneeraj\LaravelVirtualWalletPro\Traits\PayTrait;
use Haxneeraj\LaravelVirtualWalletPro\Traits\DepositTrait;
use Haxneeraj\LaravelVirtualWalletPro\Traits\InternalTransferTrait;

trait HasVirtualWallet
{
    use ModelTrait;
    use VirtualWalletTrait;
    use WalletTransactionTrait;
    use PayTrait;    
    use DepositTrait;
    use InternalTransferTrait;
}