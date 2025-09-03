<?php

namespace PayPal\Checkout\Environment;

class StripeProductionEnvironment extends StripeEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.stripe.com';
    }

    public function name(): string
    {
        return 'production';
    }
}