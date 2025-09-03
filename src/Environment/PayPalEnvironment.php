<?php

namespace PayPal\Checkout\Environment;

use PayPal\Checkout\Contracts\PaymentEnvironment;
use PayPal\Http\Environment\Environment as BaseEnvironment;

abstract class PayPalEnvironment implements PaymentEnvironment, BaseEnvironment
{
    protected string $clientId;
    
    protected string $clientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function basicAuthorizationString(): string
    {
        return base64_encode($this->clientId.':'.$this->clientSecret);
    }
    
    public function getCredentials(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];
    }
}