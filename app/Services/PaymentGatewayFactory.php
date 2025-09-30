<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Services\Gateways\MidtransGatewayService;

// use App\Services\Gateways\XenditService; // kalau ada
// use App\Services\Gateways\DokuService;   // kalau ada

class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'MIDTRANS' => app(MidtransGatewayService::class),
            // 'xendit'   => app(XenditService::class),
            // 'doku'     => app(DokuService::class),
            default    => throw new \InvalidArgumentException("Unsupported gateway: {$gateway}"),
        };
    }
}