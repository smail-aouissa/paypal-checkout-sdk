<?php

namespace PayPal\Checkout\Environment;

class PayPalProductionEnvironment extends PayPalEnvironment
{
    public function baseUrl(): string
    {
        return 'https://api.paypal.com';
    }

    public function name(): string
    {
        return 'production';
    }
}