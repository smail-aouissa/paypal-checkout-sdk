<?php

namespace PayPal\Checkout\Requests;

use GuzzleHttp\Psr7\Utils;
use PayPal\Checkout\Refunds\RefundRequest;
use PayPal\Http\PaypalRequest;

class OrderRefundRequest extends PaypalRequest
{
    public function __construct(string $captureId, RefundRequest $refundRequest)
    {
        $headers = [
            'Prefer' => 'return=representation',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $uri = str_replace(':capture_id', urlencode($captureId), '/v2/payments/captures/:capture_id/refund');
        $body = Utils::streamFor($refundRequest->toJson());

        parent::__construct('POST', $uri, $headers, $body);
    }
}
