<?php

namespace PayPal\Checkout\Adapters;

use GuzzleHttp\Psr7\Request;
use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Environment\StripeEnvironment;
use PayPal\Checkout\Http\StripeClient;
use PayPal\Checkout\Orders\Order;
use Psr\Http\Message\ResponseInterface;

class StripeAdapter implements PaymentProvider
{
    protected StripeEnvironment $environment;
    
    protected StripeClient $client;

    public function __construct(StripeEnvironment $environment)
    {
        $this->environment = $environment;
        $this->client = new StripeClient($environment);
    }

    public function createOrder(Order $order): ResponseInterface
    {
        $orderData = $this->transformOrderForStripe($order);
        
        $request = new Request(
            'POST',
            '/v1/payment_intents',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($orderData)
        );
        
        return $this->client->send($request);
    }

    public function showOrder(string $orderId): ResponseInterface
    {
        $request = new Request('GET', "/v1/payment_intents/{$orderId}");
        
        return $this->client->send($request);
    }

    public function captureOrder(string $orderId): ResponseInterface
    {
        $request = new Request(
            'POST',
            "/v1/payment_intents/{$orderId}/capture",
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );
        
        return $this->client->send($request);
    }

    public function authorizeOrder(string $orderId): ResponseInterface
    {
        return $this->showOrder($orderId);
    }

    protected function transformOrderForStripe(Order $order): array
    {
        $purchaseUnits = $order->getPurchaseUnits();
        
        if (empty($purchaseUnits)) {
            throw new \InvalidArgumentException('Order must have at least one purchase unit');
        }
        
        $purchaseUnit = $purchaseUnits[0];
        $amount = $purchaseUnit->getAmount();
        
        $data = [
            'amount' => $this->convertToStripeCents($amount->getValue()),
            'currency' => strtolower($amount->getCurrencyCode()),
            'capture_method' => $order->getIntent() === 'CAPTURE' ? 'automatic' : 'manual',
        ];
        
        $applicationContext = $order->getApplicationContext();
        if ($applicationContext) {
            $data['description'] = 'Payment for order';
        }
        
        return $data;
    }
    
    protected function convertToStripeCents(string $amount): int
    {
        return (int) round(floatval($amount) * 100);
    }
}