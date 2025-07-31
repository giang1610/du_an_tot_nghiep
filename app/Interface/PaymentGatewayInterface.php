<?php
namespace App\Interfaces;

use Request;

interface PaymentGatewayInterface
{
    public function processPayment($order, $amount);
    public function handleWebhook(Request $request);
    public function handleReturn(Request $request);
}
