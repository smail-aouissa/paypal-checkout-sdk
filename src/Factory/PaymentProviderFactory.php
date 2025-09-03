<?php

namespace PayPal\Checkout\Factory;

use InvalidArgumentException;
use PayPal\Checkout\Adapters\PayPalAdapter;
use PayPal\Checkout\Adapters\StripeAdapter;
use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Enums\PaymentDriver;
use PayPal\Checkout\Environment\PayPalProductionEnvironment;
use PayPal\Checkout\Environment\PayPalSandboxEnvironment;
use PayPal\Checkout\Environment\StripeProductionEnvironment;
use PayPal\Checkout\Environment\StripeSandboxEnvironment;

class PaymentProviderFactory
{
    public static function create(string $driver, string $environment = 'sandbox', array $config = []): PaymentProvider
    {
        if (!PaymentDriver::isValid($driver)) {
            throw new InvalidArgumentException("Unsupported payment driver: {$driver}");
        }

        switch ($driver) {
            case PaymentDriver::PAYPAL:
                return self::createPayPalProvider($environment, $config);
            
            case PaymentDriver::STRIPE:
                return self::createStripeProvider($environment, $config);
            
            default:
                throw new InvalidArgumentException("Unsupported payment driver: {$driver}");
        }
    }

    protected static function createPayPalProvider(string $environment, array $config): PaymentProvider
    {
        $clientId = $config['client_id'] ?? '';
        $clientSecret = $config['client_secret'] ?? '';

        if (empty($clientId) || empty($clientSecret)) {
            throw new InvalidArgumentException('PayPal requires client_id and client_secret');
        }

        switch ($environment) {
            case 'sandbox':
                $env = new PayPalSandboxEnvironment($clientId, $clientSecret);
                break;
            
            case 'production':
                $env = new PayPalProductionEnvironment($clientId, $clientSecret);
                break;
            
            default:
                throw new InvalidArgumentException("Unsupported environment: {$environment}");
        }

        return new PayPalAdapter($env);
    }

    protected static function createStripeProvider(string $environment, array $config): PaymentProvider
    {
        $secretKey = $config['secret_key'] ?? '';
        $publishableKey = $config['publishable_key'] ?? '';

        if (empty($secretKey)) {
            throw new InvalidArgumentException('Stripe requires secret_key');
        }

        switch ($environment) {
            case 'sandbox':
                $env = new StripeSandboxEnvironment($secretKey, $publishableKey);
                break;
            
            case 'production':
                $env = new StripeProductionEnvironment($secretKey, $publishableKey);
                break;
            
            default:
                throw new InvalidArgumentException("Unsupported environment: {$environment}");
        }

        return new StripeAdapter($env);
    }
}