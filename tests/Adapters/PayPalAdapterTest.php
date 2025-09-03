<?php

namespace Tests\Adapters;

use PayPal\Checkout\Adapters\PayPalAdapter;
use PayPal\Checkout\Environment\PayPalSandboxEnvironment;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Orders\AmountBreakdown;
use PayPal\Checkout\Orders\Amount;

it('adapter implements payment provider interface', function () {
    $environment = new PayPalSandboxEnvironment('test_client_id', 'test_client_secret');
    $adapter = new PayPalAdapter($environment);
    
    expect($adapter)->toBeInstanceOf(\PayPal\Checkout\Contracts\PaymentProvider::class);
});

it('can create order structure', function () {
    $order = new Order('CAPTURE');
    
    $amountBreakdown = new AmountBreakdown('100.00', 'USD');
    $purchaseUnit = new PurchaseUnit($amountBreakdown);
    
    $order->addPurchaseUnit($purchaseUnit);
    
    expect($order->getIntent())->toBe('CAPTURE');
    expect($order->getPurchaseUnits())->toHaveCount(1);
});