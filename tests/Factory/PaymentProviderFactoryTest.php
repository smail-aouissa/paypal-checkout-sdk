<?php

namespace Tests\Factory;

use InvalidArgumentException;
use PayPal\Checkout\Adapters\PayPalAdapter;
use PayPal\Checkout\Adapters\StripeAdapter;
use PayPal\Checkout\Factory\PaymentProviderFactory;

it('creates paypal provider for sandbox', function () {
    $provider = PaymentProviderFactory::create('paypal', 'sandbox', [
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
    ]);

    expect($provider)->toBeInstanceOf(PayPalAdapter::class);
});

it('creates stripe provider for sandbox', function () {
    $provider = PaymentProviderFactory::create('stripe', 'sandbox', [
        'secret_key' => 'sk_test_123',
        'publishable_key' => 'pk_test_123',
    ]);

    expect($provider)->toBeInstanceOf(StripeAdapter::class);
});

it('throws exception for invalid driver', function () {
    expect(function () {
        PaymentProviderFactory::create('invalid', 'sandbox', []);
    })->toThrow(InvalidArgumentException::class, 'Unsupported payment driver: invalid');
});

it('throws exception for missing paypal credentials', function () {
    expect(function () {
        PaymentProviderFactory::create('paypal', 'sandbox', []);
    })->toThrow(InvalidArgumentException::class, 'PayPal requires client_id and client_secret');
});

it('throws exception for missing stripe credentials', function () {
    expect(function () {
        PaymentProviderFactory::create('stripe', 'sandbox', []);
    })->toThrow(InvalidArgumentException::class, 'Stripe requires secret_key');
});