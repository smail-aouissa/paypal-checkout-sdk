<?php

namespace PayPal\Checkout\Contracts;

use PayPal\Checkout\Orders\Order;
use Psr\Http\Message\ResponseInterface;

interface PaymentProvider
{
    public function createOrder(Order $order): ResponseInterface;
    
    public function showOrder(string $orderId): ResponseInterface;
    
    public function captureOrder(string $orderId): ResponseInterface;
    
    public function authorizeOrder(string $orderId): ResponseInterface;
}