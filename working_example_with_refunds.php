<?php

require_once 'vendor/autoload.php';

use PayPal\Checkout\Factory\PaymentProviderFactory;
use PayPal\Checkout\Orders\Amount;
use PayPal\Checkout\Orders\ApplicationContext;
use PayPal\Checkout\Orders\Order;
use PayPal\Checkout\Orders\PurchaseUnit;
use PayPal\Checkout\Refunds\RefundRequest;

// Configuration - REPLACE WITH YOUR ACTUAL CREDENTIALS
$credentials = [
    'paypal_client_id' =>  'xxxxxxxxxxxx',
    'paypal_client_secret' =>  'xxxxxxxxxxxx',
    'stripe_secret_key' =>  'xxxxxxxxxxxx',
    'stripe_publishable_key' =>  'xxxxxxxxxxxx',
];

$environment = 'sandbox'; // Use 'production' for live payments

echo "Multi-Provider Payment SDK - Complete Example with Refunds\n";
echo "=========================================================\n\n";

// Example 1: PayPal Payment (Fixed)
echo "Example 1: Creating a PayPal Payment (CORRECTED)\n";
echo "------------------------------------------------\n";

try {
    // Create PayPal provider
    $paypalProvider = PaymentProviderFactory::create('paypal', $environment, [
        'client_id' => $credentials['paypal_client_id'],
        'client_secret' => $credentials['paypal_client_secret']
    ]);

    // Create order (FIXED VERSION)
    $order = new Order();
    $order->setIntent('CAPTURE');

    // Use simple Amount (correct parameter order: value, currency)
    $amount = new Amount('100.00', 'USD');

    // Create purchase unit (UPDATED to work with new constructor)
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

    // Create payment
    echo "Creating PayPal order for $100.00 USD...\n";
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

        // Store order ID for potential refund testing
        $paypalOrderId = $responseData['id'];

        // Get order details
        $detailsResponse = $paypalProvider->showOrder($paypalOrderId);
        $detailsData = json_decode($detailsResponse->getBody(), true);
        echo "  Order Details Retrieved: " . $detailsData['status'] . "\n";

    } else {
        echo "✗ PayPal order creation failed\n";
        echo "  Status: " . $response->getStatusCode() . "\n";
        echo "  Response: " . $response->getBody() . "\n";
    }

} catch (Exception $e) {
    echo "✗ PayPal Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: Stripe Payment
echo "Example 2: Creating a Stripe Payment\n";
echo "------------------------------------\n";

try {
    // Create Stripe provider
    $stripeProvider = PaymentProviderFactory::create('stripe', $environment, [
        'secret_key' => $credentials['stripe_secret_key'],
        'publishable_key' => $credentials['stripe_publishable_key']
    ]);

    // Create order for Stripe
    $stripeOrder = new Order();
    $stripeOrder->setIntent('CAPTURE');

    $stripeAmount = new Amount('50.00', 'USD');
    $stripePurchaseUnit = new PurchaseUnit();
    $stripePurchaseUnit->setAmount($stripeAmount);
    $stripePurchaseUnit->setReferenceId('stripe-order-' . time());

    $stripeOrder->addPurchaseUnit($stripePurchaseUnit);

    // Create payment
    echo "Creating Stripe payment for $50.00 USD...\n";
    $stripeResponse = $stripeProvider->createOrder($stripeOrder);
    $stripeResponseData = json_decode($stripeResponse->getBody(), true);

    if ($stripeResponse->getStatusCode() === 200) {
        echo "✓ Stripe payment intent created successfully!\n";
        echo "  Payment Intent ID: " . $stripeResponseData['id'] . "\n";
        echo "  Status: " . $stripeResponseData['status'] . "\n";
        echo "  Client Secret: " . $stripeResponseData['client_secret'] . "\n";
        echo "  Amount: " . ($stripeResponseData['amount'] / 100) . " " . strtoupper($stripeResponseData['currency']) . "\n";

        // Store payment ID for potential refund testing
        $stripePaymentId = $stripeResponseData['id'];
    } else {
        echo "✗ Stripe payment creation failed\n";
        echo "  Status: " . $stripeResponse->getStatusCode() . "\n";
        echo "  Response: " . $stripeResponse->getBody() . "\n";
    }

} catch (Exception $e) {
    echo "✗ Stripe Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 3: Refund Examples (DEMO - not actual refunds)
echo "Example 3: Refund Functionality Demo\n";
echo "====================================\n";

echo "IMPORTANT: The following refund examples are for demonstration purposes.\n";
echo "To actually test refunds, you would need:\n";
echo "1. For PayPal: A completed payment with a capture ID\n";
echo "2. For Stripe: A successful payment intent that has been captured\n\n";

// PayPal Refund Example
echo "PayPal Refund Example:\n";
echo "---------------------\n";

try {
    // Create a sample refund request
    $refundRequest = new RefundRequest('25.00', 'USD'); // Partial refund
    $refundRequest->setInvoiceId('REFUND-' . time())
                 ->setNoteToPayer('Partial refund as requested')
                 ->setReason('Customer requested partial refund');

    echo "Refund Request Details:\n";
    echo "- Amount: " . $refundRequest->getAmount()->getValue() . " " . $refundRequest->getAmount()->getCurrencyCode() . "\n";
    echo "- Invoice ID: " . $refundRequest->getInvoiceId() . "\n";
    echo "- Note: " . $refundRequest->getNoteToPayer() . "\n";
    echo "- Reason: " . $refundRequest->getReason() . "\n";
    echo "- JSON: " . $refundRequest->toJson() . "\n";

    // To actually process a PayPal refund, you would use:
    echo "\nTo process PayPal refund (requires actual capture ID):\n";
    echo "\$response = \$paypalProvider->refundPayment(\$captureId, \$refundRequest);\n";

} catch (Exception $e) {
    echo "✗ PayPal Refund Demo Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Stripe Refund Example
echo "Stripe Refund Example:\n";
echo "---------------------\n";

try {
    // Create a sample refund request for Stripe
    $stripeRefundRequest = new RefundRequest('15.00', 'USD'); // Partial refund
    $stripeRefundRequest->setInvoiceId('STRIPE-REFUND-' . time())
                       ->setNoteToPayer('Stripe partial refund')
                       ->setReason('requested_by_customer'); // Stripe-specific reason

    echo "Stripe Refund Request Details:\n";
    echo "- Amount: " . $stripeRefundRequest->getAmount()->getValue() . " " . $stripeRefundRequest->getAmount()->getCurrencyCode() . "\n";
    echo "- Invoice ID: " . $stripeRefundRequest->getInvoiceId() . "\n";
    echo "- Note: " . $stripeRefundRequest->getNoteToPayer() . "\n";
    echo "- Reason: " . $stripeRefundRequest->getReason() . "\n";

    // To actually process a Stripe refund, you would use:
    echo "\nTo process Stripe refund (requires actual payment intent ID):\n";
    echo "\$response = \$stripeProvider->refundPayment(\$paymentIntentId, \$stripeRefundRequest);\n";

} catch (Exception $e) {
    echo "✗ Stripe Refund Demo Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "=== Summary ===\n";
echo "\nKey Fixes Applied:\n";
echo "1. ✓ Fixed Amount constructor parameter order (value, currency)\n";
echo "2. ✓ Updated PurchaseUnit to accept both Amount and AmountBreakdown\n";
echo "3. ✓ Added setAmount() and setReferenceId() methods to PurchaseUnit\n";
echo "4. ✓ Fixed OrderCreateRequest to properly serialize Order to JSON\n";
echo "5. ✓ Updated PayPalAdapter to use corrected OrderCreateRequest\n";
echo "6. ✓ Implemented full refund functionality for both PayPal and Stripe\n";

echo "\nRefund Functionality:\n";
echo "- ✓ Unified RefundRequest class for both providers\n";
echo "- ✓ PayPal refunds use capture IDs via /v2/payments/captures/{id}/refund\n";
echo "- ✓ Stripe refunds use payment intent IDs via /v1/refunds\n";
echo "- ✓ Support for both full and partial refunds\n";
echo "- ✓ Flexible refund reasons and metadata\n";

echo "\nNext Steps:\n";
echo "1. Test with real sandbox credentials\n";
echo "2. Complete a payment flow to get capture/payment IDs\n";
echo "3. Test actual refund functionality\n";
echo "4. Implement in your application\n";

echo "\n=== Example Complete ===\n";
