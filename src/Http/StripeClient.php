<?php

namespace PayPal\Checkout\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use PayPal\Checkout\Environment\StripeEnvironment;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class StripeClient
{
    protected StripeEnvironment $environment;
    
    protected Client $client;

    public function __construct(StripeEnvironment $environment)
    {
        $this->environment = $environment;
        $this->client = new Client([
            'base_uri' => $environment->baseUrl(),
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    public function send(RequestInterface $request): ResponseInterface
    {
        $request = $this->injectAuthorizationHeaders($request);
        $request = $this->injectUserAgentHeaders($request);
        $request = $this->injectStripeHeaders($request);
        
        return $this->client->send($request);
    }

    protected function injectAuthorizationHeaders(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->environment->getSecretKey());
    }

    protected function injectUserAgentHeaders(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('User-Agent', 'PayPalCheckoutSDK-PHP (Stripe)');
    }

    protected function injectStripeHeaders(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Stripe-Version', '2023-10-16');
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}