<?php

namespace PayPal\Checkout\Requests;

use PayPal\Http\PaypalRequest;

class CancelAuthorizeRequest  extends PaypalRequest
{
    public function __construct(string $authorization_id)
    {
        $headers = [
            'Prefer' => 'return=representation',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $uri = str_replace(':authorization_id', urlencode($authorization_id), '/v2/payments/authorizations/:authorization_id/void');
        parent::__construct('POST', $uri, $headers);
    }
}
