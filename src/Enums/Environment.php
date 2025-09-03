<?php

namespace PayPal\Checkout\Enums;

class Environment
{
    const SANDBOX = 'sandbox';
    const PRODUCTION = 'production';
    
    public static function all(): array
    {
        return [
            self::SANDBOX,
            self::PRODUCTION,
        ];
    }
    
    public static function isValid(string $environment): bool
    {
        return in_array($environment, self::all());
    }
}