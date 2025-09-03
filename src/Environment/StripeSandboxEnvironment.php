<?php

namespace PayPal\Checkout\Environment;

class StripeSandboxEnvironment extends StripeEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.stripe.com';
    }

    public function name(): string
    {
        return 'sandbox';
    }
}