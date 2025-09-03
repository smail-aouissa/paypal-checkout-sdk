<?php

namespace PayPal\Checkout\Enums;

class PaymentDriver
{
    const PAYPAL = 'paypal';
    const STRIPE = 'stripe';
    
    public static function all(): array
    {
        return [
            self::PAYPAL,
            self::STRIPE,
        ];
    }
    
    public static function isValid(string $driver): bool
    {
        return in_array($driver, self::all());
    }
}