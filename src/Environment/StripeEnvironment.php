<?php

namespace PayPal\Checkout\Environment;

use PayPal\Checkout\Contracts\PaymentEnvironment;

abstract class StripeEnvironment implements PaymentEnvironment
{
    protected string $secretKey;
    
    protected string $publishableKey;

    public function __construct(string $secretKey, string $publishableKey = '')
    {
        $this->secretKey = $secretKey;
        $this->publishableKey = $publishableKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }
    
    public function getCredentials(): array
    {
        return [
            'secret_key' => $this->secretKey,
            'publishable_key' => $this->publishableKey,
        ];
    }
}