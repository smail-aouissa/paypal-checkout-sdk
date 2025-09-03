<?php

namespace Tests\Adapters;

use PayPal\Checkout\Adapters\StripeAdapter;
use PayPal\Checkout\Environment\StripeSandboxEnvironment;

it('adapter implements payment provider interface', function () {
    $environment = new StripeSandboxEnvironment('sk_test_123', 'pk_test_123');
    $adapter = new StripeAdapter($environment);
    
    expect($adapter)->toBeInstanceOf(\PayPal\Checkout\Contracts\PaymentProvider::class);
});