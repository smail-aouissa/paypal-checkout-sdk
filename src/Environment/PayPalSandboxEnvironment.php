<?php

namespace PayPal\Checkout\Environment;

class PayPalSandboxEnvironment extends PayPalEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.sandbox.paypal.com';
    }

    public function name(): string
    {
        return 'sandbox';
    }
}