<?php

namespace PayPal\Checkout\Adapters;

use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Environment\PayPalEnvironment;
use PayPal\Checkout\Http\PayPalClient;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Refunds\RefundRequest;
use PayPal\Checkout\Requests\OrderAuthorizeRequest;
use PayPal\Checkout\Requests\OrderCaptureRequest;
use PayPal\Checkout\Requests\OrderCreateRequest;
use PayPal\Checkout\Requests\OrderShowRequest;
use PayPal\Checkout\Requests\OrderRefundRequest;
use Psr\Http\Message\ResponseInterface;

class PayPalAdapter implements PaymentProvider
{
    protected PayPalEnvironment $environment;

    protected PayPalClient $client;

    public function __construct(PayPalEnvironment $environment)
    {
        $this->environment = $environment;
        $this->client = new PayPalClient($environment);
    }

    public function createOrder(Order $order): ResponseInterface
    {
        $request = new OrderCreateRequest();
        $request->body = $order->toArray();

        return $this->client->send($request);
    }

    public function showOrder(string $orderId): ResponseInterface
    {
        $request = new OrderShowRequest($orderId);

        return $this->client->send($request);
    }

    public function captureOrder(string $orderId): ResponseInterface
    {
        $request = new OrderCaptureRequest($orderId);

        return $this->client->send($request);
    }

    public function authorizeOrder(string $orderId): ResponseInterface
    {
        $request = new OrderAuthorizeRequest($orderId);

        return $this->client->send($request);
    }

    public function refundPayment(string $paymentId, RefundRequest $refundRequest): ResponseInterface
    {
        $request = new OrderRefundRequest($paymentId, $refundRequest);

        return $this->client->send($request);
    }
}
