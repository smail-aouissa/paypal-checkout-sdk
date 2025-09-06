<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Orders\Amount;
use PayPal\Checkout\Orders\ApplicationContext;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;

// Configuration
$credentials = [
    'paypal_client_id' => 'xxxxxxxxxxxx',
    'paypal_client_secret' => 'xxxxxxxxxxxx',
];

$environment = 'sandbox';

echo "Corrected PayPal Payment Example\n";
echo "================================\n\n";

try {
    // Create PayPal provider
    $paypalProvider = PaymentProviderFactory::create('paypal', $environment, [
        'client_id' => $credentials['paypal_client_id'],
        'client_secret' => $credentials['paypal_client_secret']
    ]);

    // Create order
    $order = new Order();
    $order->setIntent('CAPTURE');

    // Use simple Amount (without breakdown) for simple payments
    $amount = new Amount('100.00', 'USD');

    // Create purchase unit with simple amount
    $purchaseUnit = new PurchaseUnit();
    $purchaseUnit->setAmount($amount);
    $purchaseUnit->setReferenceId('test-order-' . time());

    $order->addPurchaseUnit($purchaseUnit);

    // Create application context
    $applicationContext = new ApplicationContext();
    $applicationContext->setBrandName('Wechalet Inc');
    $applicationContext->setShippingPreference('NO_SHIPPING');
    $applicationContext->setUserAction('PAY_NOW');
    $applicationContext->setReturnUrl('https://wwww.wechalet.com/payments/paypal/success');
    $applicationContext->setCancelUrl('https://wwww.wechalet.com/payments/paypal/cancel');
    $order->setApplicationContext($applicationContext);

    echo "Order JSON:\n";
    echo json_encode($order->toArray(), JSON_PRETTY_PRINT) . "\n\n";

    // Send request
    echo "Sending request to PayPal...\n";
    $response = $paypalProvider->createOrder($order);
    $responseData = json_decode($response->getBody(), true);

    if ($response->getStatusCode() === 201) {
        echo "✓ PayPal order created successfully!\n";
        echo "  Order ID: " . $responseData['id'] . "\n";
        echo "  Status: " . $responseData['status'] . "\n";

        // Find approval URL for customer
        foreach ($responseData['links'] as $link) {
            if ($link['rel'] === 'approve') {
                echo "  Customer Approval URL: " . $link['href'] . "\n";
                break;
            }
        }
    } else {
        echo "✗ PayPal order creation failed\n";
        echo "  Status: " . $response->getStatusCode() . "\n";
        echo "  Response: " . $response->getBody() . "\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";

    // If it's a Guzzle RequestException, try to get the full response
    if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
        $response = $e->getResponse();
        echo "  Full Response: " . $response->getBody() . "\n";
    }
}
