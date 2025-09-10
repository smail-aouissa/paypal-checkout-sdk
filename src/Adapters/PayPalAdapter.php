<?php

namespace PayPal\Checkout\Adapters;

use PayPal\Checkout\Contracts\PaymentProvider;
use PayPal\Checkout\Environment\PayPalEnvironment;
use PayPal\Checkout\Http\PayPalClient;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Refunds\RefundRequest;
use PayPal\Checkout\Requests\OrderAuthorizeRequest;
use PayPal\Checkout\Requests\CaptureAuthorizeRequest;
use PayPal\Checkout\Requests\OrderCaptureRequest;
use PayPal\Checkout\Requests\CancelAuthorizeRequest;
use PayPal\Checkout\Requests\OrderCreateRequest;
use PayPal\Checkout\Requests\OrderShowRequest;
use PayPal\Checkout\Requests\PayPalRefundRequest;
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
        $request = new OrderCreateRequest($order);

        return $this->client->send($request);
    }

    public function showOrder(string $orderId): ResponseInterface
    {
        $request = new OrderShowRequest($orderId);

        return $this->client->send($request);
    }

    public function showRefund(string $orderId): ResponseInterface
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

    public function captureAuthorizeOrder(string $authorizeId): ResponseInterface
    {
        $request = new CaptureAuthorizeRequest($authorizeId);

        return $this->client->send($request);
    }

    public function cancelAuthorizeOrder(string $authorizeId)
    {
        $request = new CancelAuthorizeRequest($authorizeId);

        return $this->client->send($request);
    }


    public function refundPayment(string $paymentId, RefundRequest $refundRequest): ResponseInterface
    {
        $request = new PayPalRefundRequest($paymentId, $refundRequest);

        return $this->client->send($request);
    }
}
