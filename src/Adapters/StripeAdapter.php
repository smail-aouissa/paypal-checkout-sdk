<?php

namespace PayPal\Checkout\Adapters;

use GuzzleHttp\Psr7\Request;
use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Environment\StripeEnvironment;
use PayPal\Checkout\Http\StripeClient;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Refunds\RefundRequest;
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

    public function showRefund(string $orderId): ResponseInterface
    {
        $params = http_build_query([
            'payment_intent' => $orderId,
        ]);

        $request = new Request('GET', "/v1/charges?{$params}");

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

    public function captureAuthorizeOrder(string $orderId): ResponseInterface
    {
        $request = new Request(
            'POST',
            "/v1/payment_intents/{$orderId}/capture",
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        return $this->client->send($request);
    }


     public function authorizeOrder(string $orderId, ?array $params=[]): ResponseInterface
    {
        $body = http_build_query([
            'payment_method' => $params['payment_method'],
            'return_url' => $params['return_url'],
        ]);

        $request = new Request(
            'POST',
            "/v1/payment_intents/{$orderId}/confirm",
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            $body
        );

        return $this->client->send($request);
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

    public function refundPayment(string $paymentId, RefundRequest $refundRequest): ResponseInterface
    {
        $refundData = $this->transformRefundForStripe($paymentId, $refundRequest);
        
        $request = new Request(
            'POST',
            '/v1/refunds',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query($refundData)
        );
        
        return $this->client->send($request);
    }

    protected function transformRefundForStripe(string $paymentId, RefundRequest $refundRequest): array
    {
        $data = [
            'payment_intent' => $paymentId,
        ];

        $amount = $refundRequest->getAmount();
        if ($amount) {
            $data['amount'] = $this->convertToStripeCents($amount->getValue());
        }

        $reason = $refundRequest->getReason();
        if ($reason) {
            $data['reason'] = $reason;
        }

        $metadata = [];
        if ($refundRequest->getInvoiceId()) {
            $metadata['invoice_id'] = $refundRequest->getInvoiceId();
        }
        if ($refundRequest->getNoteToPayer()) {
            $metadata['note_to_payer'] = $refundRequest->getNoteToPayer();
        }
        
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $data["metadata[{$key}]"] = $value;
            }
        }

        return $data;
    }

    public function cancelAuthorizeOrder($orderId)
    {
        $request = new Request(
            'POST',
            "/v1/payment_intents/{$orderId}/cancel",
            ['Content-Type' => 'application/x-www-form-urlencoded'],
        );

        return $this->client->send($request);
    }
}
