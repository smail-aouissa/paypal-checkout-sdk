<?php

namespace PayPal\Checkout\Http;

use PayPal\Checkout\Environment\PayPalEnvironment;
use PayPal\Http\PayPalClient as BasePayPalClient;

class PayPalClient extends BasePayPalClient
{
    public function __construct(PayPalEnvironment $environment)
    {
        parent::__construct($environment);
    }
}