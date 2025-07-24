<?php

namespace App\Services\Payment;

use App\Services\Payment\CodPaymentService;
use App\Services\Payment\MomoPaymentService;
use App\Services\Payment\VnpayPaymentService;
use InvalidArgumentException;

class PaymentServiceFactory
{
    public static function create(string $paymentMethod)
    {
        switch (strtolower($paymentMethod)) {
            case 'cod':
                return new CodPaymentService();
            case 'momo':
                return new MomoPaymentService();
            case 'vnpay':
                return new VnpayPaymentService();
            default:
                throw new InvalidArgumentException("Phương thức thanh toán không được hỗ trợ: {$paymentMethod}");
        }
    }
}